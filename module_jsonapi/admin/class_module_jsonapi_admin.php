<?php
/*"******************************************************************************************************
*   (c) 2015 by Kajona, www.kajona.de                                                              *
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
    protected function actionDispatch() {

        class_response_object::getInstance()->setStrResponseType(class_http_responsetypes::STR_TYPE_JSON);

        try {
            class_response_object::getInstance()->setStrStatusCode(class_http_statuscodes::SC_OK);

            $strRequestMethod = isset($_SERVER['REQUEST_METHOD']) ? $_SERVER['REQUEST_METHOD'] : 'GET';
            $strRequestMethod = strtolower($strRequestMethod);

            if(in_array($strRequestMethod, array('get', 'post', 'put', 'delete'))) {
                $arrResponse = $this->action($strRequestMethod);
            } else {
                throw new class_invalid_request_exception('Invalid request method', class_exception::$level_ERROR);
            }

        } catch (class_invalid_request_exception $e) {
            class_response_object::getInstance()->setStrStatusCode(class_http_statuscodes::SC_BADREQUEST);

            $e->processException();

            $arrResponse = array(
                'success' => false,
                'message' => $e->getMessage(),
            );

        } catch (class_exception $e) {
            class_response_object::getInstance()->setStrStatusCode(class_http_statuscodes::SC_INTERNAL_SERVER_ERROR);

            $e->processException();

            $arrResponse = array(
                'success' => false,
                'message' => $e->getMessage(),
            );

        } catch (Exception $e) {
            class_response_object::getInstance()->setStrStatusCode(class_http_statuscodes::SC_INTERNAL_SERVER_ERROR);

            $arrResponse = array(
                'success' => false,
                'message' => 'An unknown error occured',
            );

        }

        return json_encode($arrResponse, JSON_PRETTY_PRINT);
    }

    /**
     * Is called on an GET request. If an systemId is available only the
     * specific entry gets returned else an complete list
     *
     * @return array
     * @throws class_invalid_request_exception
     * @permissions view
     * @xml
     */
    protected function actionGet() {
        // if we have no systemId we return an list else only an specific entry
        if(!validateSystemid($this->getSystemid())) {

            $strClass = $this->getParam('class');

            if(empty($strClass) || !class_exists($strClass)) {
                throw new class_invalid_request_exception('Invalid class name', class_exception::$level_ERROR);
            }

            if(!method_exists($strClass, 'getObjectList')) {
                throw new class_invalid_request_exception('Invalid class type', class_exception::$level_ERROR);
            }

            // filter parameters
            $strFilter = $this->getParam('filter');
            $intStartIndex = (int) $this->getParam('startIndex');

            $intCount = (int) $this->getParam('count');
            if($intCount <= 0) {
                $intCount = 8;
            }

            $strStartDate = $this->getParam('startDate');
            if(!empty($strStartDate)) {
                $objStartDate = new \Kajona\System\System\Date(strtotime($strStartDate));
            } else {
                $objStartDate = null;
            }

            $strEndDate = $this->getParam('endDate');
            if(!empty($strEndDate)) {
                $objEndDate = new \Kajona\System\System\Date(strtotime($strEndDate));
            } else {
                $objEndDate = null;
            }

            /** @var \Kajona\System\System\ModelInterface[]|class_root[] $arrEntries */
            $arrEntries = $strClass::getObjectList($strFilter, $intStartIndex, $intCount, $objStartDate, $objEndDate);
            $arrResult = array();

            foreach($arrEntries as $objEntry) {
                // internal permission handling right here
                if(!$objEntry->rightView()) {
                    continue;
                }

                $arrRow = $this->serializeObject($objEntry);
                if(!empty($arrRow)) {
                    $arrResult[] = $arrRow;
                }
            }

            return $arrResult;
        } else {
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
    protected function actionPost() {
        /** @var \Kajona\System\System\Model $objObject */
        $objObject = $this->getCurrentObject();

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
     * @throws class_authentication_exception
     * @permissions edit
     * @xml
     */
    protected function actionPut() {
        /** @var \Kajona\System\System\Model $objObject */
        $objObject = $this->getCurrentObject($this->getSystemid());

        if(!$objObject->rightEdit()) {
            throw new class_authentication_exception("You are not allowed to update records", class_exception::$level_ERROR);
        }

        $this->injectData($objObject);

        // @TODO validate the model data which can contain any data from the json request 

        $objObject->updateObjectToDb();

        return array(
            'success' => true,
            'message' => 'Update entry successful',
        );
    }

    /**
     * Deletes the model from the database
     *
     * @return array
     * @throws class_authentication_exception
     * @permissions delete
     * @xml
     */
    protected function actionDelete() {
        /** @var \Kajona\System\System\Model $objObject */
        $objObject = $this->getCurrentObject($this->getSystemid());

        if(!$objObject->rightDelete()) {
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
     * @param \Kajona\System\System\ModelInterface $objModel
     * @return array
     */
    protected function serializeObject(\Kajona\System\System\ModelInterface $objModel) {
        $objSerializer = new class_object_serializer($objModel);

        return array_merge(
            array('_id' => $objModel->getSystemid()), 
            $objSerializer->getArrMapping()
        );
    }

    /**
     * Injects the request data into the model
     *
     * @param \Kajona\System\System\ModelInterface $objModel
     */
    protected function injectData(\Kajona\System\System\ModelInterface $objModel) {
        $arrData = $this->getRequestBody();
        $objSerializer = new class_object_serializer($objModel);
        $arrProperties = $objSerializer->getPropertyNames();

        foreach($arrProperties as $strProperty) {
            $strSetterMethod = 'set' . ucfirst($strProperty);
            if(isset($arrData[$strProperty]) && method_exists($objModel, $strSetterMethod)) {
                $objModel->$strSetterMethod($arrData[$strProperty]);
            }
        }
    }

    /**
     * Parses the request body as JSON string and returns the result as array
     *
     * @return array
     * @throws class_invalid_request_exception
     */
    protected function getRequestBody() {
        $strRawBody = file_get_contents('php://input');
        if(!empty($strRawBody)) {
            $arrBody = json_decode($strRawBody, true);
            $strLastError = json_last_error();

            if($strLastError == JSON_ERROR_NONE) {
                return $arrBody;
            } else {
                throw new class_invalid_request_exception('Invalid JSON request', class_exception::$level_ERROR);
            }
        } else {
            return null;
        }
    }

    /**
     * Returns an model based on the given GET parameter "class". If the system
     * id is available it validates whether the id is valid and that the object 
     * exists
     *
     * @param string $strSystemId
     * @return \Kajona\System\System\ModelInterface
     * @throws class_invalid_request_exception
     */
    protected function getCurrentObject($strSystemId = null) {
        $strClassName = $this->getParam('class');

        if(empty($strClassName) || !class_exists($strClassName)) {
            throw new class_invalid_request_exception('Invalid class name', class_exception::$level_ERROR);
        }

        if($strSystemId !== null) {
            if(!validateSystemid($strSystemId)) {
                throw new class_invalid_request_exception('Invalid system id', class_exception::$level_ERROR);
            }

            $objObject = class_objectfactory::getInstance()->getObject($strSystemId);

            if($objObject == null) {
                throw new class_invalid_request_exception('Object not exisiting', class_exception::$level_ERROR);
            }
        } else {
            $objObject = new $strClassName();
        }

        if(!$objObject instanceof \Kajona\System\System\ModelInterface) {
            throw new class_invalid_request_exception('Selected class must be a model', class_exception::$level_ERROR);
        }

        return $objObject;
    }
}

