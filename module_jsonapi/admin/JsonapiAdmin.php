<?php
/*"******************************************************************************************************
*   (c) 2015 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
********************************************************************************************************/

namespace Kajona\Jsonapi\Admin;

use Kajona\Jsonapi\System\InvalidRequestException;
use Kajona\Jsonapi\System\ObjectSerializer;
use Kajona\System\Admin\AdminController;
use Kajona\System\Admin\AdminInterface;
use Kajona\System\System\AuthenticationException;
use Kajona\System\System\Exception;
use Kajona\System\System\HttpResponsetypes;
use Kajona\System\System\HttpStatuscodes;
use Kajona\System\System\ModelInterface;
use Kajona\System\System\Objectfactory;
use Kajona\System\System\ResponseObject;
use Kajona\System\System\Root;
use Kajona\System\System\SystemModule;

/**
 * Admin controller of the jsonapi-module. Handles all admin requests.
 *
 * @package module_jsonapi
 * @author christoph.kappestein@gmail.com
 *
 * @module jsonapi
 * @moduleId _jsonapi_module_id_
 */
class JsonapiAdmin extends AdminController implements AdminInterface
{


    /**
     * Handles the incomming request. Catches all exceptions so that we return
     * an clean json response with an fitting status code if an error occured
     *
     * @xml
     */
    protected function actionDispatch()
    {

        ResponseObject::getInstance()->setStrResponseType(HttpResponsetypes::STR_TYPE_JSON);

        try {
            ResponseObject::getInstance()->setStrStatusCode(HttpStatuscodes::SC_OK);

            $strRequestMethod = isset($_SERVER['REQUEST_METHOD']) ? $_SERVER['REQUEST_METHOD'] : 'GET';
            $strRequestMethod = strtolower($strRequestMethod);

            if (in_array($strRequestMethod, array('get', 'post', 'put', 'delete'))) {
                $arrResponse = $this->action($strRequestMethod);
            } else {
                throw new InvalidRequestException('Invalid request method', Exception::$level_ERROR);
            }

        } catch (InvalidRequestException $e) {
            ResponseObject::getInstance()->setStrStatusCode(HttpStatuscodes::SC_BADREQUEST);

            $e->processException();

            $arrResponse = array(
                'success' => false,
                'message' => $e->getMessage(),
            );

        } catch (Exception $e) {
            ResponseObject::getInstance()->setStrStatusCode(HttpStatuscodes::SC_INTERNAL_SERVER_ERROR);

            $e->processException();

            $arrResponse = array(
                'success' => false,
                'message' => $e->getMessage(),
            );

        } catch (\Exception $e) {
            ResponseObject::getInstance()->setStrStatusCode(HttpStatuscodes::SC_INTERNAL_SERVER_ERROR);

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
     * @throws InvalidRequestException
     * @permissions view
     * @xml
     */
    protected function actionGet()
    {
        // if we have no systemId we return an list else only an specific entry
        if (!validateSystemid($this->getSystemid())) {

            $strClass = $this->getParam('class');

            if (empty($strClass) || !class_exists($strClass)) {
                throw new InvalidRequestException('Invalid class name', Exception::$level_ERROR);
            }

            if (!method_exists($strClass, 'getObjectList')) {
                throw new InvalidRequestException('Invalid class type', Exception::$level_ERROR);
            }

            // filter parameters
            $strFilter = $this->getParam('filter');
            $intStartIndex = (int)$this->getParam('startIndex');

            $intCount = (int)$this->getParam('count');
            if ($intCount <= 0) {
                $intCount = 8;
            }

            $strStartDate = $this->getParam('startDate');
            if (!empty($strStartDate)) {
                $objStartDate = new \Kajona\System\System\Date(strtotime($strStartDate));
            } else {
                $objStartDate = null;
            }

            $strEndDate = $this->getParam('endDate');
            if (!empty($strEndDate)) {
                $objEndDate = new \Kajona\System\System\Date(strtotime($strEndDate));
            } else {
                $objEndDate = null;
            }

            /** @var ModelInterface[]|Root[] $arrEntries */
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
            $objObject = Objectfactory::getInstance()->getObject($this->getSystemid());
            return $this->serializeObject($objObject);
        }
    }

    /**
     * Inserts the request data into the model and creates the entry in the
     * database
     *
     * @return array
     * @throws AuthenticationException
     * @permissions edit
     * @xml
     */
    protected function actionPost()
    {
        /** @var \Kajona\System\System\Model $objObject */
        $objObject = $this->getCurrentObject();

        if (!SystemModule::getModuleByName($objObject->getArrModule("module"))->rightEdit()) {
            throw new AuthenticationException("You are not allowed to create new records", Exception::$level_ERROR);
        }

        $this->injectData($objObject);

        // @TODO validate the model data which can contain any data from the json request
        // we could use the form-validators, so field- and object validators right here. currently this is all rather fixed inside AdminFormgenerator,
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
     * @throws AuthenticationException
     * @permissions edit
     * @xml
     */
    protected function actionPut()
    {
        /** @var \Kajona\System\System\Model $objObject */
        $objObject = $this->getCurrentObject($this->getSystemid());

        if (!$objObject->rightEdit()) {
            throw new AuthenticationException("You are not allowed to update records", Exception::$level_ERROR);
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
     * @throws AuthenticationException
     * @permissions delete
     * @xml
     */
    protected function actionDelete()
    {
        /** @var \Kajona\System\System\Model $objObject */
        $objObject = $this->getCurrentObject($this->getSystemid());

        if (!$objObject->rightDelete()) {
            throw new AuthenticationException("You are not allowed to delete new records", Exception::$level_ERROR);
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
    protected function serializeObject(\Kajona\System\System\ModelInterface $objModel)
    {
        $objSerializer = new ObjectSerializer($objModel);

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
    protected function injectData(\Kajona\System\System\ModelInterface $objModel)
    {
        $arrData = $this->getRequestBody();
        $objSerializer = new ObjectSerializer($objModel);
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
     * @throws InvalidRequestException
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
                throw new InvalidRequestException('Invalid JSON request', Exception::$level_ERROR);
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
     * @throws InvalidRequestException
     */
    protected function getCurrentObject($strSystemId = null)
    {
        $strClassName = $this->getParam('class');

        if (empty($strClassName) || !class_exists($strClassName)) {
            throw new InvalidRequestException('Invalid class name', Exception::$level_ERROR);
        }

        if ($strSystemId !== null) {
            if (!validateSystemid($strSystemId)) {
                throw new InvalidRequestException('Invalid system id', Exception::$level_ERROR);
            }

            $objObject = Objectfactory::getInstance()->getObject($strSystemId);

            if ($objObject == null) {
                throw new InvalidRequestException('Object not exisiting', Exception::$level_ERROR);
            }
        } else {
            $objObject = new $strClassName();
        }

        if (!$objObject instanceof \Kajona\System\System\ModelInterface) {
            throw new InvalidRequestException('Selected class must be a model', Exception::$level_ERROR);
        }

        return $objObject;
    }
}

