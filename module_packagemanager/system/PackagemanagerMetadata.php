<?php
/*"******************************************************************************************************
*   (c) 2007-2016 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$                                  *
********************************************************************************************************/

namespace Kajona\Packagemanager\System;


use Kajona\System\System\AdminListableInterface;
use Kajona\System\System\Carrier;
use Kajona\System\System\Exception;
use Kajona\System\System\PharModule;
use Kajona\System\System\StringUtil;
use Kajona\System\System\XmlParser;
use Kajona\System\System\Zip;
use Phar;

/**
 * Helper class, used to read the metadata-files from packages or the filesystem.
 * Read access only!
 *
 * @author sidler@mulchprod.de
 * @since 4.0
 * @package module_packagemanager
 */
class PackagemanagerMetadata implements AdminListableInterface
{

    private $strTitle;
    private $strTarget;
    private $strDescription;
    private $strVersion;
    private $strAuthor;
    private $strType;
    private $bitProvidesInstaller;
    private $arrRequiredModules = array();
    private $arrScreenshots = array();

    private $strContentprovider;
    private $strPath;

    private $bitIsPhar = false;


    /**
     * Returns the icon the be used in lists.
     * Please be aware, that only the filename should be returned, the wrapping by getImageAdmin() is
     * done afterwards.
     *
     * @return string the name of the icon, not yet wrapped by getImageAdmin(). Alternatively, you may return an array containing
     *         [the image name, the alt-title]
     */
    public function getStrIcon()
    {
        if ($this->getStrType() == "TEMPLATE") {
            return "icon_dot";
        }
        else {
            return "icon_module";
        }

    }

    /**
     * @return mixed
     */
    public function getStrDisplayName()
    {
        return $this->getStrTitle();
    }

    /**
     * Only to remain compatbible with the common list rendering
     *
     * @return int
     */
    public function getIntRecordDeleted()
    {
        return 0;
    }

    /**
     * In nearly all cases, the additional info is rendered left to the action-icons.
     *
     * @return string
     */
    public function getStrAdditionalInfo()
    {
        return Carrier::getInstance()->getObjLang()->getLang("type_".$this->getStrType(), "packagemanager").", V ".$this->getStrVersion();
    }

    /**
     * If not empty, the returned string is rendered below the common title.
     *
     * @return string
     */
    public function getStrLongDescription()
    {
        return $this->getStrDescription();
    }

    /**
     * @return mixed
     */
    public function getSystemid()
    {
        return $this->getStrTitle();
    }


    /**
     * @return string
     */
    public function __toString()
    {
        return "Title: ".$this->getStrTitle()." Version: ".$this->getStrVersion()." Type: ".$this->getStrType()." Target: ".$this->getStrTarget()." Dependencies: ".print_r($this->getArrRequiredModules(), true);
    }

    /**
     * @param string $strPath
     *
     * @return void
     */
    public function autoInit($strPath)
    {
        if (uniSubstr($strPath, -4) == ".zip") {
            $this->initFromPackage($strPath);
        }
        elseif (PharModule::isPhar($strPath)) {
            $this->initFromPhar($strPath);
        }
        else {
            $this->initFromFilesystem($strPath);
        }

        $this->setStrPath($strPath);
    }

    /**
     * Reads the metadata-file saved with along with a packages located at the filesystem.
     *
     * @param string $strPackage
     *
     * @throws Exception
     * @return void
     */
    private function initFromFilesystem($strPackage)
    {

        if (!is_file(_realpath_.$strPackage."/metadata.xml")) {
            throw new Exception("file not found: "._realpath_.$strPackage."/metadata.xml", Exception::$level_ERROR);
        }

        $strMetadata = file_get_contents(_realpath_.$strPackage."/metadata.xml");
        $this->parseXMLDocument($strMetadata);
    }

    /**
     * @param string $strPackage
     *
     * @throws Exception
     */
    private function initFromPhar($strPackage)
    {
        $this->bitIsPhar = true;

        if (substr($strPackage, 0, 7) == "phar://") {
            $strFile = _realpath_.substr($strPackage, 7);
        }
        else {
            $strFile = _realpath_.$strPackage;
        }

        $strMetadata = "";
        //if its a project phar, we need to set another alias
        if(StringUtil::indexOf($strPackage, "/project") !== false) {
            //load the metadata without registering the phar, this could lead to multiple registered aliases
            $strMetadata = file_get_contents("phar://{$strFile}/metadata.xml");

        } else {
            $objPhar = new Phar($strFile);
            if(isset($objPhar["metadata.xml"])) {
                $strMetadata = file_get_contents($objPhar["metadata.xml"]->getPathname());
            }
        }

        if ($strMetadata == "") {
            throw new Exception("file not found: "._realpath_.$strPackage."/metadata.xml", Exception::$level_ERROR);
        }
        $this->parseXMLDocument($strMetadata);
    }

    /**
     * Reads the metadata-file from a zipped package.
     *
     * @param string $strPackagePath
     *
     * @throws Exception
     * @return void
     */
    private function initFromPackage($strPackagePath)
    {
        if (!is_file(_realpath_.$strPackagePath)) {
            throw new Exception("file not found: "._realpath_.$strPackagePath, Exception::$level_ERROR);
        }

        $objZip = new Zip();
        $strMetadata = $objZip->getFileFromArchive($strPackagePath, "/metadata.xml");

        if ($strMetadata === false) {
            throw new Exception("error reading metadata from ".$strPackagePath, Exception::$level_ERROR);
        }

        $this->parseXMLDocument($strMetadata);
    }

    /**
     * Parses the xml-document and sets the internal properties.
     *
     * @param string $strXmlDocument
     *
     * @return void
     */
    private function parseXMLDocument($strXmlDocument)
    {
        $objXml = new XmlParser();
        $objXml->loadString($strXmlDocument);
        $arrXml = $objXml->xmlToArray();


        $this->setStrTitle($arrXml["package"]["0"]["title"]["0"]["value"]);
        $this->setStrDescription($arrXml["package"]["0"]["description"]["0"]["value"]);
        $this->setStrVersion($arrXml["package"]["0"]["version"]["0"]["value"]);
        $this->setStrAuthor($arrXml["package"]["0"]["author"]["0"]["value"]);
        if (isset($arrXml["package"]["0"]["target"]["0"]["value"])) {
            $this->setStrTarget($arrXml["package"]["0"]["target"]["0"]["value"]);
        }

        $this->setStrType($arrXml["package"]["0"]["type"]["0"]["value"]);
        $this->setBitProvidesInstaller($arrXml["package"]["0"]["providesInstaller"]["0"]["value"] == "TRUE");

        if (is_array($arrXml["package"]["0"]["requiredModules"])) {
            foreach ($arrXml["package"]["0"]["requiredModules"] as $arrModules) {

                if (!is_array($arrModules)) {
                    continue;
                }

                foreach ($arrModules as $arrTempModule) {
                    if (!is_array($arrTempModule)) {
                        continue;
                    }


                    foreach ($arrTempModule as $arrOneModule) {
                        if (isset($arrOneModule["attributes"]["name"])) {
                            $strModule = $arrOneModule["attributes"]["name"];
                            $strVersion = $arrOneModule["attributes"]["version"];
                            $this->arrRequiredModules[$strModule] = $strVersion;
                        }
                    }
                }

            }
        }

        if (isset($arrXml["package"]["0"]["screenshots"]) && is_array($arrXml["package"]["0"]["screenshots"])) {
            foreach ($arrXml["package"]["0"]["screenshots"] as $arrScreenshots) {
                if (!is_array($arrScreenshots)) {
                    continue;
                }

                foreach ($arrScreenshots as $arrTempImage) {
                    if (!is_array($arrTempImage)) {
                        continue;
                    }

                    foreach ($arrTempImage as $arrOneImage) {
                        if (isset($arrOneImage["attributes"]["path"])) {
                            $strImage = $arrOneImage["attributes"]["path"];

                            if (in_array(uniStrtolower(uniSubstr($strImage, -4)), array(".jpg", ".jpeg", ".gif", ".png"))) {
                                $this->arrScreenshots[] = $strImage;
                            }
                        }
                    }
                }
            }
        }

    }


    /**
     * @param string $strAuthor
     *
     * @return void
     */
    public function setStrAuthor($strAuthor)
    {
        $this->strAuthor = $strAuthor;
    }

    /**
     * @return mixed
     */
    public function getStrAuthor()
    {
        return $this->strAuthor;
    }

    /**
     * @param string $strContentprovider
     *
     * @return void
     */
    public function setStrContentprovider($strContentprovider)
    {
        $this->strContentprovider = $strContentprovider;
    }

    /**
     * @return mixed
     */
    public function getStrContentprovider()
    {
        return $this->strContentprovider;
    }

    /**
     * @param string $strDescription
     *
     * @return void
     */
    public function setStrDescription($strDescription)
    {
        $this->strDescription = $strDescription;
    }

    /**
     * @return mixed
     */
    public function getStrDescription()
    {
        return $this->strDescription;
    }

    /**
     * @param string $strPath
     *
     * @return void
     */
    public function setStrPath($strPath)
    {
        $this->strPath = $strPath;
    }

    /**
     * @return mixed
     */
    public function getStrPath()
    {
        return $this->strPath;
    }

    /**
     * @param string $strTitle
     *
     * @return void
     */
    public function setStrTitle($strTitle)
    {
        $this->strTitle = $strTitle;
    }

    /**
     * @return mixed
     */
    public function getStrTitle()
    {
        return $this->strTitle;
    }

    /**
     * @param string $strVersion
     *
     * @return void
     */
    public function setStrVersion($strVersion)
    {
        $this->strVersion = $strVersion;
    }

    /**
     * @return mixed
     */
    public function getStrVersion()
    {
        return $this->strVersion;
    }

    /**
     * @param string $strType
     *
     * @return void
     */
    public function setStrType($strType)
    {
        $this->strType = $strType;
    }

    /**
     * @return mixed
     */
    public function getStrType()
    {
        return $this->strType;
    }

    /**
     * @param string $strTarget
     *
     * @return void
     */
    public function setStrTarget($strTarget)
    {
        $this->strTarget = $strTarget;
    }

    /**
     * @return mixed
     */
    public function getStrTarget()
    {
        return $this->strTarget;
    }

    /**
     * @param bool $bitProvidesInstaller
     *
     * @return void
     */
    public function setBitProvidesInstaller($bitProvidesInstaller)
    {
        $this->bitProvidesInstaller = $bitProvidesInstaller;
    }

    /**
     * @return mixed
     */
    public function getBitProvidesInstaller()
    {
        return $this->bitProvidesInstaller;
    }

    /**
     * @param array $arrRequiredModules
     *
     * @return void
     */
    public function setArrRequiredModules($arrRequiredModules)
    {
        $this->arrRequiredModules = $arrRequiredModules;
    }

    /**
     * @return array
     */
    public function getArrRequiredModules()
    {
        return $this->arrRequiredModules;
    }

    /**
     * @return array
     */
    public function getArrScreenshots()
    {
        return $this->arrScreenshots;
    }

    /**
     * @return boolean
     */
    public function getBitIsPhar()
    {
        return $this->bitIsPhar;
    }


}
