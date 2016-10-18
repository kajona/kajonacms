<?php
/*"******************************************************************************************************
*   (c) 2015-2016 by Kajona, www.kajona.de                                                         *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
********************************************************************************************************/

namespace Kajona\Jsonapi\Admin;

use Kajona\Jsonapi\System\InvalidRequestException;
use Kajona\Jsonapi\System\ObjectSerializer;
use Kajona\System\Admin\AdminEvensimpler;
use Kajona\System\Admin\AdminFormgenerator;
use Kajona\System\Admin\AdminFormgeneratorFactory;
use Kajona\System\Admin\AdminFormgeneratorFilter;
use Kajona\System\Admin\AdminInterface;
use Kajona\System\Admin\AdminSimple;
use Kajona\System\System\AuthenticationException;
use Kajona\System\System\Exception;
use Kajona\System\System\FilterBase;
use Kajona\System\System\HttpResponsetypes;
use Kajona\System\System\HttpStatuscodes;
use Kajona\System\System\ModelInterface;
use Kajona\System\System\Objectfactory;
use Kajona\System\System\Reflection;
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
class JsonapiAdmin extends AdminEvensimpler implements AdminInterface
{
    /**
     * Handles the incoming request. Catches all exceptions so that we return
     * a clean json response with a fitting status code if an error occured
     *
     */
    protected function actionDispatch()
    {
        ResponseObject::getInstance()->setStrResponseType(HttpResponsetypes::STR_TYPE_JSON);

        try {
            ResponseObject::getInstance()->setStrStatusCode(HttpStatuscodes::SC_OK);

            $strRequestMethod = $this->getRequestMethod();
            if (in_array($strRequestMethod, array('get', 'post', 'put', 'delete'))) {
                $arrResponse = $this->action($strRequestMethod);
            } else {
                throw new InvalidRequestException('Invalid request method', Exception::$level_ERROR);
            }

        } catch (InvalidRequestException $e) {
            ResponseObject::getInstance()->setStrStatusCode(HttpStatuscodes::SC_BADREQUEST);

            $arrResponse = array(
                'success' => false,
                'message' => $e->getMessage(),
            );

        } catch (Exception $e) {
            ResponseObject::getInstance()->setStrStatusCode(HttpStatuscodes::SC_INTERNAL_SERVER_ERROR);

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
     * Is called on a GET request. If a systemId is available only the
     * specific entry gets returned else a complete list
     *
     * @return array
     * @throws InvalidRequestException
     * @permissions view
     */
    protected function actionGet()
    {
        // if we have no systemId we return a list else only a specific entry
        $objObject = $this->getCurrentObject($this->getSystemid());

        if (!validateSystemid($this->getSystemid())) {
            if ($this->getParam("form")) {
                $objAdminForm = AdminFormgeneratorFactory::createByModel($objObject);
                return $this->getAdminJsonForm($objAdminForm);
            } else {
                // pagination
                $intStartIndex = (int)$this->getParam('startIndex');
                $intStartIndex = $intStartIndex <= 0 ? 0 : $intStartIndex;
                $intCount = (int)$this->getParam('count');
                $intCount = $intCount <= 0 ? 8 : $intCount;

                $strPrevId = $this->getParam("previd") ?: "";
                $objFilter = $this->getFilterForModel($objObject);

                if ($objFilter !== null) {
                    $arrFilter = $this->getAdminJsonFilterForm($objFilter);
                } else {
                    $arrFilter = null;
                }

                $intTotalCount = $objObject::getObjectCountFiltered($objFilter, $strPrevId);
                $arrEntries = $objObject::getObjectListFiltered($objFilter, $strPrevId, $intStartIndex, $intCount);

                $arrResult = array();
                /** @var ModelInterface[]|Root[] $arrEntries */
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

                return array(
                    'totalCount' => $intTotalCount,
                    'startIndex' => $intStartIndex,
                    'filter'     => $arrFilter,
                    'entries'    => $arrResult,
                );
            }
        } else {
            if ($this->getParam("form")) {
                $objAdminForm = AdminFormgeneratorFactory::createByModel($objObject);
                return $this->getAdminJsonForm($objAdminForm);
            } else {
                return $this->serializeObject($objObject);
            }
        }
    }

    /**
     * Returns an array representation of the form structure which can be used by the client
     *
     * @param Root $objObject
     * @return array
     * @throws Exception
     * @throws InvalidRequestException
     */
    protected function getAdminJsonForm(AdminFormgenerator $objAdminForm)
    {
        if ($this->getParam("form") == "html") {
            $strTargetUri = $this->getParam("target_uri");
            if (!filter_var($strTargetUri, FILTER_VALIDATE_URL)) {
                $strTargetUri = "";
            }

            $intButtonConfig = (int)$this->getParam("button_config") ?: 2;

            return array(
                "form" => $objAdminForm->renderForm($strTargetUri, $intButtonConfig),
            );
        } else {
            $arrFields = $objAdminForm->getArrFields();
            $arrResult = array();

            foreach ($arrFields as $objField) {
                $arrResult[] = array(
                    "_class"    => get_class($objField),
                    "entryName" => $objField->getStrEntryName(),
                    "label"     => $objField->getStrLabel(),
                    "hint"      => $objField->getStrHint(),
                    "readonly"  => $objField->getBitReadonly(),
                    "value"     => $objField->getStrValue(),
                    "mandatory" => $objField->getBitMandatory(),
                );
            }

            return array(
                "name"   => $objAdminForm->getStrFormname(),
                "fields" => $arrResult,
            );
        }
    }

    /**
     * Returns a JSON representation of the filter object form
     *
     * @param FilterBase $objFilter
     * @return array
     */
    protected function getAdminJsonFilterForm(FilterBase $objFilter)
    {
        $objFilterForm = new AdminFormgeneratorFilter($objFilter->getFilterId(), $objFilter);
        $objFilterForm->generateFieldsFromObject();
        $objFilterForm->updateSourceObject();

        return $this->getAdminJsonForm($objFilterForm);
    }

    /**
     * Returns the filter object for a model
     *
     * @param string $strClassName
     * @return FilterBase|null
     * @throws Exception
     */
    protected function getFilterForModel(Root $objObject)
    {
        $strObjectFilterClass = $this->getObjectFilterClassName($objObject);

        if (!empty($strObjectFilterClass) && class_exists($strObjectFilterClass)) {
            $objFilter = new $strObjectFilterClass();
            if ($objFilter instanceof FilterBase) {
                $objFilter->updateFilterPropertiesFromParams();
            }

            return $objFilter;
        }

        return null;
    }

    /**
     * Since we have no clean way to get the fitting filter for a model we use the object_type parameter
     *
     * @param Root $objObject
     * @return string|null
     */
    protected function getObjectFilterClassName(Root $objObject)
    {
        $strCurObjectTypeName = $this->getParam("object_type");
        $objAdminController = SystemModule::getModuleByName($objObject->getArrModule("module"))->getAdminInstanceOfConcreteModule($objObject->getStrSystemid());
        $objReflection = new Reflection($objAdminController);
        $arrAnnotations = $objReflection->getAnnotationValuesFromClass(AdminEvensimpler::STR_OBJECT_LISTFILTER_ANNOTATION.$strCurObjectTypeName);

        if (count($arrAnnotations) > 0) {
            return reset($arrAnnotations);
        }

        return null;
    }

    /**
     * Inserts the request data into the model and creates the entry in the
     * database
     *
     * @return array
     * @throws AuthenticationException
     * @permissions edit
     */
    protected function actionPost()
    {
        /** @var \Kajona\System\System\Model $objObject */
        $objObject = $this->getCurrentObject();

        if (!SystemModule::getModuleByName($objObject->getArrModule("module"))->rightEdit()) {
            throw new AuthenticationException("You are not allowed to create new records", Exception::$level_ERROR);
        }

        // read the request body and set the fitting params so that the normal form validation works
        $this->readRequestBody();

        $objAdminForm = AdminFormgeneratorFactory::createByModel($objObject);
        $objAdminForm->readValues();

        // validate
        if (!$objAdminForm->validateForm()) {
            ResponseObject::getInstance()->setStrStatusCode(HttpStatuscodes::SC_BADREQUEST);

            return array(
                'success' => false,
                'errors'  => $objAdminForm->getValidationErrors(),
            );
        }

        $objAdminForm->updateSourceObject();

        // create object
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
     */
    protected function actionPut()
    {
        /** @var \Kajona\System\System\Model $objObject */
        $objObject = $this->getCurrentObject($this->getSystemid());

        if (!$objObject->rightEdit()) {
            throw new AuthenticationException("You are not allowed to update records", Exception::$level_ERROR);
        }

        // read the request body and set the fitting params so that the normal form validation works
        $this->readRequestBody();

        $objAdminForm = AdminFormgeneratorFactory::createByModel($objObject);
        $objAdminForm->readValues();

        // validate
        if (!$objAdminForm->validateForm()) {
            ResponseObject::getInstance()->setStrStatusCode(HttpStatuscodes::SC_BADREQUEST);

            return array(
                'success' => false,
                'errors'  => $objAdminForm->getValidationErrors(),
            );
        }

        $objAdminForm->updateSourceObject();

        // update object
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
     */
    protected function actionDelete()
    {
        /** @var \Kajona\System\System\Model $objObject */
        $objObject = $this->getCurrentObject($this->getSystemid());

        if (!$objObject->rightDelete()) {
            throw new AuthenticationException("You are not allowed to delete records", Exception::$level_ERROR);
        }

        // delete object
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
     * @param Root $objModel
     * @return array
     */
    protected function serializeObject(Root $objModel)
    {
        $objSerializer = new ObjectSerializer($objModel);

        /*
        $objAdminController = SystemModule::getModuleByName($objModel->getArrModule("module"))->getAdminInstanceOfConcreteModule($objModel->getStrSystemid());
        if ($objAdminController instanceof AdminSimple) {
            $arrActions = $objAdminController->getActionIcons($objModel);
        } else {
            $arrActions = [];
        }
        */

        return array_merge(
            array(
                '_id'              => $objModel->getSystemid(),
                '_class'           => get_class($objModel),
                '_icon'            => is_array($objModel->getStrIcon()) ? current($objModel->getStrIcon()) : $objModel->getStrIcon(),
                '_displayName'     => $objModel->getStrDisplayName(),
                '_additionalInfo'  => $objModel->getStrAdditionalInfo(),
                '_longDescription' => $objModel->getStrLongDescription(),
            ),
            $objSerializer->getArrMapping()
        );
    }

    /**
     * Parses the request body as JSON string and returns the result as array
     *
     * @return array
     * @throws InvalidRequestException
     */
    protected function readRequestBody()
    {
        $strRawBody = $this->getRawInput();
        if (!empty($strRawBody)) {
            $arrBody = json_decode($strRawBody, true);
            $strLastError = json_last_error();

            if ($strLastError == JSON_ERROR_NONE && is_array($arrBody)) {
                // set the request data as params so that we can use updateSourceObject to inject the data into the
                // object. Note this may overwrites GET params which have the same name
                foreach ($arrBody as $strKey => $strValue) {
                    $this->setParam($strKey, $strValue);
                }
            } else {
                throw new InvalidRequestException('Invalid JSON request', Exception::$level_ERROR);
            }
        } else {
            return null;
        }
    }

    /**
     * Returns the raw request input in case of POST and PUT requests
     *
     * @return string
     */
    protected function getRawInput()
    {
        return file_get_contents('php://input');
    }

    /**
     * Returns the lowercased request method
     *
     * @return string
     */
    protected function getRequestMethod()
    {
        $strRequestMethod = isset($_SERVER['REQUEST_METHOD']) ? $_SERVER['REQUEST_METHOD'] : 'GET';
        $strRequestMethod = strtolower($strRequestMethod);

        return $strRequestMethod;
    }

    /**
     * Returns an model based on the given GET parameter "class". If the system
     * id is available it validates whether the id is valid and that the object
     * exists
     *
     * @param string $strSystemId
     * @return \Kajona\System\System\Root
     * @throws InvalidRequestException
     */
    protected function getCurrentObject($strSystemId = null)
    {
        if (!empty($strSystemId)) {
            if (!validateSystemid($strSystemId)) {
                throw new InvalidRequestException('Invalid system id', Exception::$level_ERROR);
            }

            $objObject = Objectfactory::getInstance()->getObject($strSystemId);

            if ($objObject == null) {
                throw new InvalidRequestException('Object not exisiting', Exception::$level_ERROR);
            }
        } else {
            $strClassName = $this->getParam('class');

            if (empty($strClassName) || !class_exists($strClassName)) {
                throw new InvalidRequestException('Invalid class name', Exception::$level_ERROR);
            }

            $objObject = new $strClassName();
        }

        if (!$objObject instanceof \Kajona\System\System\Root) {
            throw new InvalidRequestException('Selected class must be a model', Exception::$level_ERROR);
        }

        return $objObject;
    }
}

