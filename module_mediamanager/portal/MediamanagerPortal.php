<?php
/*"******************************************************************************************************
*   (c) 2007-2015 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*    $Id$                                            *
********************************************************************************************************/

namespace Kajona\Mediamanager\Portal;

use Kajona\Mediamanager\System\MediamanagerFile;
use Kajona\Mediamanager\System\MediamanagerRepo;
use Kajona\Navigation\System\NavigationPoint;
use Kajona\Pages\Portal\PagesPortalController;
use Kajona\Pages\Portal\PagesPortaleditor;
use Kajona\Pages\System\PagesPortaleditorActionEnum;
use Kajona\Pages\System\PagesPortaleditorSystemidAction;
use Kajona\Rating\Portal\RatingPortal;
use Kajona\System\Portal\PortalController;
use Kajona\System\Portal\PortalInterface;
use Kajona\System\System\ArraySectionIterator;
use Kajona\System\System\Link;
use Kajona\System\System\Objectfactory;
use Kajona\System\System\SystemModule;
use Kajona\System\System\UserUser;

/**
 * Gallery Portal. Loads the thumbnails or detail-views
 *
 * @package module_mediamanager
 * @author sidler@mulchprod.de
 *
 * @module mediamanager
 * @moduleId _mediamanager_module_id_
 */
class MediamanagerPortal extends PortalController implements PortalInterface
{

    public static $INT_MODE_GALLERY = 0;
    public static $INT_MODE_DOWNLOADS = 1;

    protected $arrImageTypes = array(".png", ".gif", ".jpg", ".jpeg");


    /**
     * Constructor
     *
     * @param mixed $arrElementData
     */
    public function __construct($arrElementData = array(), $strSystemid = "")
    {
        parent::__construct($arrElementData, $strSystemid);

        if ($this->getAction() == "mediaFolder" || $this->getAction() == "imageFolder" || $this->getAction() == "openDlFolder") {
            $this->setAction("list");
        }

        if ($this->getAction() == "detailImage" || $this->getAction() == "detailDownload") {
            $this->setAction("fileDetails");
        }


        if (isset($this->arrElementData["gallery_mode"]) && $this->arrElementData["gallery_mode"] == 1) {
            $this->setAction("random");
        }

    }

    /**
     * Adds to code to enable to portaleditor
     *
     * @param string $strReturn
     *
     * @return string
     */
    private function addPortaleditorCode($strReturn)
    {

        $strRelevantSystemid = $this->arrElementData["repo_id"];

        //open a subfolder?
        if ($this->getParam("action") == "mediaFolder" && validateSystemid($this->getSystemid())) {
            $strRelevantSystemid = $this->getSystemid();
        }

        $strReturn = PagesPortaleditor::addPortaleditorContentWrapper($strReturn, $strRelevantSystemid);
        PagesPortaleditor::getInstance()->registerAction(
            new PagesPortaleditorSystemidAction(PagesPortaleditorActionEnum::EDIT(), Link::getLinkAdminHref($this->getArrModule("module"), "openFolder", "&pe=1&systemid={$strRelevantSystemid}"), $strRelevantSystemid)
        );

        return $strReturn;
    }


    /**
     * @param int $intStart
     * @param int $intEnd
     *
     * @return MediamanagerFile[]
     */
    protected function getArrFiles($intStart, $intEnd)
    {
        return MediamanagerFile::loadFilesDB($this->getSystemid(), false, true, $intStart, $intEnd);
    }

    /**
     * @return int
     */
    protected function getNumberOfEntriesOnLevel()
    {
        return MediamanagerFile::getFileCount($this->getSystemid(), false, true);
    }

    /**
     * Creates a list of thumbnails
     *
     * @return string
     * @permissions view
     */
    protected function actionList()
    {
        $strReturn = "";

        //Determin the prev_id to load
        if (!validateSystemid($this->getSystemid()) || !$this->checkIfRequestedIdIsInElementsTree()) {
            $this->setSystemid($this->arrElementData["repo_id"]);
        }

        $bitPageview = true;
        if ($this->arrElementData["repo_elementsperpage"] <= 0) {
            $this->arrElementData["repo_elementsperpage"] = $this->getNumberOfEntriesOnLevel();
            $bitPageview = false;
        }

        $objArraySectionIterator = new ArraySectionIterator($this->getNumberOfEntriesOnLevel());
        $objArraySectionIterator->setIntElementsPerPage($this->arrElementData["repo_elementsperpage"]);
        $objArraySectionIterator->setPageNumber($this->getParam("pv"));
        $objArraySectionIterator->setArraySection($this->getArrFiles($objArraySectionIterator->calculateStartPos(), $objArraySectionIterator->calculateEndPos()));

        $arrPagerContent = $this->objToolkit->simplePager(
            $objArraySectionIterator,
            $this->getLang("commons_next"),
            $this->getLang("commons_back"),
            $this->getParam("action"),
            $this->getPagename(),
            "&systemid=".$this->getSystemid(),
            "pv",
            "/module_mediamanager/".$this->arrElementData["repo_template"]
        );

        //Loop over every item and collect them
        $arrWrappingTemplate = array();
        $arrWrappingTemplate["systemid"] = $this->arrElementData["content_id"];
        $arrWrappingTemplate["folderlist"] = "";
        $arrWrappingTemplate["filelist"] = "";

        if ($objArraySectionIterator->getNumberOfElements() == 0) {
            $strReturn = $this->getLang("commons_list_empty");
        }

        $intFileCounter = 0;
        $arrRemainingFiles = array();

        //calc number of images outside the loop
        $intNrOfFilesPerRow = $this->getFilesPerRow($this->arrElementData["repo_template"]);

        /** @var MediamanagerFile $objOneFile */
        foreach ($objArraySectionIterator as $objOneFile) {

            //Check rights and the existance of placeholders
            if ($intNrOfFilesPerRow > 0 && $objOneFile->rightView()) {
                //Folder or file?

                //file
                if ($objOneFile->getIntType() == MediamanagerFile::$INT_TYPE_FILE) {
                    $arrWrappingTemplate["filelist"] .= $this->renderFileListEntry($objOneFile, $intFileCounter++, $intNrOfFilesPerRow, $arrRemainingFiles);
                }

                //Folder
                if ($objOneFile->getIntType() == MediamanagerFile::$INT_TYPE_FOLDER) {
                    $arrWrappingTemplate["folderlist"] .= $this->renderFolderListEntry($objOneFile);
                }
            }
        }
        //Print remaining files
        if (count($arrRemainingFiles) > 0) {
            $arrWrappingTemplate["filelist"] .= $this->objTemplate->fillTemplateFile($arrRemainingFiles, "/module_mediamanager/".$this->arrElementData["repo_template"], "filelist", false);
        }

        //and load the sourrounding template
        if ($bitPageview) {
            $arrWrappingTemplate["link_forward"] = $arrPagerContent["strForward"];
            $arrWrappingTemplate["link_pages"] = $arrPagerContent["strPages"];
            $arrWrappingTemplate["link_back"] = $arrPagerContent["strBack"];
        }
        $arrWrappingTemplate["pathnavigation"] = $this->generatePathnavi();
        $strReturn .= $this->objTemplate->fillTemplateFile($arrWrappingTemplate, "/module_mediamanager/".$this->arrElementData["repo_template"], "list");

        $strReturn = $this->addPortaleditorCode($strReturn);
        return $strReturn;
    }


    /**
     * Renders a single file in the list
     *
     * @param MediamanagerFile $objOneFile
     * @param int $intFileCounter
     * @param int $intNrOfFilesPerRow
     * @param array &$arrRemainingFiles
     *
     * @return string
     */
    private function renderFileListEntry(MediamanagerFile $objOneFile, $intFileCounter, $intNrOfFilesPerRow, &$arrRemainingFiles)
    {
        $arrFileTemplate = array();

        //check, if it's an image
        $strSuffix = uniStrtolower(uniSubstr($objOneFile->getStrFilename(), uniStrrpos($objOneFile->getStrFilename(), ".")));
        if (in_array($strSuffix, $this->arrImageTypes) && isset($this->arrElementData["gallery_maxh_d"]) && isset($this->arrElementData["gallery_maxw_d"])) {
            //provide image placeholders
            $arrFileTemplate["image_detail_src"] = $this->getImageUrl(
                $objOneFile->getStrFilename(),
                $this->arrElementData["gallery_maxh_d"],
                $this->arrElementData["gallery_maxw_d"],
                $this->arrElementData["gallery_text"],
                $this->arrElementData["gallery_overlay"],
                $objOneFile->getSystemid(),
                $this->arrElementData["content_id"]
            );
        }

        $arrFileTemplate["file_id"] = $objOneFile->getStrSystemid();
        $arrFileTemplate["file_name"] = $objOneFile->getStrName();
        $arrFileTemplate["file_filename"] = $objOneFile->getStrFilename();
        $arrFileTemplate["file_subtitle"] = $objOneFile->getStrSubtitle();
        $arrFileTemplate["file_description"] = $objOneFile->getStrDescription();
        $arrFileTemplate["file_size"] = bytesToString($objOneFile->getIntFileSize());
        $arrFileTemplate["file_hits"] = $objOneFile->getIntHits();
        $arrFileTemplate["file_elementid"] = $this->arrElementData["content_id"];
        $arrFileTemplate["file_lmtime"] = timeToString(filemtime(_realpath_.$objOneFile->getStrFilename()));
        if (validateSystemid($objOneFile->getOwnerId())) {
            $objUser = new UserUser($objOneFile->getOwnerId());
            $arrFileTemplate["file_owner"] = $objUser->getStrUsername();
        }

        if ($objOneFile->rightRight2()) {
            $arrFileTemplate["file_link_href"] = _webpath_."/download.php?systemid=".$objOneFile->getSystemid();
            $arrFileTemplate["file_link"] = "<a href=\""._webpath_."/download.php?systemid=".$objOneFile->getSystemid()."\">".$this->getLang("download_link")."</a>";
        }
        $this->fileListTemplateHook($objOneFile, $arrFileTemplate);

        //ratings available?
        if ($objOneFile->getFloatRating() !== null) {
            /** @var $objRating RatingPortal */
            $objRating = SystemModule::getModuleByName("rating")->getPortalInstanceOfConcreteModule();
            $arrFileTemplate["file_rating"] = $objRating->buildRatingBar(
                $objOneFile->getFloatRating(),
                $objOneFile->getIntRatingHits(),
                $objOneFile->getSystemid(),
                $objOneFile->isRateableByUser(),
                $objOneFile->rightRight3()
            );
        }

        $arrFileTemplate["file_details_href"] = Link::getLinkPortalHref($this->getPagename(), "", "fileDetails", "", $objOneFile->getSystemid(), $this->getStrPortalLanguage(), $objOneFile->getStrName());

        //render the single file
        $strCurrentImage = $this->objTemplate->fillTemplateFile($arrFileTemplate, "/module_mediamanager/".$this->arrElementData["repo_template"], "filelist_file");
        $arrRemainingFiles["file_".$intFileCounter % $intNrOfFilesPerRow] = $strCurrentImage;

        //already rendered enough files?
        if (count($arrRemainingFiles) == $intNrOfFilesPerRow) {
            $strTemp = $this->objTemplate->fillTemplateFile($arrRemainingFiles, "/module_mediamanager/".$this->arrElementData["repo_template"], "filelist");
            $arrRemainingFiles = array();
            return $strTemp;
        }

        return "";
    }


    /**
     * Renders a single folder within the list of entries
     *
     * @param MediamanagerFile $objOneFile
     *
     * @return string
     */
    private function renderFolderListEntry(MediamanagerFile $objOneFile)
    {
        $arrFolder = array();
        $arrFolder["folder_id"] = $objOneFile->getSystemid();
        $arrFolder["folder_name"] = $objOneFile->getStrName();
        $arrFolder["folder_description"] = $objOneFile->getStrDescription();
        $arrFolder["folder_subtitle"] = $objOneFile->getStrSubtitle();
        $arrFolder["folder_href"] = Link::getLinkPortalHref($this->getPagename(), "", "mediaFolder", "", $objOneFile->getSystemid(), "", $objOneFile->getStrName());

        $objFirstFile = $this->getFirstFileInFolder($objOneFile->getSystemid());
        if ($objFirstFile != null) {
            $strSuffix = uniStrtolower(uniSubstr($objFirstFile->getStrFilename(), uniStrrpos($objFirstFile->getStrFilename(), ".")));
            if (in_array($strSuffix, array(".jpg", ".jpeg", ".gif", ".png"))) {
                //provide image placeholders
                $arrFolder["folder_preview_image_src"] = $objFirstFile->getStrFilename();
            }
        }

        return $this->objTemplate->fillTemplateFile(
            $arrFolder,
            "/module_mediamanager/".$this->arrElementData["repo_template"],
            (isset($arrFolder["folder_preview_image_src"]) && $this->objTemplate->providesSection("/module_mediamanager/".$this->arrElementData["repo_template"], "folderlist_preview") ? "folderlist_preview" : "folderlist")
        );

    }


    /**
     * Use this hook-method if you want to add additional placeholders to the portal-content of a single file-entry
     * within a list.
     *
     * @param MediamanagerFile $objOneFile
     * @param string[] &$arrTemplate
     *
     * @return void
     */
    protected function fileListTemplateHook(MediamanagerFile $objOneFile, &$arrTemplate)
    {

    }


    /**
     * Prints a file as a detailed-view
     * and generates forward / backward links + a strip of prev / next files
     *
     * @param bool $bitRegisterAdditionalTitle
     *
     * @return string
     */
    protected function actionFileDetails($bitRegisterAdditionalTitle = true)
    {

        if (!$this->checkIfRequestedIdIsInElementsTree()) {
            return $this->actionList();
        }

        $bitIsImage = false;

        //Load record
        $objFile = new MediamanagerFile($this->getSystemid());

        //common fields
        $arrDetailsTemplate = array();
        $arrDetailsTemplate["file_name"] = $objFile->getStrName();
        $arrDetailsTemplate["file_description"] = $objFile->getStrDescription();
        $arrDetailsTemplate["file_subtitle"] = $objFile->getStrSubtitle();
        $arrDetailsTemplate["file_filename"] = $objFile->getStrFilename();
        $arrDetailsTemplate["file_size"] = bytesToString($objFile->getIntFileSize());
        $arrDetailsTemplate["file_hits"] = $objFile->getIntHits();
        $arrDetailsTemplate["file_systemid"] = $objFile->getSystemid();
        $arrDetailsTemplate["file_elementid"] = $this->arrElementData["content_id"];

        $arrDetailsTemplate["file_lmtime"] = timeToString(filemtime(_realpath_.$objFile->getStrFilename()));
        if (validateSystemid($objFile->getOwnerId())) {
            $objUser = new UserUser($objFile->getOwnerId());
            $arrDetailsTemplate["file_owner"] = $objUser->getStrUsername();
        }

        if ($objFile->rightRight2()) {
            $arrDetailsTemplate["file_link_href"] = _webpath_."/download.php?systemid=".$objFile->getSystemid();
            $arrDetailsTemplate["file_link"] = "<a href=\""._webpath_."/download.php?systemid=".$objFile->getSystemid()."\">".$this->getLang("download_link")."</a>";
        }

        //if its an image, provide additional information
        $strSuffix = uniStrtolower(uniSubstr($objFile->getStrFilename(), uniStrrpos($objFile->getStrFilename(), ".")));
        if (in_array($strSuffix, $this->arrImageTypes) && isset($this->arrElementData["gallery_maxh_d"]) && isset($this->arrElementData["gallery_maxw_d"])) {
            $bitIsImage = true;
            $arrDetailsTemplate["image_src"] = $this->getImageUrl(
                $objFile->getStrFilename(),
                $this->arrElementData["gallery_maxh_d"],
                $this->arrElementData["gallery_maxw_d"],
                $this->arrElementData["gallery_text"],
                $this->arrElementData["gallery_overlay"],
                $objFile->getSystemid(),
                $this->arrElementData["content_id"]
            );
        }

        $arrStripIds = $this->getNextPrevIds();
        $arrDetailsTemplate["backlink"] = ($arrStripIds["backward_1"] != "" ? Link::getLinkPortal($this->getPagename(), "", "", $this->getLang("commons_back"), "fileDetails", "", $arrStripIds["backward_1"]) : "");
        $arrDetailsTemplate["forwardlink"] = ($arrStripIds["forward_1"] != "" ? Link::getLinkPortal($this->getPagename(), "", "", $this->getLang("commons_next"), "fileDetails", "", $arrStripIds["forward_1"]) : "");

        //next /prev 3 files
        for ($intI = 1; $intI <= 3; $intI++) {
            if ($arrStripIds["forward_".$intI] != "") {
                $objCurFile = new MediamanagerFile($arrStripIds["forward_".$intI]);
                $arrDetailsTemplate["forwardlink_".$intI] = $this->renderFileStripEntry($objCurFile);
            }

            if ($arrStripIds["backward_".$intI] != "") {
                $objCurFile = new MediamanagerFile($arrStripIds["backward_".$intI]);
                $arrDetailsTemplate["backlink_".$intI] = $this->renderFileStripEntry($objCurFile);
            }
        }

        //current file
        $arrDetailsTemplate["filestrip_current"] = $this->renderFileStripEntry($objFile);

        $arrDetailsTemplate["overview"] = getLinkPortal($this->getPagename(), "", "", $this->getLang("overview"), "mediaFolder", "", $objFile->getPrevId());
        $arrDetailsTemplate["pathnavigation"] = $this->generatePathnavi(true);

        //ratings available?
        if ($objFile->getFloatRating() !== null) {
            /** @var $objRating RatingPortal */
            $objRating = SystemModule::getModuleByName("rating")->getPortalInstanceOfConcreteModule();
            $arrDetailsTemplate["file_rating"] = $objRating->buildRatingBar(
                $objFile->getFloatRating(),
                $objFile->getIntRatingHits(),
                $objFile->getSystemid(),
                $objFile->isRateableByUser(),
                $objFile->rightRight3()
            );
        }

        $strReturn = $this->objTemplate->fillTemplateFile($arrDetailsTemplate, "/module_mediamanager/".$this->arrElementData["repo_template"], "filedetail");

        //Add pe code
        $strReturn = PagesPortaleditor::addPortaleditorContentWrapper($strReturn, $objFile->getSystemid());

        PagesPortaleditor::getInstance()->registerAction(
            new PagesPortaleditorSystemidAction(PagesPortaleditorActionEnum::EDIT(), Link::getLinkAdminHref($this->getArrModule("module"), "editFile", "&pe=1&systemid={$objFile->getSystemid()}"), $objFile->getSystemid())
        );


        //set the name of the current image to the page title via class_pages
        if ($bitRegisterAdditionalTitle) {
            PagesPortalController::registerAdditionalTitle($objFile->getStrName());
        }

        //Update view counter
        if ($bitIsImage) {
            $objFile->increaseHits();
        }

        return $this->addPortaleditorCode($strReturn);
    }


    /**
     * Renders a single element of the file-strip
     *
     * @param MediamanagerFile $objCurFile
     *
     * @return string
     */
    private function renderFileStripEntry(MediamanagerFile $objCurFile)
    {
        $arrTemplate = array(
            "file_detail_href" => Link::getLinkPortalHref($this->getPagename(), "", "fileDetails", "", $objCurFile->getSystemid(), $this->getStrPortalLanguage(), $objCurFile->getStrName()),
            "file_name"        => $objCurFile->getStrName(),
            "file_systemid"    => $objCurFile->getStrSystemid(),
            "file_filename"    => $objCurFile->getStrFilename(),
            "file_elementid"   => $this->arrElementData["content_id"]
        );
        return $this->objTemplate->fillTemplateFile($arrTemplate, "/module_mediamanager/".$this->arrElementData["repo_template"], "filedetail_strip");
    }


    /**
     * Selects a random file out of the selected repo and creates a detail-view
     *
     * @return string
     */
    protected function actionRandom()
    {
        //Fetch all images of the selected category
        $arrRandom = $this->loadFilesRecursive($this->arrElementData["repo_id"]);
        $intRand = array_rand($arrRandom);
        //set the systemid as current
        if (isset($arrRandom[$intRand])) {
            $this->setSystemid($arrRandom[$intRand]);
        }
        //and load all
        $strReturn = $this->actionFileDetails(false);
        $strReturn = $this->addPortaleditorCode($strReturn);
        return $strReturn;
    }

    /**
     * Loads all images to find one randomly
     *
     * @param string $strStartID
     *
     * @return mixed
     */
    private function loadFilesRecursive($strStartID)
    {
        $arrRandom = array();
        $arrCurrLevel = MediamanagerFile::loadFilesDB($strStartID, false, true);
        if (count($arrCurrLevel) > 0) {
            foreach ($arrCurrLevel as $objOneImage) {
                if ($objOneImage->getIntType() == 0 && $objOneImage->rightView()) {
                    $arrRandom[] = $objOneImage->getSystemid();
                }

                //Load all childs
                $arrTemp = $this->loadFilesRecursive($objOneImage->getSystemid());
                foreach ($arrTemp as $strOneTemp) {
                    $arrRandom[] = $strOneTemp;
                }
            }
        }
        return $arrRandom;
    }


    /**
     * Helper function to generate the matching image.php-url for a given set of params.
     * If possible, the fastest manipulation (only resizing) is used.
     *
     * @param string $strImage
     * @param int $intHeight
     * @param int $intWidth
     * @param string $strText
     * @param string $strOverlayImage
     * @param string $strSystemid
     * @param string $strElementId
     *
     * @return string
     */
    private function getImageUrl($strImage, $intHeight, $intWidth, $strText, $strOverlayImage, $strSystemid, $strElementId)
    {

        if (is_file(_realpath_.$strImage)) {
            //If theres text to put over the image, manipulate image "inline",
            //otherwise let the work do image.php -> kinda multithreading ;)
            if ($strText == "" && $strOverlayImage == "") {
                return _webpath_."/image.php?image=".urlencode($strImage)."&amp;maxWidth=".$intWidth."&amp;maxHeight=".$intHeight;
            }

            return _webpath_."/image.php?systemid=".$strSystemid."&amp;elementid=".$strElementId;

        }

        return "Error manipulating image!";
    }


    /**
     * Generates a litte path-navigation across the folders
     *
     * @param bool $bitCurrentViewIsDetail
     *
     * @return string
     */
    private function generatePathnavi($bitCurrentViewIsDetail = false)
    {
        $strReturn = "";
        //Load the current record
        $objData = Objectfactory::getInstance()->getObject($this->getSystemid());
        $objGallery = new MediamanagerRepo($this->arrElementData["repo_id"]);

        if ($objGallery->rightView() && $objData != null) {

            $arrTemplate = array();
            //Name and link
            if ($bitCurrentViewIsDetail) {
                $arrTemplate["pathnavigation_point"] = Link::getLinkPortal($this->getPagename(), "", "_self", $objData->getStrDisplayName(), "detailImage", "", $objData->getSystemid(), "", "", $objData->getStrDisplayName());
            }
            else {
                $arrTemplate["pathnavigation_point"] = Link::getLinkPortal($this->getPagename(), "", "_self", $objData->getStrDisplayName(), "mediaFolder", "", $objData->getSystemid(), "", "", $objData->getStrDisplayName());
            }

            $strReturn .= $this->objTemplate->fillTemplateFile($arrTemplate, "/module_mediamanager/".$this->arrElementData["repo_template"], "pathnavigation_level");

            while (!$objData instanceof MediamanagerRepo) {
                $objData = Objectfactory::getInstance()->getObject($objData->getPrevId());

                $arrTemplate["pathnavigation_point"] = Link::getLinkPortal($this->getPagename(), "", "_self", $objData->getStrDisplayName(), "mediaFolder", "", $objData->getSystemid());
                $strReturn = $this->objTemplate->fillTemplateFile($arrTemplate, "/module_mediamanager/".$this->arrElementData["repo_template"], "pathnavigation_level").$strReturn;
            }

        }

        return $strReturn;
    }


    /**
     * Tries to load the fist image under the passed systemid.
     * If available, the instance is returned, otherwise null
     *
     * @param string $strFolderId
     *
     * @return MediamanagerFile
     */
    private function getFirstFileInFolder($strFolderId)
    {
        //load the files in the passed folder
        $arrSubLevel = MediamanagerFile::loadFilesDB($strFolderId, false, true);
        if (count($arrSubLevel) > 0) {
            foreach ($arrSubLevel as $objOneImage) {
                if ($objOneImage->getIntType() == MediamanagerFile::$INT_TYPE_FILE && $objOneImage->rightView()) {
                    return $objOneImage;
                }
            }
        }

        return null;
    }

    /**
     * Determins the systemids of the previous / next file
     *
     * @return mixed
     */
    private function getNextPrevIds()
    {
        $arrReturn = array();

        //Load all images on the current level
        $objCur = Objectfactory::getInstance()->getObject($this->getSystemid());
        $arrImagesLevel = MediamanagerFile::loadFilesDB($objCur->getPrevId(), MediamanagerFile::$INT_TYPE_FILE, true);
        //Sort out the unallowed ones
        foreach ($arrImagesLevel as $intKey => $objOneImage) {
            if (!$objOneImage->rightView()) {
                unset($arrImagesLevel[$intKey]);
            }
        }

        //make array-keys numeric
        /** @var $arrImagesLevel MediamanagerFile[] */
        $arrImagesLevel = array_values($arrImagesLevel);
        //Search the current image
        $intKeyHit = 0;
        foreach ($arrImagesLevel as $intKeyHit => $objOneImage) {
            if ($objOneImage->getSystemid() == $this->getSystemid()) {
                break;
            }
        }

        $arrReturn["forward_1"] = (isset($arrImagesLevel[$intKeyHit + 1]) ? $arrImagesLevel[$intKeyHit + 1]->getSystemid() : "");
        $arrReturn["forward_2"] = (isset($arrImagesLevel[$intKeyHit + 2]) ? $arrImagesLevel[$intKeyHit + 2]->getSystemid() : "");
        $arrReturn["forward_3"] = (isset($arrImagesLevel[$intKeyHit + 3]) ? $arrImagesLevel[$intKeyHit + 3]->getSystemid() : "");;

        $arrReturn["backward_1"] = (isset($arrImagesLevel[$intKeyHit - 1]) ? $arrImagesLevel[$intKeyHit - 1]->getSystemid() : "");;
        $arrReturn["backward_2"] = (isset($arrImagesLevel[$intKeyHit - 2]) ? $arrImagesLevel[$intKeyHit - 2]->getSystemid() : "");;
        $arrReturn["backward_3"] = (isset($arrImagesLevel[$intKeyHit - 3]) ? $arrImagesLevel[$intKeyHit - 3]->getSystemid() : "");;

        return $arrReturn;
    }


    /**
     * Validates if the systemid requested is a valid element of the mediamanager-tree selected via the pageeelement.
     * Avoids, that the element reacts on systemids not being present in the current tree.
     *
     * @return bool
     */
    private function checkIfRequestedIdIsInElementsTree()
    {
        $bitReturn = true;

        //check if requested systemid is part of the elements tree
        $objData = Objectfactory::getInstance()->getObject($this->getSystemid());

        while (!$objData instanceof MediamanagerRepo && $objData != null) {
            $objData = Objectfactory::getInstance()->getObject($objData->getPrevId());
        }

        //if the requested systemid belong to the tree set in the pageelement, the systemids should match.
        if ($objData == null || $objData->getSystemid() != $this->arrElementData["repo_id"]) {
            $bitReturn = false;
        }

        return $bitReturn;
    }

    /**
     * Calculates the number of images per row as defined in the template.
     *
     * @param string $strTemplate
     *
     * @return int
     */
    private function getFilesPerRow($strTemplate)
    {

        $arrElements = $this->objTemplate->getElementsFromTemplateFile("/module_mediamanager/".$strTemplate, "filelist");
        return count($arrElements);
    }


    /**
     * @return array
     */
    public function getNavigationNodes()
    {
        $arrReturn = array();

        $objRepo = new MediamanagerRepo($this->arrElementData["repo_id"]);
        $objPoint = new NavigationPoint();
        $objPoint->setIntRecordStatus(1);
        $objPoint->setStrName($objRepo->getStrTitle());
        $objPoint->setStrPageI($this->getPagename());
        $objPoint->setSystemid($objRepo->getSystemid());
        $objPoint->setStrLinkSystemid($objRepo->getSystemid());
        $objPoint->setStrLinkAction("mediaFolder");
        $objPoint->setBitIsForeignNode(true);

        $arrReturn["node"] = $objPoint;
        $arrReturn["subnodes"] = $this->getNavigationNodesHelper($objPoint->getSystemid());

        return $arrReturn;

    }

    /**
     * @param string $strParentId
     *
     * @return array
     */
    private function getNavigationNodesHelper($strParentId)
    {

        $arrFoldersDB = MediamanagerFile::loadFilesDB($strParentId, MediamanagerFile::$INT_TYPE_FOLDER, true);

        $arrReturn = array();
        foreach ($arrFoldersDB as $objOneFolder) {
            $objPoint = new NavigationPoint();
            $objPoint->setIntRecordStatus(1);
            $objPoint->setStrName($objOneFolder->getStrName());
            $objPoint->setStrPageI($this->getPagename());
            $objPoint->setSystemid($objOneFolder->getSystemid());
            $objPoint->setStrLinkSystemid($objOneFolder->getSystemid());
            $objPoint->setStrLinkAction("mediaFolder");
            $objPoint->setBitIsForeignNode(true);

            $arrTemp = array(
                "node"     => $objPoint,
                "subnodes" => $this->getNavigationNodesHelper($objOneFolder->getSystemid())
            );

            $arrReturn[] = $arrTemp;
        }

        return $arrReturn;
    }
}
