<?php


$bitRemoveSource = isset($argv[1]) && $argv[1] == "deletesource" ? true : false;

/**
 * CLI params:
 * deploypath=xxx
 * removesource=xxx
 *
 * Class PharCreator
 */
class PharCreator
{

    public $strDeployPath = "";
//    public $strDeployPath = "/Users/sidler/web/kajona_phar_only";

    public $bitRemoveSource = false;


    public function generatePhars()
    {
        $arrCores = scandir("./../");

        foreach ($arrCores as $strOneCore) {

            if (strpos($strOneCore, "core") === false) {
                continue;
            }

            $arrFiles = scandir("./../".$strOneCore);

            foreach ($arrFiles as $strFile) {

                if (is_dir(__DIR__."/../".$strOneCore."/".$strFile) && substr($strFile, 0, 7) == 'module_') {

                    $strModuleName = substr($strFile, 7);
                    $strPharName = "module_".$strModuleName.".phar";

                    $strTargetPath = __DIR__."/../".$strOneCore."/".$strPharName;
                    if ($this->strDeployPath != "" && is_dir($this->strDeployPath."/".$strOneCore)) {
                        $strTargetPath = $this->strDeployPath."/".$strOneCore."/".$strPharName;
                    }

                    $phar = new Phar(
                        $strTargetPath,
                        FilesystemIterator::CURRENT_AS_FILEINFO | FilesystemIterator::KEY_AS_FILENAME,
                        $strPharName
                    );
                    $phar->buildFromDirectory(__DIR__."/../".$strOneCore."/module_".$strModuleName);
                    $phar->setStub($phar->createDefaultStub());
                    // Compression with ZIP or GZ?
                    //$phar->convertToExecutable(Phar::ZIP);
                    //$phar->compress(Phar::GZ);
                    echo 'Generated phar '.$strPharName."\n";

                    if($this->bitRemoveSource) {
                        $this->rrmdir(__DIR__."/../".$strOneCore."/module_".$strModuleName);
                    }
                }

            }
        }

    }

    public function parseParams($arrParams) {
        $arrParsed = array();

        foreach($arrParams as $strOneParam) {
            $arrOneParam = explode("=", $strOneParam);
            if(count($arrOneParam) == 2) {
                $arrParsed[$arrOneParam[0]] = $arrOneParam[1];
            }
        }

        if(isset($arrParsed["deploypath"])) {
            $this->strDeployPath = $arrParsed["deploypath"];
        }

        if(isset($arrParsed["removesource"])) {
            $this->bitRemoveSource = $arrParsed["removesource"];
        }

    }

    /**
     * @param $strDir
     *
     * @see http://www.php.net/manual/de/function.rmdir.php#98622
     */
    private function rrmdir($strDir)
    {
        if (is_dir($strDir)) {
            $arrObjects = scandir($strDir);
            foreach ($arrObjects as $objObject) {
                if ($objObject != "." && $objObject != "..") {
                    if (filetype($strDir."/".$objObject) == "dir") {
                        $this->rrmdir($strDir."/".$objObject);
                    }
                    else {
                        unlink($strDir."/".$objObject);
                    }
                }
            }
            reset($arrObjects);
            rmdir($strDir);
        }
    }

}

$objCreator = new PharCreator();
$objCreator->parseParams(array_slice($argv, 1));
$objCreator->generatePhars();
