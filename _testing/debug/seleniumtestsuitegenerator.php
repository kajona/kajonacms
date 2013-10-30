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
        $this->strProjectFolder  = "/project";
        $this->strSeleniumFolder = $this->strProjectFolder."/seleniumtesting";
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
        if($arrSystemParameter["URLPATHNAME"][0] == "/")
            $arrSystemParameter["URLPATHNAME"] = uniSubstr ($arrSystemParameter["URLPATHNAME"], 1);

        return $arrSystemParameter;   
    }
    
    public function checkExistingDir($strDirName) {        
        if(is_dir(_realpath_.$strDirName))
            echo "\n Ok, found folder ".$strDirName;
        else
            DIE("\n\n ERROR: The folder  '".$strDirName."' does not exist!!");
    }
        
    public function checkWriteableDir(class_filesystem $objFilesystem, $strDirName) {
        if($objFilesystem->isWritable($strDirName))
            echo "\n Ok, ".$strDirName." is writeable.";
        else
            DIE("\n\n ERROR: ".$strDirName." is NOT writeable!!\n Please change permissions to let the webserver write in it.");
    }
    
    public function deleteFolder(class_filesystem $objFilesystem, $strDirName) {
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


class class_createFiles extends class_seleniumsuite {
    public function generator() {
        if(issetPost("doGenerate") && issetPost("SCHEME") && issetPost("HOSTNAME") && issetPost("URLPATHNAME")   ) {            
            $this->checkExistingDir($this->strProjectFolder);            
            $objFilesystem = new class_filesystem();
            $this->checkWriteableDir($objFilesystem, $this->strProjectFolder);             
            $this->deleteFolder($objFilesystem, $this->strSeleniumFolder);
            
            echo "\n\n### Creating testing suite... ###\n";
            echo "\n Creating folder ".$this->strSeleniumFolder;
            $objFilesystem->folderCreate($this->strSeleniumFolder);
                        
            echo "\n Searching for available Selenium tests...\n";
            $arrFiles = class_resourceloader::getInstance()->getFolderContent("/tests", array(".html", ".htm"));
            echo "\n Found ".count($arrFiles)." Selenium test(s)\n\n";          
            
            if(count($arrFiles) == 0)
                echo "\n\n :-(   No Files found.";
            else {
                
                $this->createEnvFile($_POST["SCHEME"],$_POST["HOSTNAME"],$_POST["URLPATHNAME"],$_POST["ADMINNAME"],$_POST["ADMINPASSWORD"],$_POST["USERNAME"],$_POST["USERPASSWORD"],$_POST["DBHOST"], $_POST["DBNAME"] ,$_POST["DBUSER"] ,$_POST["DBPASSWORD"] ,$_POST["DBPREFIX"]);
                
                $strContentTestsuiteFile = <<<HTML
<?xml version="1.0" encoding="UTF-8"?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
<meta content="text/html; charset=UTF-8" http-equiv="content-type" />
<title>Test Suite</title>
</head>
<body>
<table id="suiteTable" cellpadding="1" cellspacing="1" border="1" class="selenium"><tbody>
<tr><td><b>Test Suite</b></td></tr>
<tr><td><a href="setEnv.htm">setEnv</a></td></tr>
HTML;
                
                
                if($_POST["databasehelper"] == "mysql") {
                    echo "\n\n  Adding database helper for MYSQL.\n";
                    $strContentTestsuiteFile .= "\n  <tr><td><a href=\"../../core/module_installer/tests/selhelper_kaj4x_allInOneInstallerMYSQL.html\">selhelper_MySQL-Installer.html</a></td></tr>";                    
                }
                elseif ($_POST["databasehelper"] == "sqlite") {
                    echo "\n\n  Adding database helper for SQLITE.\n";
                     $strContentTestsuiteFile .= "\n  <tr><td><a href=\"../../core/module_installer/tests/selhelper_kaj4x_allInOneInstallerSQLITE.html\">selhelper_SQLITE-Installer.html</a></td></tr>"; 
                }
                
                
                foreach ($arrFiles as $strPathToFile=>$strOneFile) {
                    if(substr($strOneFile, 0,33) == "selhelper_kaj4x_allInOneInstaller")
                        continue;
                    echo "\n  Adding test file: ".$strPathToFile;
                    $strContentTestsuiteFile .= "\n  <tr><td><a href=\"../..".$strPathToFile."\">".$strOneFile."</a></td></tr>";                    
                }
                $strContentTestsuiteFile .= <<<HTML
</tbody></table>
</body>
</html>
HTML;
                echo "\n\n  Writing master file for testsuite (Testsuite_YOURHOSTNAME.htm: this contains a link to all the tests)";
                file_put_contents(_realpath_.$this->strSeleniumFolder."/Testsuite_".$_POST["HOSTNAME"].".htm", $strContentTestsuiteFile);
                echo "\n\n\n<b>You will find your new files in "._realpath_.$this->strSeleniumFolder."</b>\nOpen the file 'Testsuite_".$_POST["HOSTNAME"].".htm' in your Selenium IDE. All available test cases will be included.";
            }
        }
    }    
    
   
    private function createEnvFile($strScheme,$strHostname,$strPathname,$strAdminname,$strAdminpassword,$strUsername,$strUserpassword,$strDbhost,$strDbname,$strDbuser,$Dbpassword,$strDbprefix) {
       
        $strContentEnvFile = <<<HTML
<?xml version="1.0" encoding="UTF-8"?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head profile="http://selenium-ide.openqa.org/profiles/test-case">
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<link rel="selenium.base" href="irrelevant" />
<title>Set Testing Environment</title>
</head>
<body>
<table cellpadding="1" cellspacing="1" border="1">
<thead>
<tr><td rowspan="1" colspan="3">setEnv</td></tr>
</thead><tbody>
<tr>
	<td>store</td>
	<td>
HTML;
        $strContentEnvFile .= $strScheme."://".$strHostname;
        $strContentEnvFile .= <<<HTML
</td>
	<td>sysAddress</td>
</tr>
<tr>
	<td>store</td>
	<td>
HTML;
        $strContentEnvFile .= $strPathname;
        $strContentEnvFile .= <<<HTML
</td>
	<td>sysPathname</td>
</tr>
<tr>
	<td>store</td>
	<td>
HTML;
        $strContentEnvFile .= $strAdminname;
        $strContentEnvFile .= <<<HTML
</td>
	<td>user1</td>
</tr>
<tr>
	<td>store</td>
	<td>
HTML;
        $strContentEnvFile .= $strAdminpassword;
        $strContentEnvFile .= <<<HTML
</td>
	<td>passwd1</td>
</tr>
<tr>
	<td>store</td>
	<td>
HTML;
        $strContentEnvFile .= $strUsername;
        $strContentEnvFile .= <<<HTML
</td>
	<td>user2</td>
</tr>
<tr>
	<td>store</td>
	<td>
HTML;
        $strContentEnvFile .= $strUserpassword;
        $strContentEnvFile .= <<<HTML
</td>
	<td>passwd2</td>
</tr>           
<tr>
	<td>store</td>
	<td>
HTML;
        $strContentEnvFile .= $strDbhost;
        $strContentEnvFile .= <<<HTML
</td>
	<td>databasehost</td>
</tr> 
<tr>
	<td>store</td>
	<td>
HTML;
        $strContentEnvFile .= $strDbname;
        $strContentEnvFile .= <<<HTML
</td>
	<td>databasename</td>
</tr> 
<tr>
	<td>store</td>
	<td>
HTML;
        $strContentEnvFile .= $strDbuser;
        $strContentEnvFile .= <<<HTML
</td>
	<td>databaseuser</td>
</tr> 
<tr>
	<td>store</td>
	<td>
HTML;
        $strContentEnvFile .= $Dbpassword;
        $strContentEnvFile .= <<<HTML
</td>
	<td>databasepassword</td>
</tr> 
<tr>
	<td>store</td>
	<td>
HTML;
        $strContentEnvFile .= $strDbprefix;
        $strContentEnvFile .= <<<HTML
</td>
	<td>databaseprefix</td>
</tr> 
<tr>
	<td>open</td>
	<td>\${sysAddress}/\${sysPathname}</td>
	<td></td>
</tr>
</tbody></table>
</body>
</html>
HTML;
        echo "\n\n  Writing environment file for your system (setEnv.htm)";
        file_put_contents(_realpath_.$this->strSeleniumFolder."/setEnv.htm", $strContentEnvFile);
    }

    
    public function selectorform () {                
        echo "\n\n<b>Provide the settings for your Selenium Testingsuite</b>";
        echo "<form method=\"post\">";
        
        echo "\n\n<b>System Parameter</b>\nThe following parameter will be used for your environment. Please change if necessary, e.g. the hostname to test on another machine.\n";
        echo "<table>";
        $arrSystemParameter = $this->getSystemParameter();
        foreach($arrSystemParameter as $key => $strOneParameter) 
            echo $this->drawParameterTable($key, $strOneParameter);
        echo "</table>";
        
        
        echo "\n\n<b>Testing User</b>\nThese user will be created within the testing:";
        echo "<table>";
        echo $this->drawParameterTable("ADMINNAME", "admin");
        echo $this->drawParameterTable("ADMINPASSWORD", "kajona");
        echo $this->drawParameterTable("USERNAME", "scott");
        echo $this->drawParameterTable("USERPASSWORD", "tiger");
        echo "</table>";

        echo "\n\n-------------------------------------------------------------------------";
        echo "\n\n<b>Database Installer</b>\nYou can <u>optional</u> add a 'helper test case' to set up your database. Please choose MySQL or SQLite.";

        echo "\n<input type=\"radio\" name=\"databasehelper\" value=\"no\" checked /> No database installer";        
        echo "\n<input type=\"radio\" name=\"databasehelper\" value=\"mysql\" /> MySQL installer";        
        echo "\n<input type=\"radio\" name=\"databasehelper\" value=\"sqlite\" /> SQLite installer";        
        
        echo "\n\n<b>Database Parameter</b>\nThis parameter will be used within the testings (the user will be created):";
        echo "<table>";
        echo $this->drawParameterTable("DBHOST", "localhost");
        echo $this->drawParameterTable("DBNAME", "kajona4");
        echo $this->drawParameterTable("DBUSER", "kajona4");
        echo $this->drawParameterTable("DBPASSWORD", "kajona4");
        echo $this->drawParameterTable("DBPREFIX", "selenium_");
        echo "</table>";
        
        
        echo "\n\n\n";
        echo "<input type=\"hidden\" name=\"doStart\" value=\"1\" />";
        echo "<input type=\"hidden\" name=\"copydirection\" value=\"down\" />";
        echo "<input type=\"hidden\" name=\"doGenerate\" value=\"1\" />";
        echo "<input type=\"submit\" value=\"Cool! Create testing suite now!\" />";
        echo "</form>";
    }   
    
    private function drawParameterTable($strParameterName,$strParameterValue) {
        $strTableRow = "<tr><td>".$strParameterName.":</td><td><input size=\"45\" type=\"text\" name=\"".$strParameterName."\" value=\"".$strParameterValue."\" /></td></tr>";
        return $strTableRow;
    }

} // class_copydown zu


// ####################################################################################### //

if(issetPost("doStart")) {
    $objSeleniumGenerator = new class_createFiles();        
    if(getPost("doGenerate") == "")
        $objSeleniumGenerator->selectorform();
    else 
        $objSeleniumGenerator->generator(); 
} 

else {
    $objSeleniumGenerator = new class_seleniumsuite;
    echo "<form method=\"post\">";
    echo "<input type=\"hidden\" name=\"doStart\" value=\"1\" />";
    echo "\n\n<b>Generate testing suite</b> \n This will create a testing suite in ".$objSeleniumGenerator->strSeleniumFolder."\n Use this to get a set of files for your Selenium testing.\n You can use this to test your project with Selenium IDE.\n";
    echo "\n\n<b> WARNING!! All existing files in destination folders will be deleted/overwritten!!!</b>\n";    
    echo "\n\n<input type=\"submit\" value=\"Start\" />";
    echo "</form>";
}


echo "\n\n\n <a href=\"".$_SERVER['PHP_SELF']."?".$_SERVER['QUERY_STRING']."\">Testing startpage</a>";
echo "\n\n";
echo "+-------------------------------------------------------------------------------+\n";
echo "| (c) www.kajona.de                                                             |\n";
echo "+-------------------------------------------------------------------------------+\n";
echo "</pre>";
