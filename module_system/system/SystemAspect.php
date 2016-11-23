<?php
/*"******************************************************************************************************
*   (c) 2007-2016 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
********************************************************************************************************/

namespace Kajona\System\System;


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
 *
 * @sortManager Kajona\System\System\CommonSortmanager
 */
class SystemAspect extends Model implements ModelInterface, AdminListableInterface
{

    /**
     * @var string
     * @tableColumn aspects.aspect_name
     * @tableColumnDatatype char254
     * @fieldType Kajona\System\Admin\Formentries\FormentryText
     * @fieldMandatory
     * @fieldLabel form_aspect_name
     *
     * @addSearchIndex
     */
    private $strName = "";

    /**
     * @var bool
     * @tableColumn aspects.aspect_default
     * @tableColumnDatatype int
     * @fieldType Kajona\System\Admin\Formentries\FormentryYesno
     * @fieldMandatory
     * @fieldLabel form_aspect_default
     */
    private $bitDefault = 0;

    private static $STR_SESSION_ASPECT_KEY = "STR_SESSION_ASPECT_KEY";
    private static $STR_SESSION_ASPECT_OBJECT = "STR_SESSION_ASPECT_OBJECT";

    public function rightEdit()
    {
        return parent::rightEdit() && parent::rightRight5();
    }

    public function rightDelete()
    {
        return parent::rightDelete() && parent::rightRight5();
    }


    /**
     * Returns the name to be used when rendering the current object, e.g. in admin-lists.
     *
     * @return string
     */
    public function getStrDisplayName()
    {
        //try to load the name from the lang-files
        $strLabel = $this->getLang("aspect_".$this->getStrName(), "system");
        if ($strLabel != "!aspect_".$this->getStrName()."!") {
            return $strLabel;
        } else {
            return $this->getStrName();
        }
    }

    /**
     * Returns the icon the be used in lists.
     * Please be aware, that only the filename should be returned, the wrapping by getImageAdmin() is
     * done afterwards.
     *
     * @return string the name of the icon, not yet wrapped by getImageAdmin()
     */
    public function getStrIcon()
    {
        return "icon_aspect";
    }

    /**
     * In nearly all cases, the additional info is rendered left to the action-icons.
     *
     * @return string
     */
    public function getStrAdditionalInfo()
    {
        return $this->getBitDefault() == 1 ? " (".$this->getLang("aspect_isDefault", "system").")" : "";
    }

    /**
     * If not empty, the returned string is rendered below the common title.
     *
     * @return string
     */
    public function getStrLongDescription()
    {
        return "";
    }

    /**
     * saves the current object with all its params back to the database
     *
     * @return bool
     */
    protected function updateStateToDb()
    {

        //if no other aspect exists, we have a new default aspect
        if (SystemAspect::getObjectCountFiltered() == 0) {
            $this->setBitDefault(1);
        }

        if ($this->getBitDefault() == 1) {
            self::resetDefaultAspect();
        }

        return parent::updateStateToDb();
    }


    /**
     * Returns an array of all aspects available
     *
     * @return SystemAspect[]
     * @static
     */
    public static function getActiveObjectList()
    {
        $objOrm = new OrmObjectlist();
        $objOrm->addWhereRestriction(new OrmObjectlistSystemstatusRestriction(OrmComparatorEnum::NotEqual(), 0));
        return $objOrm->getObjectList(__CLASS__, "");
    }


    /**
     * Returns the number of aspects installed in the system
     *
     * @return int
     */
    public static function getActiveObjectCount()
    {
        $objOrm = new OrmObjectlist();
        $objOrm->addWhereRestriction(new OrmObjectlistSystemstatusRestriction(OrmComparatorEnum::NotEqual(), 0));
        return $objOrm->getObjectCount(__CLASS__);
    }


    /**
     * Resets all default aspects.
     * Afterwards, no default aspect is available!
     *
     * @return bool
     */
    public static function resetDefaultAspect()
    {
        $strQuery = "UPDATE "._dbprefix_."aspects
                     SET aspect_default = 0";
        return Carrier::getInstance()->getObjDB()->_pQuery($strQuery, array());
    }

    /**
     * Returns the default aspect, defined in the admin.
     * This takes permissions into account!
     *
     * @param bool $bitIgnorePermissions
     *
     * @return SystemAspect null if no aspect is set up
     */
    public static function getDefaultAspect($bitIgnorePermissions = false)
    {
        $objORM = new OrmObjectlist();
        $objORM->addWhereRestriction(new OrmObjectlistRestriction("AND system_status = 1", array()));
        $objORM->addWhereRestriction(new OrmObjectlistRestriction("AND aspect_default = 1", array()));
        /** @var SystemAspect $objAspect */
        $objAspect = $objORM->getSingleObject(get_called_class());

        if ($objAspect != null && ($bitIgnorePermissions || $objAspect->rightView())) {
            return $objAspect;
        } else {
            $arrAspects = SystemAspect::getActiveObjectList();
            if (count($arrAspects) > 0) {
                foreach ($arrAspects as $objOneAspect) {
                    if ($objOneAspect->rightView()) {
                        return $objOneAspect;
                    }
                }
            }

            return null;
        }
    }

    /**
     * Returns an aspect by name, ignoring the status
     *
     * @param string $strName
     *
     * @return SystemAspect or null if not found
     */
    public static function getAspectByName($strName)
    {
        $objORM = new OrmObjectlist();
        $objORM->addWhereRestriction(new OrmObjectlistRestriction("AND aspect_name = ?", array($strName)));
        return $objORM->getSingleObject(get_called_class());
    }


    /**
     * Returns the aspect currently selected by the user.
     * If no aspect was selected before, the default aspect is returned instead.
     * In addition, the current params are processed in order to react on changes made
     * by the user / external sources.
     *
     * @return SystemAspect null if no aspect is set up
     */
    public static function getCurrentAspect()
    {

        //process params maybe existing
        if (defined("_admin_") && _admin_ && Carrier::getInstance()->getParam("aspect") != "" && validateSystemid(Carrier::getInstance()->getParam("aspect"))) {
            self::setCurrentAspectId(Carrier::getInstance()->getParam("aspect"));
        }

        //aspect registered in session?
        if (validateSystemid(Carrier::getInstance()->getObjSession()->getSession(SystemAspect::$STR_SESSION_ASPECT_KEY))) {
            if (Carrier::getInstance()->getObjSession()->getSession(SystemAspect::$STR_SESSION_ASPECT_OBJECT, Session::$intScopeRequest) !== false) {
                return Carrier::getInstance()->getObjSession()->getSession(SystemAspect::$STR_SESSION_ASPECT_OBJECT, Session::$intScopeRequest);
            } else {
                $objAspect = new SystemAspect(Carrier::getInstance()->getObjSession()->getSession(SystemAspect::$STR_SESSION_ASPECT_KEY));
                Carrier::getInstance()->getObjSession()->setSession(SystemAspect::$STR_SESSION_ASPECT_OBJECT, $objAspect, Session::$intScopeRequest);
                return $objAspect;
            }
        }

        $objAspect = SystemAspect::getDefaultAspect();
        if ($objAspect != null) {
            self::setCurrentAspectId($objAspect->getSystemid());
        }
        return $objAspect;
    }

    /**
     * Wrapper to getCurrentAspect(), returning the ID of the aspect currently selected.
     * If no aspect is selected, an empty string is returned.
     *
     * @return string
     */
    public static function getCurrentAspectId()
    {
        $objAspect = SystemAspect::getCurrentAspect();
        if ($objAspect != null) {
            return $objAspect->getSystemid();
        } else {
            return "";
        }
    }

    /**
     * Saves an aspect id as the current active one - but only if the previous one was changed
     *
     * @param string $strAspectId
     *
     * @return void
     */
    public static function setCurrentAspectId($strAspectId)
    {
        if (validateSystemid($strAspectId) && $strAspectId != Carrier::getInstance()->getObjSession()->getSession(SystemAspect::$STR_SESSION_ASPECT_KEY)) {
            Carrier::getInstance()->getObjSession()->setSession(SystemAspect::$STR_SESSION_ASPECT_KEY, $strAspectId);
            Carrier::getInstance()->getObjSession()->setSession(SystemAspect::$STR_SESSION_ASPECT_OBJECT, new SystemAspect($strAspectId), Session::$intScopeRequest);
        }
    }


    /**
     * @param string $strName
     *
     * @return void
     */
    public function setStrName($strName)
    {
        $this->strName = $strName;
    }

    /**
     * @param string $bitDefault
     *
     * @return void
     */
    public function setBitDefault($bitDefault)
    {
        $this->bitDefault = $bitDefault;
    }

    /**
     * @return string
     */
    public function getStrName()
    {
        return $this->strName;
    }

    /**
     * @return bool
     */
    public function getBitDefault()
    {
        return $this->bitDefault;
    }

}
