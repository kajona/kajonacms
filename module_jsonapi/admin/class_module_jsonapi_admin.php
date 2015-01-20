<?php
/*"******************************************************************************************************
*   (c) 2007-2014 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
********************************************************************************************************/


/**
 * Admin controller of the jsonapi-module. Handles all admin requests.
 *
 * @package module_jsonapi
 * @author christoph.kappestein@gmail.com
 *
 * @module jsonapi
 * @moduleId _jsonapi_module_id_
 */
class class_module_jsonapi_admin extends class_admin_controller implements interface_admin {



    /**
     * Handles the incomming request. Catches all exceptions so that we return
     * an clean json response with an fitting status code if an error occured
     *
     * @xml
     */
    protected function actionDispatch()
    {
        class_response_object::getInstance()->setStrResponseType(class_http_responsetypes::STR_TYPE_JSON);

        try {
            class_response_object::getInstance()->setStrStatusCode(class_http_statuscodes::SC_OK);

            //per coding convention, we use prefixes to indicate what data-type a variable is handling, e.g. int, str, obj, long, bit.
            //this is a little bit of overhead when coding, but makes the code much more readable, at least in our opinion
            //so $response would become $objResponse
            $response = $this->doHandle();

        } catch (class_invalid_request_exception $e) {
            class_response_object::getInstance()->setStrStatusCode(class_http_statuscodes::SC_BADREQUEST);

            $e->processException();

            $response = array(
                'success' => false,
                'message' => $e->getMessage(),
            );
        } catch (class_exception $e) {
            class_response_object::getInstance()->setStrStatusCode(class_http_statuscodes::SC_INTERNAL_SERVER_ERROR);

            $e->processException();

            $response = array(
                'success' => false,
                'message' => $e->getMessage(),
            );
        } catch (Exception $e) {
            class_response_object::getInstance()->setStrStatusCode(class_http_statuscodes::SC_INTERNAL_SERVER_ERROR);

            $response = array(
                'success' => false,
                'message' => 'An unknown error occured',
            );
        }

        return json_encode($response, JSON_PRETTY_PRINT);
    }

    /**
     * Creates an new model of the specified class and handles the action 
     * according to the request method
     *
     * @return array
     */
    protected function doHandle() {
        $className = $this->getParam('class');
        //direct access to a validated systemid is best achieved by getSystemid()
        $systemId = $this->getSystemid();
        $requestMethod = isset($_SERVER['REQUEST_METHOD']) ? $_SERVER['REQUEST_METHOD'] : 'GET';

        if (!empty($className) && class_exists($className)) {
            $obj = new $className($systemId);
            if (!$obj instanceof interface_model) {
                throw new class_invalid_request_exception('Selected class must be a model', class_exception::$level_ERROR);
            }
        } else {
            throw new class_invalid_request_exception('Invalid class name', class_exception::$level_ERROR);
        }

        switch ($requestMethod) {

            case 'GET':
                //call the internal action dispatcher to trigger the permission management. Call it by the name of the method,
                // so a call to "list" would be mapped to the method-name "actionList".
                //permissions are checked in two separate ways:
                // 1.) a given systemid -> the object itself is instantiated, the permission is validated against the object
                // 2.) no systemid given -> the permissions are validated against the current module, so here module jsonapi - what is nonsense in this scenario
                $response = $this->action("get");
                break;

            case 'POST':
                if (!empty($systemId)) {
                    throw new class_invalid_request_exception('Systemid must be empty when creating an entry', class_exception::$level_ERROR);
                }

                $response = $this->action("post");
                break;

            case 'PUT':
                if (!validateSystemid($systemId)) {
                    throw new class_invalid_request_exception('Systemid must be given when updating an entry', class_exception::$level_ERROR);
                }

                $response = $this->doPut($obj);
                break;

            case 'DELETE':
                if (!validateSystemid($systemId)) {
                    throw new class_invalid_request_exception('Systemid must be given when deleting an entry', class_exception::$level_ERROR);
                }

                $response = $this->action("delete");
                break;

            default:
                throw new class_invalid_request_exception('Invalid request method', class_exception::$level_ERROR);
                break;

        }

        return $response;
    }

    /**
     * Is called on an GET request. If an systemId is available only the 
     * specific entry gets returned else an complete list
     *
     * @return array
     * @permissions view
     */
    protected function actionGet() {
        // if we have no systemId we return an list else only an specific entry
        if (!validateSystemid($this->getSystemid())) {

            $strClass = $this->getParam('class');

            // filter parameters
            $filter = $this->getParam('filter');
            $startIndex = (int) $this->getParam('startIndex');

            $count = (int) $this->getParam('count');
            if ($count <= 0) {
                $count = 8;
            }

            $startDate = $this->getParam('startDate');
            if (!empty($startDate)) {
                $startDate = new class_date(strtotime($startDate));
            } else {
                $startDate = null;
            }

            $endDate = $this->getParam('endDate');
            if (!empty($endDate)) {
                $endDate = new class_date(strtotime($endDate));
            } else {
                $endDate = null;
            }

            //getObjectList is static, so call it against the class-definition, plz
            /** @var interface_model[]|class_root[] $entries */
            $entries = $strClass::getObjectList($filter, $startIndex, $count, $startDate, $endDate);
            $result = array();

            foreach ($entries as $entry) {

                //internal permission handling right here
                if(!$entry->rightView())
                    continue;

                $row = $this->serializeObject($entry);
                if (!empty($row)) {
                    $result[] = $row;
                }
            }

            return $result;
        } else {
            //get the object from the global object-factory, taking care of caching and everything else
            $obj = class_objectfactory::getInstance()->getObject($this->getSystemid());
            return $this->serializeObject($obj);
        }
    }

    /**
     * Inserts the request data into the model and creates the entry in the
     * database
     *
     * @return array
     * @throws class_authentication_exception
     * @permissions edit
     */
    protected function actionPost()
    {
        //since we don't have a final object, the permission-handling evaluates the edit-permissions against module jsonapi.
        //in addition, we should validate the edit-permissions of the target-class' module, e.g.:

        $strClass = $this->getParam('class');
        /** @var class_model $objObject */
        $objObject = new $strClass();

        if(!class_module_system_module::getModuleByName($objObject->getArrModule("module"))->rightEdit()) {
            throw new class_authentication_exception("You are not allowed to create new records", class_exception::$level_ERROR);
        }

        $this->injectData($objObject);

        // @TODO validate the model data which can contain any data from the json request
        // we could use the form-validators, so field- and object validators right here. currently this is all rather fixed inside class_admin_formgenerator,
        // but we should refactor it from there into separate classes, so in order to reuse it here -> filing a ticket?

        $objObject->updateObjectToDb();

        return array(
            'success' => true,
            'message' => 'Create entry successful',
        );
    }

    /**
     * Inserts the request data into the model and updates the entry in the 
     * database
     *
     * @return array
     * @permissions edit
     */
    protected function doPut(interface_model $obj)
    {
        // @TODO check whether the model actual exists in the database

        $this->injectData($obj);

        // @TODO validate the model data which can contain any data from the json request 

        $obj->updateObjectToDb();

        return array(
            'success' => true,
            'message' => 'Update entry successful',
        );
    }

    /**
     * Deletes the model from the database
     *
     * @return array
     * @permissions delete
     */
    protected function actionDelete()
    {
        // @TODO check whether the model actual exists in the database

        $obj = class_objectfactory::getInstance()->getObject($this->getSystemid());

        if($obj == null) {
            throw new class_invalid_request_exception('Object not exisiting', class_exception::$level_ERROR);
        }
        $obj->deleteObject();

        return array(
            'success' => true,
            'message' => 'Delete entry successful',
        );
    }

    /**
     * Serialize an model into an array. Uses the object serializer which 
     * searches in the model for @jsonExport annotations. The system id is 
     * always added
     *
     * @return array
     */
    protected function serializeObject(interface_model $obj)
    {
        $serializer = new class_object_serializer($obj);

        return array_merge(
            array('_id' => $obj->getSystemid()), 
            $serializer->getArrMapping()
        );
    }

    /**
     * Injects the request data into the model
     *
     * @return interface_model
     */
    protected function injectData(interface_model $obj)
    {
        $data = $this->getRequestBody();
        $serializer = new class_object_serializer($obj);
        $properties = $serializer->getPropertyNames();

        foreach ($properties as $property) {
            $setterMethod = 'set' . ucfirst($property);
            if (isset($data[$property]) && method_exists($obj, $setterMethod)) {
                $obj->$setterMethod($data[$property]);
            }
        }
    }

    /**
     * Parses the request body as JSON string and returns the result as array
     *
     * @return array
     */
    protected function getRequestBody()
    {
        $rawBody = file_get_contents('php://input');
        if (!empty($rawBody)) {
            $body = json_decode($rawBody, true);
            $lastError = json_last_error();

            if ($lastError == JSON_ERROR_NONE) {
                return $body;
            } else {
                throw new class_invalid_request_exception('Invalid JSON request', class_exception::$level_ERROR);
            }
        } else {
            return null;
        }
    }
}


