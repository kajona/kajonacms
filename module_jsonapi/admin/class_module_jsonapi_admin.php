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

            $objResponse = $this->doHandle();

        } catch (class_invalid_request_exception $e) {
            class_response_object::getInstance()->setStrStatusCode(class_http_statuscodes::SC_BADREQUEST);

            $e->processException();

            $objResponse = array(
                'success' => false,
                'message' => $e->getMessage(),
            );

        } catch (class_exception $e) {
            class_response_object::getInstance()->setStrStatusCode(class_http_statuscodes::SC_INTERNAL_SERVER_ERROR);

            $e->processException();

            $objResponse = array(
                'success' => false,
                'message' => $e->getMessage(),
            );

        } catch (Exception $e) {
            class_response_object::getInstance()->setStrStatusCode(class_http_statuscodes::SC_INTERNAL_SERVER_ERROR);

            $objResponse = array(
                'success' => false,
                'message' => 'An unknown error occured',
            );

        }

        return json_encode($objResponse, JSON_PRETTY_PRINT);
    }

    /**
     * Creates an new model of the specified class and handles the action 
     * according to the request method
     *
     * @return array
     */
    protected function doHandle() {
        $strClassName = $this->getParam('class');
        $strSystemId = $this->getSystemid();
        $strRequestMethod = isset($_SERVER['REQUEST_METHOD']) ? $_SERVER['REQUEST_METHOD'] : 'GET';

        if (!empty($strClassName) && class_exists($strClassName)) {
            $objObject = new $strClassName($strSystemId);
            if (!$objObject instanceof interface_model) {
                throw new class_invalid_request_exception('Selected class must be a model', class_exception::$level_ERROR);
            }
        } else {
            throw new class_invalid_request_exception('Invalid class name', class_exception::$level_ERROR);
        }

        switch ($strRequestMethod) {

            case 'GET':
                //call the internal action dispatcher to trigger the permission management. Call it by the name of the method,
                // so a call to "list" would be mapped to the method-name "actionList".
                //permissions are checked in two separate ways:
                // 1.) a given systemid -> the object itself is instantiated, the permission is validated against the object
                // 2.) no systemid given -> the permissions are validated against the current module, so here module jsonapi - what is nonsense in this scenario
                $arrResponse = $this->action("get");
                break;

            case 'POST':
                if (!empty($strSystemId)) {
                    throw new class_invalid_request_exception('Systemid must be empty when creating an entry', class_exception::$level_ERROR);
                }

                $arrResponse = $this->action("post");
                break;

            case 'PUT':
                if (!validateSystemid($strSystemId)) {
                    throw new class_invalid_request_exception('Systemid must be given when updating an entry', class_exception::$level_ERROR);
                }

                $arrResponse = $this->doPut($objObject);
                break;

            case 'DELETE':
                if (!validateSystemid($strSystemId)) {
                    throw new class_invalid_request_exception('Systemid must be given when deleting an entry', class_exception::$level_ERROR);
                }

                $arrResponse = $this->action("delete");
                break;

            default:
                throw new class_invalid_request_exception('Invalid request method', class_exception::$level_ERROR);
                break;

        }

        // in case of permission errors the response contains an string from the
        // "action" method. In this case we extract the message and return an 
        // clean json response
        // @TODO find a better way to handle this case
        if (is_string($arrResponse)) {
            $arrResponse = array(
                'success' => false,
                'message' => trim(strip_tags($arrResponse)),
            );
        }

        return $arrResponse;
    }

    /**
     * Is called on an GET request. If an systemId is available only the 
     * specific entry gets returned else an complete list
     *
     * @return array
     * @permissions view
     * @xml
     */
    protected function actionGet() {
        // if we have no systemId we return an list else only an specific entry
        if (!validateSystemid($this->getSystemid())) {

            $strClass = $this->getParam('class');

            // filter parameters
            $strFilter = $this->getParam('filter');
            $intStartIndex = (int) $this->getParam('startIndex');

            $intCount = (int) $this->getParam('count');
            if ($intCount <= 0) {
                $intCount = 8;
            }

            $strStartDate = $this->getParam('startDate');
            if (!empty($strStartDate)) {
                $objStartDate = new class_date(strtotime($strStartDate));
            } else {
                $objStartDate = null;
            }

            $strEndDate = $this->getParam('endDate');
            if (!empty($endDate)) {
                $objEndDate = new class_date(strtotime($strEndDate));
            } else {
                $objEndDate = null;
            }

            /** @var interface_model[]|class_root[] $entries */
            $arrEntries = $strClass::getObjectList($strFilter, $intStartIndex, $intCount, $objStartDate, $objEndDate);
            $arrResult = array();

            foreach ($arrEntries as $objEntry) {
                // internal permission handling right here
                if (!$objEntry->rightView()) {
                    continue;
                }

                $arrRow = $this->serializeObject($objEntry);
                if (!empty($arrRow)) {
                    $arrResult[] = $arrRow;
                }
            }

            return $arrResult;
        } else {
            //get the object from the global object-factory, taking care of caching and everything else
            $objObject = class_objectfactory::getInstance()->getObject($this->getSystemid());
            return $this->serializeObject($objObject);
        }
    }

    /**
     * Inserts the request data into the model and creates the entry in the
     * database
     *
     * @return array
     * @throws class_authentication_exception
     * @permissions edit
     * @xml
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
     * @xml
     */
    protected function doPut(interface_model $objModel)
    {
        // @TODO check whether the model actual exists in the database

        $this->injectData($objModel);

        // @TODO validate the model data which can contain any data from the json request 

        $objModel->updateObjectToDb();

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
     * @xml
     */
    protected function actionDelete()
    {
        $objObject = class_objectfactory::getInstance()->getObject($this->getSystemid());

        if($objObject == null) {
            throw new class_invalid_request_exception('Object not exisiting', class_exception::$level_ERROR);
        }

        if(!class_module_system_module::getModuleByName($objObject->getArrModule("module"))->rightDelete()) {
            throw new class_authentication_exception("You are not allowed to delete new records", class_exception::$level_ERROR);
        }

        $objObject->deleteObject();

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
    protected function serializeObject(interface_model $objModel)
    {
        $objSerializer = new class_object_serializer($objModel);

        return array_merge(
            array('_id' => $objModel->getSystemid()), 
            $objSerializer->getArrMapping()
        );
    }

    /**
     * Injects the request data into the model
     *
     * @return interface_model
     */
    protected function injectData(interface_model $objModel)
    {
        $arrData = $this->getRequestBody();
        $objSerializer = new class_object_serializer($objModel);
        $arrProperties = $objSerializer->getPropertyNames();

        foreach ($arrProperties as $strProperty) {
            $strSetterMethod = 'set' . ucfirst($strProperty);
            if (isset($arrData[$strProperty]) && method_exists($objModel, $strSetterMethod)) {
                $objModel->$strSetterMethod($arrData[$strProperty]);
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
        $strRawBody = file_get_contents('php://input');
        if (!empty($strRawBody)) {
            $arrBody = json_decode($strRawBody, true);
            $strLastError = json_last_error();

            if ($strLastError == JSON_ERROR_NONE) {
                return $arrBody;
            } else {
                throw new class_invalid_request_exception('Invalid JSON request', class_exception::$level_ERROR);
            }
        } else {
            return null;
        }
    }
}


