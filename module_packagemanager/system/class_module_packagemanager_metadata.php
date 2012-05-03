<?php
/*"******************************************************************************************************
*   (c) 2007-2012 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id: class_module_tags_admin.php 4485 2012-02-07 12:48:04Z sidler $                                  *
********************************************************************************************************/

/**
 * Helper class, used to read the metadata-files from packages or the filesystem.
 * Read access only!
 *
 * @author sidler@mulchprod.de
 * @since 4.0
 * @package module_packagemanager
 */
class class_module_packagemanager_metadata implements interface_admin_listable {

    private $strTitle;
    private $strTarget;
    private $strDescription;
    private $strVersion;
    private $strAuthor;
    private $strType;
    private $bitProvidesInstaller;
    private $strRequiredModules;
    private $strMinVersion;

    private $strContentprovider;
    private $strPath;



    /**
     * Returns the icon the be used in lists.
     * Please be aware, that only the filename should be returned, the wrapping by getImageAdmin() is
     * done afterwards.
     *
     * @return string the name of the icon, not yet wrapped by getImageAdmin(). Alternatively, you may return an array containing
     *         [the image name, the alt-title]
     */
    public function getStrIcon() {
        if($this->getStrType() == "TEMPLATE")
            return "icon_dot.gif";
        else
            return "icon_module.gif";

    }

    public function getStrDisplayName() {
        return $this->getStrTitle();
    }

    /**
     * In nearly all cases, the additional info is rendered left to the action-icons.
     *
     * @return string
     */
    public function getStrAdditionalInfo() {
        return class_carrier::getInstance()->getObjLang()->getLang("type_".$this->getStrType(), "packagemanager").", V ".$this->getStrVersion();
    }

    /**
     * If not empty, the returned string is rendered below the common title.
     * @return string
     */
    public function getStrLongDescription() {
        return $this->getStrDescription();
    }

    public function getSystemid() {
        return $this->getStrTitle();
    }


    public function __toString() {
        return "Title: ".$this->getStrTitle()." Version: ".$this->getStrVersion()." Type: ".$this->getStrType()." Target: ".$this->getStrTarget();
    }

    /**
     * @param $strPath
     */
    public function autoInit($strPath) {
        if(uniSubstr($strPath, -4) == ".zip")
            $this->initFromPackage($strPath);
        else
            $this->initFromFilesystem($strPath);

        $this->setStrPath($strPath);
    }

    /**
     * Reads the metadata-file saved with along with a packages located at the filesystem.
     *
     * @param $strPackage
     * @throws class_exception
     */
    private function initFromFilesystem($strPackage) {

        if(!is_file(_realpath_.$strPackage."/metadata.xml"))
            throw new class_exception("file not found: "._realpath_.$strPackage."/metadata.xml", class_exception::$level_ERROR);

        $strMetadata = file_get_contents(_realpath_.$strPackage."/metadata.xml");
        $this->parseXMLDocument($strMetadata);
    }

    /**
     * Reads the metadata-file from a zipped package.
     *
     * @param $strPackagePath
     * @throws class_exception
     */
    private function initFromPackage($strPackagePath) {
        if(!is_file(_realpath_.$strPackagePath))
            throw new class_exception("file not found: "._realpath_.$strPackagePath, class_exception::$level_ERROR);

        $objZip = new class_zip();
        $strMetadata = $objZip->getFileFromArchive($strPackagePath, "/metadata.xml");

        if($strMetadata === false)
            throw new class_exception("error reading metadata from ".$strPackagePath, class_exception::$level_ERROR);

        $this->parseXMLDocument($strMetadata);
    }

    /**
     * Parses the xml-document and sets the internal properties.
     *
     * @param $strXmlDocument
     */
    private function parseXMLDocument($strXmlDocument) {
        $objXml = new class_xml_parser();
        $objXml->loadString($strXmlDocument);
        $arrXml = $objXml->xmlToArray();


        $this->setStrTitle($arrXml["package"]["0"]["title"]["0"]["value"]);
        $this->setStrDescription($arrXml["package"]["0"]["description"]["0"]["value"]);
        $this->setStrVersion($arrXml["package"]["0"]["version"]["0"]["value"]);
        $this->setStrAuthor($arrXml["package"]["0"]["author"]["0"]["value"]);
        $this->setStrTarget($arrXml["package"]["0"]["target"]["0"]["value"]);
        $this->setStrType($arrXml["package"]["0"]["type"]["0"]["value"]);
        $this->setBitProvidesInstaller($arrXml["package"]["0"]["providesInstaller"]["0"]["value"] == "TRUE");
        $this->setStrRequiredModules($arrXml["package"]["0"]["requiredModules"]["0"]["value"]);
        $this->setStrMinVersion($arrXml["package"]["0"]["minSystemVersion"]["0"]["value"]);
    }



    public function setStrAuthor($strAuthor) {
        $this->strAuthor = $strAuthor;
    }

    public function getStrAuthor() {
        return $this->strAuthor;
    }

    public function setStrContentprovider($strContentprovider) {
        $this->strContentprovider = $strContentprovider;
    }

    public function getStrContentprovider() {
        return $this->strContentprovider;
    }

    public function setStrDescription($strDescription) {
        $this->strDescription = $strDescription;
    }

    public function getStrDescription() {
        return $this->strDescription;
    }

    public function setStrPath($strPath) {
        $this->strPath = $strPath;
    }

    public function getStrPath() {
        return $this->strPath;
    }

    public function setStrTitle($strTitle) {
        $this->strTitle = $strTitle;
    }

    public function getStrTitle() {
        return $this->strTitle;
    }

    public function setStrVersion($strVersion) {
        $this->strVersion = $strVersion;
    }

    public function getStrVersion() {
        return $this->strVersion;
    }

    public function setStrType($strType) {
        $this->strType = $strType;
    }

    public function getStrType() {
        return $this->strType;
    }

    public function setStrTarget($strTarget) {
        $this->strTarget = $strTarget;
    }

    public function getStrTarget() {
        return $this->strTarget;
    }

    public function setBitProvidesInstaller($bitProvidesInstaller) {
        $this->bitProvidesInstaller = $bitProvidesInstaller;
    }

    public function getBitProvidesInstaller() {
        return $this->bitProvidesInstaller;
    }

    public function setStrMinVersion($strMinVersion) {
        $this->strMinVersion = $strMinVersion;
    }

    public function getStrMinVersion() {
        return $this->strMinVersion;
    }

    public function setStrRequiredModules($strRequiredModules) {
        $this->strRequiredModules = $strRequiredModules;
    }

    public function getStrRequiredModules() {
        return $this->strRequiredModules;
    }


}