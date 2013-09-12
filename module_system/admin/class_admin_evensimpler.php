<?php
/*"******************************************************************************************************
*   (c) 2007-2012 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id: class_admin_evensimpler.php 5334 2012-11-25 18:15:24Z tkiefer $	                            *
********************************************************************************************************/


/**
 * Class holding common methods for extended and simplified admin-guis.
 * Compared to class_admin_simple, this implementation is based on a declarative approach,
 * reducing the amount of code required to implement a modules' admin-views.
 *
 * Subclasses are able to declare actions and the matching objects with annotations.
 * Have a look at the demo-module on how to use it.
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
     * Redefined action-handler to match the declarative action to a "real", implemented action.
     * The permission handling and other stuff is checked by the base-class.
     *
     * @param string $strAction
     *
     * @return string
     */
    public function action($strAction = "") {
        if ($strAction == "")
            $strActionName = $this->getAction();
        else
            $strActionName = $strAction;

        $this->strOriginalAction = $strActionName;

        if(!$this->checkMethodExistsInConcreteClass("action".ucfirst($strActionName))) {

            foreach(self::$arrActionNameMapping as $strAutoMatchAction => $strAnnotation) {
                $this->autoMatchAction($strAutoMatchAction, $strAnnotation, $strActionName);
            }
        }

        return parent::action($strActionName);
    }

    /**
     * Tries to get the name of an action (edit, delete, list, new, save) for a given object-type.
     * Example: Converts list to listOtherObject for the object class_module_demo_demo if the annotation
     *          @ objectListOtherObject class_module_demo_demo is declared
     *
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
     * Helper method to resolve the declared action to a real action, so to make list out of listOtherObject.
     * If possible, the current object-type class (based on the annotation is stored, too.
     *
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
            if(count($arrAnnotations) > 0)
                $this->setCurObjectClassName(reset($arrAnnotations));
            else
                $this->setCurObjectClassName(null);
        }
    }

    /**
     * Check if method exists in concrete class and not only in class_admin_simple
     *
     * @param $strMethod
     *
     * @internal param $strActionName
     * @return bool
     */
    protected function checkMethodExistsInConcreteClass($strMethod) {

        if(method_exists($this, $strMethod)) {
            $objRefl = new ReflectionMethod($this, $strMethod);

            if($objRefl->class != "class_admin_evensimpler") {
                return true;
            }
            else return false;
        }
        return false;
    }


    /**
     * Renders the form to create a new entry
     *
     * @throws class_exception
     * @return string
     * @permissions edit
     */
    protected function actionNew() {
        $strType = $this->getCurObjectClassName();

        if(!is_null($strType)) {
            /** @var $objEdit interface_model|class_model */
            $objEdit = new $strType();


            $objForm = $this->getAdminForm($objEdit);
            $objForm->getObjSourceobject()->setSystemid($this->getParam("systemid"));
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

        //try 1: get the object type and names based on the current object
        $objInstance = class_objectfactory::getInstance()->getObject($this->getSystemid());
        if($objInstance != null) {
            $strObjectTypeName = uniSubstr($this->getActionNameForClass("edit", $objInstance), 4);
            if($strObjectTypeName != "") {
                $strType = get_class($objInstance);
                $this->setCurObjectClassName($strType);
                $this->setStrCurObjectTypeName($strObjectTypeName);
            }
        }

        //try 2: regular, oldschool resolving based on the current action-params
        $strType = $this->getCurObjectClassName();

        if(!is_null($strType)) {

            $objEdit = new $strType($this->getSystemid());
            $objForm = $this->getAdminForm($objEdit);
            $objForm->addField(new class_formentry_hidden("", "mode"))->setStrValue("edit");

            return $objForm->renderForm(getLinkAdminHref($this->getArrModule("modul"), "save".$this->getStrCurObjectTypeName()));
        }
        else
            throw new class_exception("error editing current object type not known ", class_exception::$level_ERROR);
    }


    /**
     * Renders the general list of records
     *
     * @throws class_exception
     * @return string
     * @permissions view
     */
    protected function actionList() {
        /** @var $strType interface_model|class_model */
        $strType = $this->getCurObjectClassName();

        if(!is_null($strType)) {
            $objArraySectionIterator = new class_array_section_iterator($strType::getObjectCount($this->getSystemid()));
            $objArraySectionIterator->setPageNumber((int)($this->getParam("pv") != "" ? $this->getParam("pv") : 1));
            $objArraySectionIterator->setArraySection($strType::getObjectList($this->getSystemid(), $objArraySectionIterator->calculateStartPos(), $objArraySectionIterator->calculateEndPos()));

            //pass the internal action in order to get a proper paging
            $strOriginalAction = $this->getAction();
            $this->setAction($this->strOriginalAction);
            $strList = $this->renderList($objArraySectionIterator, false, "list".$this->getStrCurObjectTypeName());
            $this->setAction($strOriginalAction);
            return $strList;
        }
        else
            throw new class_exception("error loading list current object type not known ", class_exception::$level_ERROR);
    }

    /**
     * Creates the admin-form for a given object.
     * You may want to override this method in case you want to inject additional fields or
     * to modify the form.
     *
     * @param interface_model|class_model $objInstance
     * @return class_admin_formgenerator
     */
    protected function getAdminForm(interface_model $objInstance) {

        //already generated?
        if($this->objCurAdminForm != null && get_class($this->objCurAdminForm->getObjSourceobject()) == get_class($objInstance) && $objInstance->getSystemid() == $this->objCurAdminForm->getObjSourceobject()->getSystemid())
            return $this->objCurAdminForm;

        $objForm = new class_admin_formgenerator($this->getArrModule("modul"), $objInstance);
        $objForm->generateFieldsFromObject();
        $this->objCurAdminForm = $objForm;
        return $objForm;
    }

    /**
     * Updates the source-object based on the passed form-params
     * and synchronizes it with the database.
     *
     * @throws class_exception
     * @return string "" in case of success
     * @permissions edit
     */
    protected function actionSave() {
        $strType = $this->getCurObjectClassName();
        $strSystemId = "";

        if(!is_null($strType)) {

            /** @var $objRecord interface_model|class_model */
            $objRecord = null;

            if($this->getParam("mode") == "new") {
                $objRecord = new $strType();
                $strSystemId = $this->getSystemid();
            }
            else if($this->getParam("mode") == "edit")
                $objRecord = new $strType($this->getSystemid());

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
            throw new class_exception("error on saving current object type not known ", class_exception::$level_ERROR);


        return $this->getLang("commons_error_permissions");
    }


    /**
     * Builds the object-path of the currently selected record.
     * Used to render the path-navigation in the backend.
     * Therefore the path from the current record up to the module-record is created based on the
     * common prev-id relation.
     * Each node is rendered using getOutputNaviEntry, so you may overwrite getOutputNaviEntry in
     * order to create the links based on an object.
     *
     * @return array
     * @see class_admin_evensimpler::getOutputNaviEntry()
     */
    protected function getArrOutputNaviEntries() {

        $strOldAction = $this->getAction();
        $this->setAction($this->strOriginalAction);
        $arrPathLinks = parent::getArrOutputNaviEntries();
        $this->setAction($strOldAction);

        $arrPath = $this->getPathArray($this->getSystemid());
        
        // Render additional navigation path entries for child objects.
        foreach($arrPath as $strOneSystemid) {

            if(!validateSystemid($strOneSystemid))
                continue;

            $objInstance = class_objectfactory::getInstance()->getObject($strOneSystemid);
            if($objInstance != null) {
                $objEntry = $this->getOutputNaviEntry($objInstance);
                if($objEntry != null)
                    $arrPathLinks[] = $objEntry;
            }
            
        }

        return $arrPathLinks;
    }
    
    
    /**
     * Overwrite to generate path navigation entries for the given object.
     * If not overwritten, the entries will be skipped and won't be included into the
     * path navigation.
     * 
     * @param interface_model $objInstance
     * @return string Navigation link.
     */
    protected function getOutputNaviEntry(interface_model $objInstance) {
        return null;
    }

    /**
     * Internal redefinition in order to match the internal, "real" action to
     * the action based on the passed, declarative action.
     *
     * @return string
     */
    protected function getQuickHelp() {
        $strOldAction = $this->getAction();
        $this->setAction($this->strOriginalAction);
        $strQuickhelp = parent::getQuickHelp();
        $this->setAction($strOldAction);
        return $strQuickhelp;
    }


    /**
     * Internal redefinition in order to match the internal, "real" action to
     * the action based on the passed, declarative action.
     *
     * @return string
     */
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

    /**
     * @param \class_admin_formgenerator $objCurAdminForm
     */
    public function setObjCurAdminForm($objCurAdminForm) {
        $this->objCurAdminForm = $objCurAdminForm;
    }

    /**
     * @return \class_admin_formgenerator
     */
    public function getObjCurAdminForm() {
        return $this->objCurAdminForm;
    }


}

