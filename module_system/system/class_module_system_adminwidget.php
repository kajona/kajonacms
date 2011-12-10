<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2011 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$	                        *
********************************************************************************************************/

/**
 * Class to represent a single adminwidget
 *
 * @package module_system
 * @author sidler@mulchprod.de
 */
class class_module_system_adminwidget extends class_model implements interface_model {

    private $strClass = "";
    private $strContent = "";


	/**
     * Constructor to create a valid object
     *
     * @param string $strSystemid (use "" on new objects)
     */
    public function __construct($strSystemid = "") {
        $this->setArrModuleEntry("modul", "system");
        $this->setArrModuleEntry("moduleId", _system_modul_id_);

		parent::__construct($strSystemid);

    }

    /**
     * @see class_model::getObjectTables();
     * @return array
     */
    protected function getObjectTables() {
        return array(_dbprefix_."adminwidget" => "adminwidget_id");
    }

    /**
     * Returns the name to be used when rendering the current object, e.g. in admin-lists.
     * @return string
     */
    public function getStrDisplayName() {
        return $this->getStrClass();
    }


    /**
     * Inits the object by loading the values from the db
     *
     */
    protected function initObjectInternal() {
        $strQuery = "SELECT * FROM "._dbprefix_."adminwidget,
        						   "._dbprefix_."system
        				WHERE system_id = adminwidget_id
        				  AND system_id = ? ";

        $arrRow = $this->objDB->getPRow($strQuery, array($this->getSystemid()));
        if(count($arrRow) > 0) {
            $this->setStrClass($arrRow["adminwidget_class"]);
            $this->setStrContent($arrRow["adminwidget_content"]);
        }
    }

    /**
     * Updates the values of the current widget to the db
     * @return bool
     */
    protected function updateStateToDb() {
        $strQuery = "UPDATE "._dbprefix_."adminwidget
                   SET adminwidget_class = ?,
                       adminwidget_content = ?
                 WHERE adminwidget_id = ? ";
        return $this->objDB->_pQuery($strQuery, array($this->getStrClass(), $this->getStrContent(), $this->getSystemid()), array(true, false, true) );
    }

    /**
     * Deletes the current object from the database
     *
     * @return bool
     */
    protected function deleteObjectInternal() {
	    $strQuery = "DELETE FROM "._dbprefix_."adminwidget
                             WHERE adminwidget_id = ?";
        if($this->objDB->_pQuery($strQuery, array($this->getSystemid()))) {
            return true;
        }
        return false;
    }



    /**
     * Looks up all widgets available in the filesystem.
     * ATTENTION: returns the class-name representation of a file, NOT the filename itself.
     *
     * @return array
     */
    public function getListOfWidgetsAvailable() {
        $arrReturn = array();

        $arrFiles = class_resourceloader::getInstance()->getFolderContent("/admin/widgets/", array(".php"));

        foreach($arrFiles as $strOneFile) {
            if($strOneFile != "interface_adminwidget.php" && $strOneFile != "class_adminwidget.php") {
                $arrReturn[] = uniStrReplace(".php", "", $strOneFile);
            }
        }

        return $arrReturn;
    }

    /**
     * Creates the concrete widget represented by this model-element
     *
     * @return class_adminwidget
     */
    public function getConcreteAdminwidget() {
        $objWidget = new $this->strClass();
        //Pass the field-values
        $objWidget->setFieldsAsString($this->getStrContent());
        $objWidget->setSystemid($this->getSystemid());
        return $objWidget;
    }

    //--- GETTERS / SETTERS ---------------------------------------------------------------------------------

    public function setStrClass($strClass) {
        $this->strClass = $strClass;
    }
    public function setStrContent($strContent) {
        $this->strContent = $strContent;
    }

    public function getStrClass() {
        return $this->strClass;
    }
    public function getStrContent() {
        return $this->strContent;
    }

}


