<?php
/*"******************************************************************************************************
*   (c) 2012 Kajona, mr.bashshell                                                                           *
*-------------------------------------------------------------------------------------------------------*
*   $Id$                                           *
********************************************************************************************************/

echo "<pre>\n";
echo "+-------------------------------------------------------------------------------+\n";
echo "|                                                                               |\n";
echo "|   Selenium Testsuite Generator                                                |\n";
echo "|                                                                               |\n";
echo "+-------------------------------------------------------------------------------+\n";
           
@ini_set("max_execution_time", "2000");

class class_seleniumsuite {
    
    function __construct() {
        class_carrier::getInstance();
        //NO $this->strCommonTestsFolder    = "/core/_testing/commontests";
        $this->strProjectFolder  = "/project";
        $this->strSeleniumFolder = $this->strProjectFolder."/seleniumtesting";
        $this->strSelTempFolder  = $this->strSeleniumFolder."/temp";
    }

    public function getSystemParameter() {

        $strHeaderName = class_config::readPlainConfigsFromFilesystem("https_header");
        $strHeaderValue = strtolower(class_config::readPlainConfigsFromFilesystem("https_header_value"));

        $arrSystemParameter = array();
        $arrSystemParameter["SCHEME"] = isset($_SERVER[$strHeaderName]) && (strtolower($_SERVER[$strHeaderName]) == $strHeaderValue) ? "https" : "http";
        $arrSystemParameter["HOSTNAME"] = $_SERVER['SERVER_NAME'];
        $strRequestUri = $_SERVER['REQUEST_URI'];
        $path_parts = pathinfo($strRequestUri);
        $arrSystemParameter["URLPATHNAME"] = $path_parts['dirname'];

        return $arrSystemParameter;   
    }
    
    public function checkExistingDir($strDirName) {        
        if(is_dir(_realpath_.$strDirName))
            echo "\n Ok, found folder ".$strDirName;
        else
            DIE("\n\n ERROR: The folder  '".$strDirName."' does not exist!!");
    }
        
    public function checkWriteableDir($objFilesystem, $strDirName) {        
        if($objFilesystem->isWritable($strDirName))
            echo "\n Ok, ".$strDirName." is writeable.";
        else
            DIE("\n\n ERROR: ".$strDirName." is NOT writeable!!\n Please change permissions to let the webserver write in it.");
    }
    
    public function deleteFolder($objFilesystem, $strDirName) {
        if(is_dir(_realpath_.$strDirName)) {
            echo "\n Found existing folder ".$strDirName.", delete it...";

            $boolDeleteAction = @$objFilesystem->folderDeleteRecursive($strDirName);
            if($boolDeleteAction === false)
                DIE("\n\n ERROR: Folder ".$strDirName." can not be deleted! Permission denied!");

        }
        else 
            echo "\n Ok, ".$strDirName." does not already exist.";
    }

} // class_seleniumsuite end


class class_copydown extends class_seleniumsuite {
    public function generator() {
        if(issetPost("doGenerate") && issetPost("SCHEME") && issetPost("HOSTNAME") && issetPost("URLPATHNAME")   ) {            
            //NO $this->checkExistingDir($this->strCommonTestsFolder);            
            $this->checkExistingDir($this->strProjectFolder);            
            $objFilesystem = new class_filesystem();
            $this->checkWriteableDir($objFilesystem, $this->strProjectFolder);             
            $this->deleteFolder($objFilesystem, $this->strSeleniumFolder);
            
            echo "\n\n### Creating testsuite... ###\n";
            echo "\n Creating folder ".$this->strSeleniumFolder;
            $objFilesystem->folderCreate($this->strSeleniumFolder);
            
            /*
            echo "\n Searching for available common Selenium tests...\n";
            $arrFilesCommon = $objFilesystem->getFilelist($this->strCommonTestsFolder, array(".html", ".htm"));
            echo "\n Found ".count($arrFilesCommon)." common test(s)\n\n";
            */
            
            echo "\n Searching for available Selenium tests...\n";
            $arrFiles = class_resourceloader::getInstance()->getFolderContent("/tests", array(".html", ".htm"));
            echo "\n Found ".count($arrFiles)." Selenium test(s)\n\n";          
            
            if(count($arrFiles) == 0)
                echo "\n\n :-(   No Files found.";
            else {
                $strContentTestsuiteFile ="                
<?xml version=\"1.0\" encoding=\"UTF-8\"?>
<!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML 1.0 Strict//EN\" \"http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd\">
<html xmlns=\"http://www.w3.org/1999/xhtml\" xml:lang=\"en\" lang=\"en\">
<head>
<meta content=\"text/html; charset=UTF-8\" http-equiv=\"content-type\" />
<title>Test Suite</title>
</head>
<body>
<table id=\"suiteTable\" cellpadding=\"1\" cellspacing=\"1\" border=\"1\" class=\"selenium\"><tbody>
<tr><td><b>Test Suite</b></td></tr>";           

                $arrSearches = array();
                $arrSearches[] = "XxxSCHEMExxX";
                $arrSearches[] = "XxxHOSTNAMExxX";
                $arrSearches[] = "XxxPATHNAMExxX";
                $arrReplacements = array();
                $arrReplacements[] = $_POST["SCHEME"];
                $arrReplacements[] = $_POST["HOSTNAME"];
                $arrReplacements[] = $_POST["URLPATHNAME"];                
                
                foreach ($arrFiles as $strPathToFile=>$strOneFile) {
                    echo "\n  Processing file: ".$strPathToFile;
                    //$strContentCurrentFile = file_get_contents(_realpath_.$this->strCommonTestsFolder."/".$strOneFile);
                    $strContentCurrentFile = file_get_contents(_realpath_."/".$strPathToFile);

                    $strNewFileContent = uniStrReplace($arrSearches, $arrReplacements, $strContentCurrentFile);
                    $strFileName = _realpath_.$this->strSeleniumFolder."/proj_".$strOneFile;
                    file_put_contents($strFileName, $strNewFileContent);
                    chmod($strFileName, 0777);
                    $strContentTestsuiteFile .= "\n  <tr><td><a href=\"proj_".$strOneFile."\">".$strOneFile."</a></td></tr>";
                }
                $strContentTestsuiteFile .= "\n
</tbody></table>
</body>
</html>";
                echo "\n\n  Write master file for testsuite";
                file_put_contents(_realpath_.$this->strSeleniumFolder."/_Testsuite.htm", $strContentTestsuiteFile);
                echo "\n\n\n<b>You will find your new files in "._realpath_.$this->strSeleniumFolder."</b>";
            }
        }
    }    
    
    
    public function selectorform () {                
        echo "\n\nThis will generate the files for your Selenium Testingsuite (<b>'COPY DOWN'</b>)";
        echo "<form method=\"post\">";
        echo "\nThe following parameter will be used. Please change if necessary. E.g. you can change the hostname to test on another machine.\n";
        $arrSystemParameter = $this->getSystemParameter();
        foreach($arrSystemParameter as $key => $strOneParameter) 
            echo "\n ".$key.": <input size=\"45\" type=\"text\" name=\"".$key."\" value=\"".$strOneParameter."\" /> \n\n";
        echo "\nSure? Continue?";
        echo "\n\n\n";
        echo "<input type=\"hidden\" name=\"doStart\" value=\"1\" />";
        echo "<input type=\"hidden\" name=\"copydirection\" value=\"down\" />";
        echo "<input type=\"hidden\" name=\"doGenerate\" value=\"1\" />";
        echo "<input type=\"submit\" value=\"Cool! Create testsuite now!\" />";
        echo "</form>";
    }    

} // class_copydown zu


class class_copyup extends class_seleniumsuite {
    public function generator() {   
        if(issetPost("doGenerate") && issetPost("SCHEME") && issetPost("HOSTNAME") && issetPost("URLPATHNAME")   ) {                   
            $objFilesystem = new class_filesystem();
            $this->checkExistingDir($this->strSeleniumFolder);            
            $this->checkWriteableDir($objFilesystem, $this->strSeleniumFolder);            
            $this->deleteFolder($objFilesystem, $this->strSelTempFolder);
            echo "\n Creating folder ".$this->strSelTempFolder;
            $objFilesystem->folderCreate($this->strSelTempFolder);
            
            echo "\n\n### Processing Selenium files... ###\n";            
            echo "\n Searching for available Selenium tests...\n";
            $arrFiles = $objFilesystem->getFilelist($this->strSeleniumFolder, array(".html"));
            echo "\n Found ".count($arrFiles)." files(s) in '".$this->strSeleniumFolder."' \n\n";
            if(count($arrFiles) == 0)
                echo "\n\n :-(   No Files found.";
            else {
                foreach ($arrFiles as $strOneFile) {
                    if(substr($strOneFile, 0,5) == "proj_") {
                        echo "\n  Processing file: ".$strOneFile;
                        $strContentCurrentFile = file_get_contents(_realpath_.$this->strSeleniumFolder."/".$strOneFile);
                        $arrReplacements = array();
                        $arrReplacements[] = "XxxSCHEMExxX";
                        $arrReplacements[] = "XxxHOSTNAMExxX";
                        $arrReplacements[] = "XxxPATHNAMExxX";
                        $arrSearches = array();
                        $arrSearches[] = $_POST["SCHEME"];
                        $arrSearches[] = $_POST["HOSTNAME"];
                        $arrSearches[] = $_POST["URLPATHNAME"];

                        $strNewFileContent = uniStrReplace($arrSearches, $arrReplacements, $strContentCurrentFile);
                        $strNewFileName = _realpath_.$this->strSelTempFolder."/".substr($strOneFile, 5); // proj_ = 5
                        file_put_contents($strNewFileName, $strNewFileContent);
                        chmod($strNewFileName, 0777);
                    }
                    else 
                        echo "\n  <b>WARNING: Found file ".$strOneFile.", but will not use it! Files need the prefix 'proj_' to be used!</b>";
                }               
            }         
        }
        echo "\n\n\n\nYou will find your new files in "._realpath_.$this->strSelTempFolder."\n<b>Please remeber to distribute these files to the appropiate module_xxxx folder under core!!!</b>";
    }
    
    
    public function selectorform() {
        echo "\n\nThis will copy your Selenium files to temp and replace the parameter of your environment (<b>'COPY UP'</b>)";
        echo "<form method=\"post\">";
        echo "\nThe following parameter will be replaced by placeholders. Please change if you used differend values in your testsuite.\n";
        $arrSystemParameter = $this->getSystemParameter();
        foreach($arrSystemParameter as $key => $strOneParameter) 
            echo "\n ".$key.": <input size=\"45\" type=\"text\" name=\"".$key."\" value=\"".$strOneParameter."\" /> \n\n";
        echo "\nSure? Continue?";
        echo "\n\n\n";
        echo "<input type=\"hidden\" name=\"doStart\" value=\"1\" />";
        echo "<input type=\"hidden\" name=\"copydirection\" value=\"up\" />";
        echo "<input type=\"hidden\" name=\"doGenerate\" value=\"1\" />";
        echo "<input type=\"submit\" value=\"Cool! Transfer my changes now!\" />";
        echo "</form>";        
    }
} // class_copyup zu 

// ####################################################################################### //

if(issetPost("doStart")) {
    if(getPost("copydirection")== "up")
        $objSeleniumGenerator = new class_copyup();
    
    else if(getPost("copydirection")== "down") 
        $objSeleniumGenerator = new class_copydown();        
    
    else
        DIE("ERROR: No direction choosen!");
    
    
    if(getPost("doGenerate") == "") {            
        $objSeleniumGenerator->selectorform();
    }
    else {
        $objSeleniumGenerator->generator(); 
    }
} 

else {
    $objSeleniumGenerator = new class_seleniumsuite;
    echo "\n What do you want to do?";

    echo "<form method=\"post\">";
    echo "<input type=\"hidden\" name=\"doStart\" value=\"1\" />";
    echo "\n<input type=\"radio\" name=\"copydirection\" value=\"down\" checked /> <b>GENERATE testingsuite ('copy down')</b> (will copy files TO ".$objSeleniumGenerator->strSeleniumFolder.")\n      -> Use this to get a set of files for your Selenium testing.\n      -> You can use this set to test your project with Selenium IDE.\n      -> The new files will get the prefix 'proj_'.";
    echo "\n<input type=\"radio\" name=\"copydirection\" value=\"up\" /> <b>CLEANUP projectfiles</b> (will copy files FROM ".$objSeleniumGenerator->strSeleniumFolder." to a temp folder)\n      -> Use this to copy your changed files from the testingsuite to a temporary folder and let the parameter be anonymized.\n      -> You have to distribute your new to the module_xxxxx/tests folder later manually.\n      -> Files with prefix 'proj_' will be used and the prefix will be removed.";
    echo "\n\n<b> WARNING!! All existing files in destination folders will be deleted/overwritten!!!</b>\n";
    echo "\n\n<input type=\"submit\" value=\"Start\" />";
    echo "</form>";

}


echo "\n\n\n <a href=\"".$_SERVER['PHP_SELF']."?".$_SERVER['QUERY_STRING']."\">Testing startpage</a>";
echo "\n\n";
echo "+-------------------------------------------------------------------------------+\n";
echo "| (c) www.kajona.de                                                           |\n";
echo "+-------------------------------------------------------------------------------+\n";
echo "</pre>";
