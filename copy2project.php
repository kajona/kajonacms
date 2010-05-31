<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2009 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*   $Id$                                            *
********************************************************************************************************/




class class_copy2project {

    private $strBasePath = "";
    private $strLogName = "copy2project.log";
    private $strSystemFolderName = "kajona";

    private $arrFileExclusionsP2M = array("config.php",
                                          ".htaccess",
                                          "global_includes.php",
                                          "systemlog.log",
                                          "dblog.log" );

    private $arrFolderExclusionsM2P = array(".svn");

    private $arrLogContent = array();

    private $strCopyLog = "";
    private $strCopyWarnings = "";



    public function  __construct() {
        $this->strBasePath = dirname(__FILE__);

        
    }


    public function doWork() {
        if( !isset($_GET["action"] ) ) {

            echo "Note: The order of folders and files is alphabetical. \n";
            echo "      If a module overwrites a file already copied by a previous folder, \n";
            echo "      only the last one will be copied back.\n\n";
            echo "init params: \n";
            echo "  base folder:          ".$this->strBasePath."\n";
            echo "  system folder:        ".$this->strBasePath."/".$this->strSystemFolderName."\n";
            echo "  excluded files P2M:   ".implode(", ", $this->arrFileExclusionsP2M)."\n";
            echo "  excluded folders M2P: ".implode(", ", $this->arrFolderExclusionsM2P)."\n";
            echo "  logfile:              ".$this->strLogName."\n";

            echo "<h3>Copy modules 2 project (Down to ./".$this->strSystemFolderName.")</h3>";
            echo "<a href=\"copy2project.php?action=modules2project\">Copies all files to the subfolder \n".$this->strBasePath." --> ".$this->strBasePath."/".$this->strSystemFolderName."</a>\n";
            echo "\n<h3>Copy project 2 modules (Up from ./".$this->strSystemFolderName.")</h3>";
            echo "<a href=\"copy2project.php?action=project2modules\">Copies all files from the subfolder into the module-structure \n".$this->strBasePath."/".$this->strSystemFolderName." --> ".$this->strBasePath."</a>\n";
            echo "\n<h3>Check consistency of system-folder (".$this->strSystemFolderName.")</h3>";
            echo "<a href=\"copy2project.php?action=checkProject\">Compares the logfile with the project folder.\nLists all files not exising anymore or existing but not being listed in the logfile.</a>";
        }
        else {
            if($_GET["action"] == "modules2project") {
                echo "<h3>modules 2 project</h3>";
                $this->modules2project();

                if($this->strCopyWarnings != "")
                    echo "<b>\nWarnings: \n".$this->strCopyWarnings."</b>";

                echo $this->strCopyLog;

            }
            else if($_GET["action"] == "project2modules") {
                echo "<h3>project 2 modules</h3>";
                $this->project2modules();

                if($this->strCopyWarnings != "")
                    echo "<b>\nWarnings: \n".$this->strCopyWarnings."</b>\n";

                echo $this->strCopyLog;
            }
            else if($_GET["action"] == "checkProject") {
                echo "<h3>check project consistency</h3>";
                $this->checkProject();
            }
        }
    }


    private function addFilesToLogfile() {
        echo "Adding submitted files to logfile. \n<b>You still have to execute a copy-run afterwards.</b>\n";
        echo "Adding files to module ".$_POST["targetModule"]."...\n\n";


        if(!isset($_POST["files"]) || count($_POST["files"]) == 0) {
            echo "no files to add.\n\n";
            return;
        }

        $strLogContent = file_get_contents($this->strBasePath."/".$this->strLogName); 

        foreach($_POST["files"] as $strOneFile => $strValue) {
            $strOneFile = str_replace($this->strBasePath."/".$this->strSystemFolderName, "", $strOneFile);

            $strSource = $this->strBasePath."/".$_POST["targetModule"]."".$strOneFile;
            $strTarget = $this->strBasePath."/".$this->strSystemFolderName."".$strOneFile;

            echo $strOneFile."\n";
            echo "\t".$strTarget." to ".$strSource;

            $strLogContent .= $strTarget."<to>".$strSource."<eol>\n";
        }

        file_put_contents($this->strBasePath."/".$this->strLogName, $strLogContent);

        echo "\n\n";
    }


    private function checkProject() {

        if(isset($_POST["submit"])) {
            $this->addFilesToLogfile();
        }

        echo "Loading logfile ".$this->strBasePath."/".$this->strLogName."\n\n";
        $strLogContent = trim(file_get_contents($this->strBasePath."/".$this->strLogName));

        $arrFiles = explode("<eol>", $strLogContent);
        
        $arrCleanedUpArray = array();
        
        echo "Searching for files being deleted...\n\n";
        foreach($arrFiles as $strOneFileEntry) {
            $arrSingleFile = explode("<to>", trim($strOneFileEntry));
            if(isset($arrSingleFile[0]) && isset($arrSingleFile[1])) {
                
                $arrCleanedUpArray[] = $arrSingleFile[0];
                
                if(!is_file($arrSingleFile[0])) {
                    echo "> Sourcefile: ".$arrSingleFile[0]." not existing anymore\n";
                    echo "      Origin: ".$arrSingleFile[1]."\n";     
                }
            }
        }
        
        echo "\n...finished.\n\n";
        
        echo "Searching for files being added in the project (and not being copied back to modules)...\n\n";
        echo "Add selected files to module:\n";
        echo "<form method=\"POST\" >\n";

        $arrFolderContent = $this->getFolderContent($this->strBasePath);
        echo "<select name=\"targetModule\" >\n";
        foreach($arrFolderContent["folders"] as $strOneFolder) {
            if($strOneFolder != $this->strSystemFolderName && !in_array($strOneFolder, $this->arrFolderExclusionsM2P))
                echo "<option value=\"".$strOneFolder."\" >".$strOneFolder."</option>\n";
        }
        echo "</select>\n\n";
        
        $this->checkProjectRecursive($this->strBasePath."/".$this->strSystemFolderName, $arrCleanedUpArray);

        echo "\n<input type=\"submit\" name=\"submit\" value=\"Add to logfile\" />\n";
        echo "</form>";
        echo "\n...finished.\n";
                
    }
    
    private function checkProjectRecursive($strStartPath, $arrLogArray) {
    $arrFolderContent = $this->getFolderContent($strStartPath);

        foreach($arrFolderContent["files"] as $strSingleFile) {
            if(!in_array($strStartPath."/".$strSingleFile, $arrLogArray)) {
                $strFileId = "file_".$strStartPath.$strSingleFile;
                echo "<input type=\"checkbox\" id=\"".$strFileId."\" name=\"files[".$strStartPath."/".$strSingleFile."]\" ><label for=\"".$strFileId."\"> ".$strStartPath."/".$strSingleFile."</label>\n";
            }
        }

        foreach ($arrFolderContent["folders"] as $strSingleFolder) {
            $this->checkProjectRecursive($strStartPath."/".$strSingleFolder, $arrLogArray);
        }
    }

    private function project2modules() {
        //load the logfiles
        echo "Loading logfile ".$this->strBasePath."/".$this->strLogName."\n\n";
        $strLogContent = trim(file_get_contents($this->strBasePath."/".$this->strLogName));

        $arrFiles = explode("<eol>", $strLogContent);
        foreach($arrFiles as $strOneFileEntry) {
            $arrSingleFile = explode("<to>", trim($strOneFileEntry));
            if(isset($arrSingleFile[0]) && isset($arrSingleFile[1])) {
                

                if(is_file($arrSingleFile[0])) {
                    //excluded file?
                    $strFilename = basename($arrSingleFile[0]);
                    if(!in_array($strFilename, $this->arrFileExclusionsP2M)) {
                        $this->strCopyLog .= " copy ".$arrSingleFile[0]. " --> ".$arrSingleFile[1]."\n";
                        //check if folder exists
                        $arrFolders = explode("/", dirname($arrSingleFile[1]) );

                        $strAttachedFolders = "/";
                        foreach($arrFolders as $strSingleFolder) {
                            $strAttachedFolders .= $strSingleFolder."/";

                            if(!is_dir($strAttachedFolders)) {
                                mkdir($strAttachedFolders, 0777);
                            }


                        }


                        copy($arrSingleFile[0], $arrSingleFile[1]);
                    }
                    else {
                        $this->strCopyLog .= "   <b>Skipped due to exclusions list:".$arrSingleFile[0]."</b>\n";
                        //$this->strCopyWarnings .= "Skipped due to exclusions list:".$arrSingleFile[0]."\n";
                    }
                }
                else {
                    $this->strCopyWarnings .= "Sourcefile ".$arrSingleFile[0]." not existing anymore\n";
                }
            }
        }
        
    }


    private function modules2project() {
        if(!isset($_POST["submit"])) {
            //show a form and the list of folders available
            echo "<form method=\"POST\" target=\"\">\n";

            $arrFolderContent = $this->getFolderContent($this->strBasePath);

            foreach($arrFolderContent["folders"] as $strOneFolder) {
                if($strOneFolder != $this->strSystemFolderName && !in_array($strOneFolder, $this->arrFolderExclusionsM2P))
                    echo "<input type=\"checkbox\" name=\"module[".$strOneFolder."]\" value=\"".$strOneFolder."\" id=\"".$strOneFolder."\" checked=\"checked\" /><label for=\"".$strOneFolder."\">".$strOneFolder."</label>\n";
            }


            echo "<input type=\"submit\" name=\"submit\" value=\"Copy modules 2 project\" />\n";
            echo "</form>\n";
        }
        else {
            //check which folders to copy
            echo "base set: ".implode(", ", $_POST["module"])."\n";

            //check if target folder exists
            if(is_dir($this->strBasePath."/".$this->strSystemFolderName))
                echo "target folder ".$this->strBasePath."/".$this->strSystemFolderName." existing\n";
            else {
                if(mkdir($this->strBasePath."/".$this->strSystemFolderName)) {
                    echo "target folder ".$this->strBasePath."/".$this->strSystemFolderName." created\n";
                    chmod($this->strBasePath."/".$this->strSystemFolderName, 0777);
                }
                else {
                    echo "<b>failed to create ".$this->strBasePath."/".$this->strSystemFolderName."</b>\n";
                    return;
                }
            }


            foreach($_POST["module"] as $strSingleModule) {
                $this->strCopyLog .= "\n\n<b>".$strSingleModule."</b>\n";
                $this->copyModule2ProjectRecursive($this->strBasePath."/".$strSingleModule, $this->strBasePath."/".$this->strSystemFolderName, "   ");
            }

            $this->updateConfigFile($this->strBasePath."/".$this->strSystemFolderName."/system/config/config.php");

            $this->writeLogFile();

        }
    }
   

    private function copyModule2ProjectRecursive($strStartFolder, $strTargetFolder, $strDepthPrefix) {
        $arrFolderContent = $this->getFolderContent($strStartFolder);

        foreach($arrFolderContent["files"] as $strSingleFile) {
            $this->strCopyLog .= $strDepthPrefix.$strSingleFile." -> ".$strTargetFolder."/".$strSingleFile."\n";
            
            if(isset($this->arrLogContent[$strTargetFolder."/".$strSingleFile])) {
                $this->strCopyLog .= $strDepthPrefix."<b>File overwrites ".$this->arrLogContent[$strTargetFolder."/".$strSingleFile]."</b>\n";
                $this->strCopyWarnings .= "File ".$strStartFolder."/".$strSingleFile." overwrites ".$this->arrLogContent[$strTargetFolder."/".$strSingleFile]."\n";
            }
            $this->arrLogContent[$strTargetFolder."/".$strSingleFile] = $strStartFolder."/".$strSingleFile;

            if(copy($strStartFolder."/".$strSingleFile, $strTargetFolder."/".$strSingleFile)) {
                if(!chmod($strTargetFolder."/".$strSingleFile, 0777))
                    $this->strCopyWarnings .= "chmod() on ".$strTargetFolder."/".$strSingleFile." failed\n";
            }
            else
                $this->strCopyWarnings .= "Failed to copy ".$strStartFolder."/".$strSingleFile." to ".$strTargetFolder."/".$strSingleFile;
        }

        foreach ($arrFolderContent["folders"] as $strSingleFolder) {
            if(!in_array($strSingleFolder, $this->arrFolderExclusionsM2P)) {
                $this->strCopyLog .= $strDepthPrefix.$strSingleFolder." -> ".$strTargetFolder."/".$strSingleFolder."\n";
                //mkdir

                if(!file_exists($strTargetFolder."/".$strSingleFolder)) {
                    if(!mkdir($strTargetFolder."/".$strSingleFolder)) {
                        $this->strCopyWarnings .= "Failed to create folder ".$strTargetFolder."/".$strSingleFolder."\n";
                        continue ;
                    }
                    else {
                        chmod($strTargetFolder."/".$strSingleFolder, 0777);
                    }
                }
                $this->copyModule2ProjectRecursive($strStartFolder."/".$strSingleFolder, $strTargetFolder."/".$strSingleFolder, $strDepthPrefix."    ");
            }
        }
    }



    private function writeLogFile() {
        $strLog = "";
        foreach($this->arrLogContent as $strTarget => $strSource) {
            $strLog .= $strTarget."<to>".$strSource."<eol>\n";
        }

        echo "\n\nwriting logfile to ".$this->strBasePath."/".$this->strLogName."\n";
        file_put_contents($this->strBasePath."/".$this->strLogName, $strLog);
    }

    private function updateConfigFile($strConfigFile) {
        $strContent = file_get_contents($strConfigFile);
	    $strSearch = "/\[\'debuglevel\'\]\s* = 0/";
	    $strReplace = "['debuglevel'] = 1";
	    $strContent = preg_replace($strSearch, $strReplace, $strContent);

	    $strSearch = "/\[\'debuglogging\'\]\s* = 1/";
	    $strReplace = "['debuglogging'] = 2";
	    $strContent = preg_replace($strSearch, $strReplace, $strContent);

	    file_put_contents($strConfigFile, $strContent);
    }



    private function getFolderContent($strBaseFolder) {

        $arrFiles = array();
        $arrFolders = array();

        if(is_dir($strBaseFolder)) {
			$handle = opendir($strBaseFolder);
			if($handle !== false) {
				while(false !== ($strFilename = readdir($handle))) 	{

					if(($strFilename != "." && $strFilename != "..")) {
                        if(is_file($strBaseFolder . "/".$strFilename)) {
                            $arrFiles[] = $strFilename;
						}
                        else if(is_dir($strBaseFolder . "/".$strFilename)) {
                            $arrFolders[] = $strFilename;
                        }
					}
				}
				closedir($handle);
			}
		}
		else
			return false;

		//sorting
		asort($arrFiles);
        asort($arrFolders);

        return array("files" => $arrFiles, "folders" => $arrFolders );
        
    }

    
}



echo "<pre>";

echo "<h2><a href=\"copy2project.php\">Copy 2 Project [".dirname(__FILE__)."]</a></h2>";



$objCopy = new class_copy2project();
$objCopy->doWork();

echo "</pre>";




?>
