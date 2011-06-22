<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2011 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$                                               *
********************************************************************************************************/


/**
 * class_csv, used to access data stored in csv-files.
 * This class can either be used to write to csv-files or to read from csv-files
 *
 * @package modul_system
 * @author sidler@mulchprod.de
 */
class class_csv {

    private $arrMapping = null;
    private $arrData = null;
    private $strFilename = null;
    private $strDelimiter = null;
    private $strTextEncloser = null;
    private $intImportRowOffset = 0;


    /**
     * Use a ';' as delimiter
     *
     * @var string
     */
    public static $str_delimiter_semikolon = ";";

    /**
     * Use a ',' as delimiter
     *
     * @var string
     */
    public static $str_delimiter_comma = ",";



	/**
	 * Constructor
	 *
	 */
	public function __construct($strDelimiter = "") {
		$this->arrModul["name"] 		= "class_csv";
		$this->arrModul["author"] 		= "sidler@mulchprod.de";
		$this->arrModul["moduleId"]		= _system_modul_id_;

		if($strDelimiter == "") {
		    $this->strDelimiter = class_csv::$str_delimiter_comma;
		} else {
		    $this->strDelimiter = $strDelimiter;
		}

	    // Try to overwrite PHP memory-limit so also large files can be processed
		if (class_carrier::getInstance()->getObjConfig()->getPhpIni("memory_limit") < 50)
			@ini_set("memory_limit", "50M");
	}


	/**
	 * Creates an array containing the rows given in the csv-file
	 *
	 * @return bool
	 * @throws class_exception
	 */
	public function createArrayFromFile() {
	    //all needed params given?
	    if($this->arrMapping != "" && $this->strFilename != "") {

            //init final array
            $this->arrData = array();
            $arrFinalArray = array();

            //open pointer on file
            $objFilesystem = new class_filesystem();
            $objFilesystem->openFilePointer($this->strFilename, "r");

            $strRow = $objFilesystem->readLineFromFile();
            if($strRow === false)
                return false;

            if($this->intImportRowOffset > 0) {
                for($intI = 0; $intI < $this->intImportRowOffset; $intI++)
                    $strRow = $objFilesystem->readLineFromFile();
            }

	        //first row are the headers
            $arrHeader = explode($this->strDelimiter, $strRow);

            $strRow = $objFilesystem->readLineFromFile();
            while($strRow !== false) {
                if(uniStrlen($strRow) > 0) {
                    $arrOneRow = explode($this->strDelimiter, $strRow);
                    $arrCSVRow = array();
                    foreach($arrHeader as $intKey => $strHeader) {
                        $strHeader = trim($strHeader);
                        //include the mapping specified
                        //add an encloser?
                        if($this->strTextEncloser != null) {
                            $strHeader = uniStrReplace($this->strTextEncloser, "", trim($strHeader));
                        }
                        $strRowKey = $this->arrMapping[$strHeader];
                        $strValue = $arrOneRow[$intKey];
                        //remove an encloser?
                        if($this->strTextEncloser != null) {
                            $strValue = uniStrReplace($this->strTextEncloser, "", trim($strValue));
                        }
                        $arrCSVRow[$strRowKey] = $strValue;
                    }
                    //add to final array
                    $arrFinalArray[] = $arrCSVRow;
                }

                $strRow = $objFilesystem->readLineFromFile();
            }

            $objFilesystem->closeFilePointer();

            $this->setArrData($arrFinalArray);

	        return true;
	    }
	    else {
	        throw new class_exception("cannot proceed, needed values (mapping or filename) missing", class_exception::$level_ERROR);
	    }
	}


	/**
	 * Writes the current array of data to the given csv-file or directly to the browser.
	 * Make sure to have set all needed values before, otherwise
	 * an exception is thrown
	 *
	 * @return bool
     * @param bool $bitStreamToBrowser
     * @param bool $bitExcludeHeaders skip the header-row in the output, generated based on the mapping
	 * @throws class_exception
	 */
	public function writeArrayToFile($bitStreamToBrowser = false, $bitExcludeHeaders = false) {
	    //all needed values set before?
	    if($this->arrData != null && $this->arrMapping != null && $this->strFilename != null) {
	        //create file-content. use a file-pointer to avoid max-mem-errors

	        $objFilesystem = new class_filesystem();
	        //open file
            if($bitStreamToBrowser) {
                header("Content-type: text/csv");
                header("Content-Disposition: attachment; filename=".saveUrlEncode(trim(basename($this->strFilename))));
            }
            else
                $objFilesystem->openFilePointer($this->strFilename);
            
	        //the first row should contain the row-names
            if(!$bitExcludeHeaders) {
                $strRow = "";
                foreach ($this->arrMapping as $strSourceCol => $strTagetCol) {
                    //add enclosers?
                    if($this->strTextEncloser != null) {
                        $strTagetCol = $this->strTextEncloser.$strTagetCol.$this->strTextEncloser;
                    }
                    $strRow .= $strTagetCol.$this->strDelimiter;
                }
                //remove last delimiter, eol
                $strRow = uniSubstr($strRow, 0, (uniStrlen($this->strDelimiter))*-1);
                //add a linebreak
                $strRow .= "\n";
                //write header to file
                if($bitStreamToBrowser)
                    echo($strRow);
                else
                    $objFilesystem->writeToFile($strRow);
            }
            
	        //iterate over the data array to write it to the file
	        foreach ($this->arrData as $arrOneRow) {
	            $strRow = "";
	            foreach($this->arrMapping as $strSourceCol => $strTargetCol) {
	                if(isset($arrOneRow[$strSourceCol])) {
	                    $strEntry = $arrOneRow[$strSourceCol];
	                    //escape the delimiter maybe occuring in the text
	                    $strEntry = uniStrReplace($this->strDelimiter, "\\".$this->strDelimiter, $strEntry);
	                    //add enclosers?
	                    if($this->strTextEncloser != null) {
	                        $strEntry = $this->strTextEncloser.$strEntry.$this->strTextEncloser;
	                    }
	                }
	                else
	                   $strEntry = "";

	                $strRow .= $strEntry.$this->strDelimiter;
	            }
	            //remove last delimiter, eol
	            $strRow = uniSubstr($strRow, 0, (uniStrlen($this->strDelimiter))*-1);
	            //add linebreak
	            $strRow .= "\n";
	            //and write to file
                if($bitStreamToBrowser)
                    echo($strRow);
                else
                    $objFilesystem->writeToFile($strRow);
	        }
	        //and close the filepointer...
            if(!$bitStreamToBrowser)
                $objFilesystem->closeFilePointer();

            if($bitStreamToBrowser) {
                flush();
                die();
            }
	        return true;
	    }
	    else {
	        throw new class_exception("can't proceed, needed values missing", class_exception::$level_ERROR);
	    }
	    return false;
	}


    

	/**
	 * Set the type of delimiter of the source or target file
	 * Use the static class-vars to use valid delimiters
	 *
	 * @param string $strDelimiter
	 */
	public function setStrDelimiter($strDelimiter) {
	    $this->strDelimiter = $strDelimiter;
	}


	/**
	 * Set an array of rows to write to a csv-file
	 *
	 * @param mixed $arrData
	 */
	public function setArrData($arrData) {
	    if(count($arrData) > 0) {
	        $this->arrData = $arrData;
	    }
	}

	/**
	 * Returns the current arrData
	 *
	 * @return array
	 */
	public function getArrData() {
	    return $this->arrData;
	}

	/**
	 * Set the filename of the source or target file
	 *
	 * @param string $strFilename
	 */
	public function setStrFilename($strFilename) {
	    //replace realpath?
	    if(uniStrpos($strFilename, _realpath_) !== false) {
	        $strFilename = uniStrReplace(_realpath_, "", $strFilename);
	    }
	    $this->strFilename = $strFilename;
	}


	/**
	 * Set an array of column-mappings. Usefull if you want different column names or
	 * to limit the columns to read / write.
	 * The array is build like
	 * array( "sourceCol1" => "targetCol1",
	 *        "sourceCol2" => "targetCol2");
	 *
	 * @param array $arrMapping
	 */
	public function setArrMapping($arrMapping) {
	    if(count($arrMapping) > 0) {
	        $this->arrMapping = $arrMapping;
	    }
	}

	/**
	 * Sets an encloser to sourround the values.
	 * Example: " --> "value1","value2"
	 *
	 * @param string $strEncloser
	 */
	public function setTextEncloser($strEncloser) {
	    if($strEncloser == "")
	       $strEncloser = null;

	    $this->strTextEncloser = $strEncloser;
	}

    /**
     * Sets the nr of rows from top to be skipped during import.
     * Use this setting if there are additional headers in the import-file
     * 
     * @param int $intImportRowOffset 
     */
    public function setIntImportRowOffset($intImportRowOffset) {
        $this->intImportRowOffset = $intImportRowOffset;
    }


}

?>