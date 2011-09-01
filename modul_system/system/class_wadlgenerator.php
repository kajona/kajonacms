<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2011 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id: class_lockmanager.php 4088 2011-08-26 13:14:19Z sidler $                                       *
********************************************************************************************************/

/**
 * This class may be used in order to generate a wadl-file for a single module.
 * A wadl may be used by external classes in order to build rest-clients matching the kajona-api
 *
 * @package modul_system
 * @author sidler@mulchprod.de
 * @since 3.4.1
 */
class class_wadlgenerator  {

    private $bitRewrite = false;
    
    private $strModule = "";
    private $strArea = "";
    
    private $strMethods = "";
    
    private $arrGrammars = array();

	public function __construct($strArea, $strModule) {
        $this->bitRewrite = _system_mod_rewrite_ == "true";
        
        $this->strArea = $strArea;
        $this->strModule = $strModule;
	}
    
    /**
     * Adds an entry to the list of linked grammar-section
     * 
     * @param string $strGrammar 
     */
    public function addIncludeGrammars($strGrammar) {
        $this->arrGrammars[] = $strGrammar;
    }
    
    
    /**
     * Adds a single method to the document.
     * Depending on the params passed, it may be added with a set of params or a link to an
     * external representation
     * 
     * @param type $bitGet true = GET, false = POST
     * @param type $strName name of the operation, so the action-command
     * @param type $arrParams array( [strName, strType, bitRequired, $strFixed] )
     * @param type $arrRepresentations array( [strMediaType, strElement] )
     */
    public function addMethod($bitGet, $strName, $arrParams = array(), $arrRepresentations = array()) {
        
        if(!$this->bitRewrite) {
            
            if($this->strArea == "admin")
                $arrParams[] = array("admin", "xsd:string", true, "1");
            
            $arrParams[] = array("module", "xsd:string", true, $this->strModule);
            
        }
        
        $this->strMethods .= " <resource path=\"".$strName."\">\n";
        $this->strMethods .= "  <method name=\"".($bitGet ? "GET" : "POST")."\">\n";
        
        $this->strMethods .= "   <request>\n";
        
        foreach($arrRepresentations as $arrSingleRepresentation) {
            $this->strMethods .= "    <representation \n";
            $this->strMethods .= "      mediaType=\"".$arrSingleRepresentation[0]."\"\n";
            $this->strMethods .= "      element=\"".$arrSingleRepresentation[1]."\"\n";
            $this->strMethods .= "    />";
        }
        
        
        foreach($arrParams as $arrSingleParam) {
            $this->strMethods .= "    <param \n";
            $this->strMethods .= "      name=\"".$arrSingleParam[0]."\"\n";
            $this->strMethods .= "      type=\"".$arrSingleParam[1]."\"\n";
            $this->strMethods .= "      style=\"query\"\n";
            $this->strMethods .= "      required=\"".($arrSingleParam[2] ? "true" : "false")."\"\n";
            
            if(isset($arrSingleParam[3]))
                $this->strMethods .= "      fixed=\"".$arrSingleParam[3]."\"\n";
            
            $this->strMethods .= "    />";
        }
        $this->strMethods .= "   </request>\n";
        $this->strMethods .= "   <response>\n";
        $this->strMethods .= "    <representation mediaType=\"application/xml\" />\n";
        $this->strMethods .= "   </response>\n";
        $this->strMethods .= "  </method>\n";
        $this->strMethods .= " </resource>\n";
    }
    
    /**
     * Generates the final wadl document and returns the complete
     * docment
     * @return string 
     */
    public function getDocument() {
        return $this->getDocumentWrapper();
    }
    
    /**
     * Internal helper, generates the wrapper around the methods and other document sections
     * @return string 
     */
    private function getDocumentWrapper() {
        $strReturn = "<application xmlns:xsi=\"http://www.w3.org/2001/XMLSchema-instance\" 
            xmlns:xsd=\"http://www.w3.org/2001/XMLSchema\" 
            xsi:schemaLocation=\"http://research.sun.com/wadl/2006/10 wadl.xsd\" 
            xmlns=\"http://research.sun.com/wadl/2006/10\">\n";
        
        
        //_system_mod_rewrite_ == "true"
        
        $strRessource = "";
        if($this->bitRewrite) {
            $strRessource .= _webpath_."/xml";
            if($this->strArea == "admin")
                $strRessource .= "/admin/";
            
            $strRessource .= $this->strModule."/";
        }
        else {
            $strRessource .= _webpath_."/xml.php";
        }
        
        //add grammars, if given
        if(count($this->arrGrammars) > 0) {
            $strReturn .= " <grammars>\n";
            foreach($this->arrGrammars as $strOneGrammar)
                $strReturn .= "   <include href=\"".$strOneGrammar."\" />";
            $strReturn .= " </grammars>\n";
        }
        
        $strReturn .= "<resources base=\"".$strRessource."\"> \n";
        $strReturn .= $this->strMethods;
        $strReturn .= "</resources>  \n";
        $strReturn .= "<!-- generated by Kajona WADL generator, kernel version ".class_modul_system_module::getModuleByName("system")->getStrVersion()." -->\n";
        $strReturn .= "</application>  \n";
        
        return $strReturn;
    }

    

} 

?>