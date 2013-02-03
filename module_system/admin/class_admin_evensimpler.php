<?php
/*"******************************************************************************************************
*   (c) 2007-2012 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id: class_admin_evensimpler.php 5334 2012-11-25 18:15:24Z tkiefer $	                            *
********************************************************************************************************/


/**
 * Class holding common methods for extended and simplified admin-guis.
 *
 * @module module_system
 * @since 4.1
 *
 * @author tim.kiefer@kojikui.de, ph.wolfer@googlemail.com
 */
abstract class class_admin_evensimpler extends class_admin_simple {
    const   STR_OBJECT_LIST_ANNOTATION = "@objectList";
    const   STR_OBJECT_NEW_ANNOTATION = "@objectNew";
    const   STR_OBJECT_EDIT_ANNOTATION = "@objectEdit";
    
    private static $arrActionNameMapping = array(
        "list" => self::STR_OBJECT_LIST_ANNOTATION,
        "new" => self::STR_OBJECT_NEW_ANNOTATION,
        "edit" => self::STR_OBJECT_EDIT_ANNOTATION,
        "save" => self::STR_OBJECT_EDIT_ANNOTATION,
        "delete" => self::STR_OBJECT_EDIT_ANNOTATION
    );

    private $strCurObjectClassName;
    private $strCurObjectTypeName = "";

    private $strOriginalAction = "";

    /**
     * @var class_admin_formgenerator
     */
    private $objCurAdminForm = null;


    /**
     * @param string $strAction
     *
     * @return string
     */
    public function action($strAction = "") {
        if ($strAction == "") $strActionName = $this->getAction();
        else $strActionName = $strAction;

        $this->strOriginalAction = $strActionName;

        if(!$this->checkMethodExistsInConcreteClass("action" . ucfirst($strActionName))) {

            foreach(self::$arrActionNameMapping as $strAutoMatchAction => $strAnnotation) {
                $this->autoMatchAction($strAutoMatchAction, $strAnnotation, $strActionName);
            }
        }

        return parent::action($strActionName);
    }

    /**
     * @param $strAction
     * @param $objInstance
     *
     * @return string
     */
    protected function getActionNameForClass($strAction, $objInstance) {
        if (isset(self::$arrActionNameMapping[$strAction])) {
            $strAnnotationPrefix = self::$arrActionNameMapping[$strAction];
            
            if ($strAction == "new") {
                return $strAction . $this->getStrCurObjectTypeName();
            }
            else {
                $objReflection = new class_reflection($this);
                $arrAnnotations = $objReflection->getAnnotationsWithValueFromClass(get_class($objInstance));
                
                foreach ($arrAnnotations as $strProperty) {
                    if (uniStrpos($strProperty, $strAnnotationPrefix) === 0) {
                        return $strAction . uniSubstr($strProperty, uniStrlen($strAnnotationPrefix));
                    }
                }
            }
        }
        
        return parent::getActionNameForClass($strAction, $objInstance);
    }


    /**
     * @param $strAutoMatchAction
     * @param $strAnnotation
     * @param $strActionName
     *
     * @return void
     */
    private function autoMatchAction($strAutoMatchAction, $strAnnotation, &$strActionName) {

        if(uniStrpos($strActionName, $strAutoMatchAction) === 0) {
            // Set name of current list object
            $this->setStrCurObjectTypeName(uniStrReplace($strAutoMatchAction, "", $strActionName));
            $strActionName = $strAutoMatchAction;

            $objReflection = new class_reflection($this);
            $arrAnnotations = $objReflection->getAnnotationValuesFromClass($strAnnotation . $this->getStrCurObjectTypeName());
            if(count($arrAnnotations) > 0) $this->setCurObjectClassName(reset($arrAnnotations));
            else $this->setCurObjectClassName(null);
        }
    }

    /**
     * Check if method exists in concrete class not only in class_admin_simple
     *
     * @param $strMethod
     *
     * @internal param $strActionName
     * @return bool
     */
    protected function checkMethodExistsInConcreteClass($strMethod) {

        if(method_exists($this, $strMethod)) {
            $refl = new ReflectionMethod($this, $strMethod);

            if($refl->class != "class_admin_evensimpler") {
                return true;
            }
            else return false;
        }
        return false;
    }


    /**
     * Renders the form to create a new entry
     * $strMode = "new", class_admin_formgenerator $objForm = null
     *
     * @throws class_exception
     * @return string
     * @permissions edit
     */
    protected function actionNew() {
        $strTyp = $this->getCurObjectClassName();

        if(!is_null($strTyp)) {
            /** @var $objEdit interface_model|class_model */
            $objEdit = new $strTyp();
            $objEdit->setSystemid($this->getParam("systemid"));

            $objForm = $this->getAdminForm($objEdit);
            $objForm->addField(new class_formentry_hidden("", "mode"))->setStrValue("new");

            return $objForm->renderForm(getLinkAdminHref($this->getArrModule("modul"), "save" . $this->getStrCurObjectTypeName()));
        }
        else
            throw new class_exception("error creating new entry current object type not known ", class_exception::$level_ERROR);
    }


    /**
     * Renders the form to edit an existing entry
     *
     * @throws class_exception
     * @return string
     * @permissions edit
     */
    protected function actionEdit() {
        $strTyp = $this->getCurObjectClassName();

        if(!is_null($strTyp)) {

            $objEdit = new $strTyp($this->getSystemid());
            $objForm = $this->getAdminForm($objEdit);
            $objForm->addField(new class_formentry_hidden("", "mode"))->setStrValue("edit");

            return $objForm->renderForm(getLinkAdminHref($this->getArrModule("modul"), "save" . $this->getStrCurObjectTypeName()));
        }
        else
            throw new class_exception("error editing current object type not known ", class_exception::$level_ERROR);
    }


    /**
     * Renders the general list of records
     *
     * @abstract
     * @throws class_exception
     * @return string
     * @permissions view
     */
    protected function actionList() {
        /** @var $strTyp interface_model|class_model */
        $strTyp = $this->getCurObjectClassName();

        if(!is_null($strTyp)) {

            $objArraySectionIterator = new class_array_section_iterator($strTyp::getObjectCount($this->getSystemid()));
            $objArraySectionIterator->setPageNumber((int)($this->getParam("pv") != "" ? $this->getParam("pv") : 1));
            $objArraySectionIterator->setArraySection($strTyp::getObjectList($this->getSystemid(), $objArraySectionIterator->calculateStartPos(), $objArraySectionIterator->calculateEndPos()));

            return $this->renderList($objArraySectionIterator);
        }
        else
            throw new class_exception("error loading list current object typ not known ", class_exception::$level_ERROR);
    }

    /**
     * @param interface_model|class_model $objInstance
     * @return class_admin_formgenerator
     */
    protected function getAdminForm(interface_model $objInstance) {

        //already generated?
        if($this->objCurAdminForm != null && get_class($this->objCurAdminForm->getObjSourceobject()) == get_class($objInstance))
            return $this->objCurAdminForm;

        $objForm = new class_admin_formgenerator($this->getArrModule("modul"), $objInstance);
        $objForm->generateFieldsFromObject();
        $this->objCurAdminForm = $objForm;
        return $objForm;
    }

    /**
     * Saves the passed values as a new category to the db
     *
     * @throws class_exception
     * @return string "" in case of success
     * @permissions edit
     */
    protected function actionSave() {
        $strTyp = $this->getCurObjectClassName();
        $strSystemId = "";

        if(!is_null($strTyp)) {

            /** @var $objRecord interface_model|class_model */
            $objRecord = null;

            if($this->getParam("mode") == "new"){
                $objRecord = new $strTyp();
                $strSystemId = $this->getSystemid();
            }
            else if($this->getParam("mode") == "edit")
                $objRecord = new $strTyp($this->getSystemid());

            if($objRecord != null) {
                $objForm = $this->getAdminForm($objRecord);
                if(!$objForm->validateForm()) {
                    if($this->getParam("mode") === "new")
                        return $this->actionNew();
                    if($this->getParam("mode") === "edit")
                        return $this->actionEdit();
                }

                $objForm->updateSourceObject();
                $objRecord->updateObjectToDb($strSystemId);


                $this->adminReload(getLinkAdminHref($this->getArrModule("modul"), $this->getActionNameForClass("list", $objRecord), "&systemid=".$objRecord->getStrPrevId().($this->getParam("pe") != "" ? "&peClose=1" : "")));
                return "";
            }
        }
        else
            throw new class_exception("error on saving current object typ not known ", class_exception::$level_ERROR);


        return $this->getLang("commons_error_permissions");
    }


    /**
     * Returns an additional set of action-buttons rendered right after the edit-action.
     *
     * @param class_model $objListEntry
     * @return array
     */
    protected function renderAdditionalActions(class_model $objListEntry) {
        return array();
    }

    
    protected function getArrOutputNaviEntries() {
        $arrPathLinks = parent::getArrOutputNaviEntries();
        $arrPath = $this->getPathArray($this->getSystemid());
        
        // Render additional navigation path entries for child objects.
        foreach($arrPath as $strOneVoting) {

            $objInstance = class_objectfactory::getInstance()->getObject($strOneVoting);
            $strLink = $this->getOutputNaviEntry($objInstance);
            
            if ($strLink) {
                $arrPathLinks[] = $strLink;
            }
        }

        return $arrPathLinks;
    }
    
    
    /**
     * Overwrite to generate path navigation entries for the given object.
     * 
     * @param interface_model $objInstance
     * @return string Navigation link.
     */
    protected function getOutputNaviEntry($objInstance) {
        return null;
    }

    protected function getQuickHelp() {
        $strOldAction = $this->getAction();
        $this->setAction($this->strOriginalAction);
        $strQuickhelp = parent::getQuickHelp();
        $this->setAction($strOldAction);
        return $strQuickhelp;
    }


    protected function getOutputActionTitle() {
        if($this->getStrCurObjectTypeName() == "")
            return $this->getOutputModuleTitle();
        else
            return $this->getLang($this->getObjLang()->stringToPlaceholder("modul_titel_".$this->getStrCurObjectTypeName()));
    }




    public function setStrCurObjectTypeName($strCurObjectTypeName) {
        $this->strCurObjectTypeName = $strCurObjectTypeName;
    }

    public function getStrCurObjectTypeName() {
        return $this->strCurObjectTypeName;
    }

    public function setCurObjectClassName($strCurObjectTyp) {
        $this->strCurObjectClassName = $strCurObjectTyp;
    }

    public function getCurObjectClassName() {
        return $this->strCurObjectClassName;
    }
}

