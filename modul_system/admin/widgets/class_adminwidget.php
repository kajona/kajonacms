<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2008 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
* 																										*
* 	class_adminwidget.php																				*
* 	base-class to be extended by all adminwidgets														*																				*
*																										*
*-------------------------------------------------------------------------------------------------------*
*	$Id$	                                    *
********************************************************************************************************/

/**
 * Base class to be extended by all adminwidgets.
 * Holds a few methods to create a framework-like behaviour 
 *
 * @package modul_system
 */
abstract class class_adminwidget {

    private $arrModule = array();
    private $arrFields = array();
    private $arrPersistenceKeys = array();
    private $strSytemid = "";
    
    /**
     * instance of class_db
     *
     * @var class_db
     */
    private $objDb;
    
    /**
     * instance of class_toolkit
     *
     * @var class_toolkit_admin
     */
    protected $objToolkit;
    
    /**
     * instance of class_texte
     *
     * @var class_texte
     */
    private $objTexte;
    
    public function __construct($arrModule = array()) {
        $this->arrModule["p_name"] 				= "modul_system";
    	$this->arrModule["p_author"] 			= "sidler@mulchprod.de";
    	$this->arrModule["p_moduleId"] 			= _system_modul_id_;
    	$this->arrModule["p_modul"]				= "system";
    	
    	$this->arrModule = array_merge($this->arrModule, $arrModule);
    	
    	$this->objDb = class_carrier::getInstance()->getObjDB();
    	$this->objToolkit = class_carrier::getInstance()->getObjToolkit("admin");
    	$this->objTexte = class_carrier::getInstance()->getObjText();
    	
    }
    
    /**
     * Use this method to tell the widgets whicht keys of the $arrFields should
     * be loaded from and be persitsted to the database
     *
     * @param array $arrKeys
     */
    protected final function setPersistenceKeys($arrKeys) {
        $this->arrPersistenceKeys = $arrKeys;
    }

    /**
     * This method invokes the rendering of the widget. Calls
     * the implementing class.
     *
     * @return string
     */
    public final function generateWidgetOutput() {
        return $this->getWidgetOutput();
    }
    
    /**
     * Overwrite this method!
     *
     * @return string
     * @see interface_adminwidget::getWidgetOutput()
     */
    public function getWidgetOutput() {
        return "";
    }
    
    /**
     * Returns the current fields as a serialized array.
     *
     * @return string
     */
    public final function getFieldsAsString() {
        $arrFieldsToPersist = array();
        foreach($this->arrPersistenceKeys as $strOneKey) {
            $arrFieldsToPersist[$strOneKey] = $this->getFieldValue($strOneKey);
        }
        
        $strArraySerialized = serialize($arrFieldsToPersist);
        return $strArraySerialized;
    }
    
    /**
     * Takes the current fields serialized and retransforms the contents
     *
     * @param string $strContent
     */
    public final function setFieldsAsString($strContent) {
        $arrFieldsToLoad = unserialize($strContent);
        foreach($this->arrPersistenceKeys as $strOneKey) {
            if(isset($arrFieldsToLoad[$strOneKey])) {
                $this->setFieldValue($strOneKey, $arrFieldsToLoad[$strOneKey]);   
            }
        }
    }

    /**
     * Pass an array of values. The method looks for fields to be loaded into
     * the internal arrays.
     *
     * @param array $arrFields
     */
    public final function loadFieldsFromArray($arrFields) {
        foreach($this->arrPersistenceKeys as $strOneKey) {
            if(isset($arrFields[$strOneKey])) {
                $this->setFieldValue($strOneKey, $arrFields[$strOneKey]);   
            }
        }
    }
    
    /**
     * Loads a text-fragement from the textfiles
     *
     * @param string $strKey
     * @return string
     */
    public final function getText($strKey) {
        return $this->objTexte->getText($strKey, "adminwidget", "admin");
    }
    
    /**
     * Looks up a value in the fields-array
     *
     * @param string $strFieldName
     * @return mixed
     */
    protected final function getFieldValue($strFieldName) {
        if(isset($this->arrFields[$strFieldName]))
            return $this->arrFields[$strFieldName];
        else
            return "";    
    }
    
    /**
     * Sets the value of a given field
     *
     * @param string $strFieldName
     * @param mixed $mixedValue
     */
    protected final function setFieldValue($strFieldName, $mixedValue) {
        $this->arrFields[$strFieldName] = $mixedValue;
    }
    
    /**
     * Sets the systemid of the current widget
     *
     * @param string $strSystemid
     */
    public final function setSystemid($strSystemid) {
        $this->strSytemid = $strSystemid;
    }

    /**
     * Returns the systemid of the current widget
     *
     * @return string
     */
    public final function getSystemid() {
        return $this->strSytemid;   
    }
    
//--- Layout/Content functions --------------------------------------------------------------------------

    /**
     * Use this method to place a formatted text in the widget
     *
     * @param string $strText
     * @return string
     */
    protected final function widgetText($strText) {
        return $this->objToolkit->adminwidgetText($strText);
    }
    
    /**
     * Use this method to generate a separator / divider to split up 
     * the widget in logical sections.
     *
     * @return string
     */
    protected final function widgetSeparator() {
        return $this->objToolkit->adminwidgetSeparator();
    }
}


?>