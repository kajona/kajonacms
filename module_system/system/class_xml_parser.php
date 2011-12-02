<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2011 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$                                       *
********************************************************************************************************/

/**
 * Class to handle xml-files,
 * parsing them using a dom parser
 *
 * @package module_system
 */
class class_xml_parser {

	private $objDocument = null;
	private $strFile = "";


	/**
	 * Creates an instance of the parser
	 *
	 * @param string $strFile
	 */
	public function __construct($strFile = "") {
		if($strFile != "")
		   $this->loadFile($strFile);
	}



	/**
	 * Loads a xml-file
	 *
	 * @param string $strFile
	 * @return bool
	 */
	public function loadFile($strFile) {
	    $arrModules = get_loaded_extensions();
	    if(!in_array("dom", $arrModules))
	        throw new class_exception("no DOM-Extension installed", class_exception::$level_ERROR);

	    if(strpos($strFile, _realpath_) === false)
	        $strFile = _realpath_.$strFile;

		$this->objDocument = new DOMDocument();
		if(file_exists($strFile)) {
			if(@$this->objDocument->load($strFile)) {
				$this->strFile = $strFile;
				return true;
			}
		}
		return false;
	}


	/**
	 * Creates a DOM-Document using the passed string
	 *
	 * @param string $strString
	 * @return bool
	 */
	public function loadString($strString) {
	    $arrModules = get_loaded_extensions();
	    if(!in_array("dom", $arrModules))
	        throw new class_exception("no DOM-Extension installed", class_exception::$level_ERROR);

        $this->objDocument = new DOMDocument();
        return @$this->objDocument->loadXML($strString);
	}


	/**
	 * Creates an array using the current document
	 *
	 * @return array
	 */
	public function xmlToArray() {
		$arrXML = array();
		$this->parseXmlToArray($this->objDocument, $arrXML);
		return $arrXML;
	}

	/**
	 * Walks through the document to create an array
	 *
	 * @param node $domNode
	 * @param array $arrXML
	 */
	private function parseXmlToArray($domNode, &$arrXML) {
		//Resolve the reference to work on it
		$arrPointer = &$arrXML;
		//Get the first child of the current node
		$domNode = $domNode->firstChild;
		while($domNode != null) {
			switch($domNode->nodeType) {
				case XML_TEXT_NODE:
                case XML_CDATA_SECTION_NODE:
				//Here we have a text node, so get the value if it isn't empty
				if(trim($domNode->nodeValue))
                    $arrPointer['value'] = trim($domNode->nodeValue);
                    break;
				case XML_ELEMENT_NODE:
                    //Here we have another node, so resolve it
                    $arrPointer = &$arrXML[$domNode->nodeName][];
                    //Maybe there are some Attributes..
                    if($domNode->hasAttributes()) {
                        $arrAttributes = $domNode->attributes;
                        foreach($arrAttributes as $objAttribute) {
                            $arrPointer['attributes'][$objAttribute->name] = $objAttribute->value;
                        }
                    }
                    break;
			}
			//If we have more childs, call them
			if($domNode->hasChildNodes())
			    $this->parseXmlToArray($domNode, $arrPointer);
			//And get the values from the siblings
			$domNode = $domNode->nextSibling;
		}
	}


	/**
	 * Used mainly for debugging, this method draws the current xml-tree to
	 * std::out
	 *
	 */
	public function drawXmlTree() {
		$arrXML = $this->xmlToArray();
		$this->drawRecursive(0, $arrXML);
	}

	/**
	 * Helper to walk through the arrays
	 *
	 * @param int $intLevel
	 * @param array $arrDraw
	 */
	private function drawRecursive($intLevel, $arrDraw) {
		$intI = 1;
		foreach($arrDraw as $strKey => $value) {
			//Print out key
			for($intI = 0; $intI < $intLevel; $intI++) {
				echo "   ";
			}
			echo "|--".$strKey ."";
			//Call subarrays
			if(is_array($value)) {
				echo "\n";
				$this->drawRecursive($intLevel+1, $value);
			}
			else
				echo " ( ".$value." )\n";
		}
	}

	/**
	 * Validated the current document against the defined DTD
	 *
	 * @return bool
	 */
	public function xmlValidateDTD() {
		if($this->objDocument != null) {
			if($this->objDocument->validate() !== false)
			return true;
		}
		return false;
	}

	/**
	 * Validated the current document against the passed schema
	 *
	 * @param string $strSchema schema to load
	 * @return bool
	 */
	public function xmlValidateSchema($strSchema) {
		if(is_file($strSchema)) {
			if($this->objDocument != null) {
				if(@$this->objDocument->schemaValidate($strSchema) !== false)
					return true;
			}
		}
		return false;
	}


	/**
	 * Looks up all values of elements named like the passed name
	 *
	 * @param string $strName
	 * @return array
	 */
	public function getElementValueByName($strName) {
		$arrReturn = array();
		if($this->objDocument != null) {
			$arrNodeList = $this->objDocument->getElementsByTagName($strName);
			if(count($arrNodeList) > 0) {
				foreach($arrNodeList as $arrOneNode) {
					$arrReturn[] = $arrOneNode->nodeValue;
				}
			}
		}
		return $arrReturn;
	}


	/**
	 * Returns an array of all tags having the passed name.
	 * NOTE: You get an array containing just the attributes, not the child elements!
	 *
	 * @param array $strTagName
	 */
	public function getNodesAttributesAsArray($strTagName) {
	    $arrReturn = array();
	    $intNodeCounter = 0;
        $listNodes = $this->objDocument->getElementsByTagName($strTagName);
        for($i=0; $i < $listNodes->length; $i++) {
            $objNode = $listNodes->item($i);
            if($objNode->hasAttributes()) {
                $arrReturn[$intNodeCounter] = array();
                $arrAttributes = $objNode->attributes;
                $intAttrCounter = 0;
                foreach($arrAttributes as $objAttribute) {
                    $arrReturn[$intNodeCounter][$intAttrCounter] = array();
                    $arrReturn[$intNodeCounter][$intAttrCounter]["name"] = $objAttribute->name;
                    $arrReturn[$intNodeCounter][$intAttrCounter]["value"] = $objAttribute->value;
                    $intAttrCounter++;
                }
                $intNodeCounter++;
            }
        }
        return $arrReturn;
	}

}

