<?php
/*"******************************************************************************************************
*   (c) 2007-2015 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
********************************************************************************************************/

/**
 * Model for a single aspect. An aspect is a filter-type that can be applied to the backend.
 * E.g. there could be different dashboard for each aspect or a module may be visible only for given
 * aspects.
 * Aspects should and will not replace the permissions! If a module was removed from an aspect, it may
 * still be accessible directly due to sufficient permissions.
 * This means aspects are rather some kind of view-filter then business-logic filters.
 *
 * @package module_system
 * @since 3.4
 * @author sidler@mulchprod.de
 * @targetTable aspects.aspect_id
 *
 * @module system
 * @moduleId _system_modul_id_
 */
class class_module_system_aspect extends class_model implements interface_model, interface_admin_listable {

    /**
     * @var string
     * @tableColumn aspects.aspect_name
     * @tableColumnDatatype char254
     * @fieldType text
     * @fieldMandatory
     *
     * @addSearchIndex
     */
    private $strName = "";

    /**
     * @var bool
     * @tableColumn aspects.aspect_default
     * @tableColumnDatatype int
     * @fieldType yesno
     * @fieldMandatory
     */
    private $bitDefault = 0;

    private static $STR_SESSION_ASPECT_KEY = "STR_SESSION_ASPECT_KEY";
    private static $STR_SESSION_ASPECT_OBJECT = "STR_SESSION_ASPECT_OBJECT";



    /**
     * Returns the name to be used when rendering the current object, e.g. in admin-lists.
     *
     * @return string
     */
    public function getStrDisplayName() {
        //try to load the name from the lang-files
        $strLabel = $this->getLang("aspect_".$this->getStrName(), "system");
        if($strLabel != "!aspect_".$this->getStrName()."!")
            return $strLabel;
        else
            return $this->getStrName();
    }

    /**
     * Returns the icon the be used in lists.
     * Please be aware, that only the filename should be returned, the wrapping by getImageAdmin() is
     * done afterwards.
     *
     * @return string the name of the icon, not yet wrapped by getImageAdmin()
     */
    public function getStrIcon() {
        return "icon_aspect";
    }

    /**
     * In nearly all cases, the additional info is rendered left to the action-icons.
     *
     * @return string
     */
    public function getStrAdditionalInfo() {
        return $this->getBitDefault() == 1 ? " (".$this->getLang("aspect_isDefault", "system").")" : "";
    }

    /**
     * If not empty, the returned string is rendered below the common title.
     *
     * @return string
     */
    public function getStrLongDescription() {
        return "";
    }

    /**
     * saves the current object with all its params back to the database
     *
     * @return bool
     */
    protected function updateStateToDb() {

        //if no other aspect exists, we have a new default aspect
        if(class_module_system_aspect::getObjectCount() == 0) {
            $this->setBitDefault(1);
        }

        if($this->getBitDefault() == 1)
            self::resetDefaultAspect();

        return parent::updateStateToDb();
    }


    /**
     * Returns an array of all aspects available
     *
     * @return class_module_system_aspect[]
     * @static
     */
    public static function getActiveObjectList() {
        $objOrm = new class_orm_objectlist();
        $objOrm->addWhereRestriction(new class_orm_objectlist_systemstatus_restriction(class_orm_comparator_enum::NotEqual(), 0));
        return $objOrm->getObjectList(__CLASS__, "");
    }


    /**
     * Returns the number of aspects installed in the system
     *
     * @return int
     */
    public static function getActiveObjectCount() {
        $objOrm = new class_orm_objectlist();
        $objOrm->addWhereRestriction(new class_orm_objectlist_systemstatus_restriction(class_orm_comparator_enum::NotEqual(), 0));
        return $objOrm->getObjectCount(__CLASS__);
    }


    /**
     * Resets all default aspects.
     * Afterwards, no default aspect is available!
     *
     * @return bool
     */
    public static function resetDefaultAspect() {
        $strQuery = "UPDATE "._dbprefix_."aspects
                     SET aspect_default = 0";
        return class_carrier::getInstance()->getObjDB()->_pQuery($strQuery, array());
    }

    /**
     * Returns the default aspect, defined in the admin.
     * This takes permissions into account!
     *
     * @param bool $bitIgnorePermissions
     *
     * @return class_module_system_aspect null if no aspect is set up
     */
    public static function getDefaultAspect($bitIgnorePermissions = false) {
        $objORM = new class_orm_objectlist();
        $objORM->addWhereRestriction(new class_orm_objectlist_restriction("AND system_status = 1", array()));
        $objORM->addWhereRestriction(new class_orm_objectlist_restriction("AND aspect_default = 1", array()));
        /** @var class_module_system_aspect $objAspect */
        $objAspect = $objORM->getSingleObject(get_called_class());

        if($objAspect != null  && ($bitIgnorePermissions || $objAspect->rightView())) {
            return $objAspect;
        }
        else {
            $arrAspects = class_module_system_aspect::getActiveObjectList();
            if(count($arrAspects) > 0) {
                foreach($arrAspects as $objOneAspect)
                    if($objOneAspect->rightView())
                        return $objOneAspect;
            }

            return null;
        }
    }

    /**
     * Returns an aspect by name, ignoring the status
     *
     * @param string $strName
     *
     * @return class_module_system_aspect or null if not found
     */
    public static function getAspectByName($strName) {
        $objORM = new class_orm_objectlist();
        $objORM->addWhereRestriction(new class_orm_objectlist_restriction("AND aspect_name = ?", array($strName)));
        return $objORM->getSingleObject(get_called_class());
    }


    /**
     * Returns the aspect currently selected by the user.
     * If no aspect was selected before, the default aspect is returned instead.
     * In addition, the current params are processed in order to react on changes made
     * by the user / external sources.
     *
     * @return class_module_system_aspect null if no aspect is set up
     */
    public static function getCurrentAspect() {

        //process params maybe existing
        if(defined("_admin_") && _admin_ && class_carrier::getInstance()->getParam("aspect") != "" && validateSystemid(class_carrier::getInstance()->getParam("aspect"))) {
            self::setCurrentAspectId(class_carrier::getInstance()->getParam("aspect"));
        }

        //aspect registered in session?
        if(validateSystemid(class_carrier::getInstance()->getObjSession()->getSession(class_module_system_aspect::$STR_SESSION_ASPECT_KEY))) {
            if(class_carrier::getInstance()->getObjSession()->getSession(class_module_system_aspect::$STR_SESSION_ASPECT_OBJECT, class_session::$intScopeRequest) !== false) {
                return class_carrier::getInstance()->getObjSession()->getSession(class_module_system_aspect::$STR_SESSION_ASPECT_OBJECT, class_session::$intScopeRequest);
            }
            else {
                $objAspect = new class_module_system_aspect(class_carrier::getInstance()->getObjSession()->getSession(class_module_system_aspect::$STR_SESSION_ASPECT_KEY));
                class_carrier::getInstance()->getObjSession()->setSession(class_module_system_aspect::$STR_SESSION_ASPECT_OBJECT, $objAspect, class_session::$intScopeRequest);
                return $objAspect;
            }
        }
        else {
            $objAspect = class_module_system_aspect::getDefaultAspect();
            if($objAspect != null)
                self::setCurrentAspectId($objAspect->getSystemid());
            return $objAspect;
        }
    }

    /**
     * Wrapper to getCurrentAspect(), returning the ID of the aspect currently selected.
     * If no aspect is selected, an empty string is returned.
     *
     * @return string
     */
    public static function getCurrentAspectId() {
        $objAspect = class_module_system_aspect::getCurrentAspect();
        if($objAspect != null)
            return $objAspect->getSystemid();
        else
            return "";
    }

    /**
     * Saves an aspect id as the current active one - but only if the previous one was changed
     *
     * @param string $strAspectId
     * @return void
     */
    public static function setCurrentAspectId($strAspectId) {
        if(validateSystemid($strAspectId) && $strAspectId != class_carrier::getInstance()->getObjSession()->getSession(class_module_system_aspect::$STR_SESSION_ASPECT_KEY)) {
            class_carrier::getInstance()->getObjSession()->setSession(class_module_system_aspect::$STR_SESSION_ASPECT_KEY, $strAspectId);
            class_carrier::getInstance()->getObjSession()->setSession(class_module_system_aspect::$STR_SESSION_ASPECT_OBJECT, new class_module_system_aspect($strAspectId), class_session::$intScopeRequest);
        }
    }


    /**
     * @param string $strName
     * @return void
     */
    public function setStrName($strName) {
        $this->strName = $strName;
    }

    /**
     * @param string $bitDefault
     * @return void
     */
    public function setBitDefault($bitDefault) {
        $this->bitDefault = $bitDefault;
    }

    /**
     * @return string
     */
    public function getStrName() {
        return $this->strName;
    }

    /**
     * @return bool
     */
    public function getBitDefault() {
        return $this->bitDefault;
    }

}
