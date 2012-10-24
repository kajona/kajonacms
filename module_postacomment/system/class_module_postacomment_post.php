<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2012 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$                           *
********************************************************************************************************/

/**
 * Model for comment itself
 *
 * @package module_postacomment
 * @author sidler@mulchprod.de
 *
 * @targetTable postacomment.postacomment_id
 */
class class_module_postacomment_post extends class_model implements interface_model, interface_sortable_rating, interface_admin_listable, interface_recorddeleted_listener {

    /**
     * @var string
     * @tableColumn postacomment_title
     *
     * @fieldType text
     * @fieldLabel form_comment_title
     */
    private $strTitle;

    /**
     * @var string
     * @tableColumn postacomment_comment
     *
     * @fieldMandatory
     * @fieldType textarea
     * @fieldLabel postacomment_comment
     */
    private $strComment;

    /**
     * @var string
     * @tableColumn postacomment_username
     *
     * @fieldMandatory
     * @fieldType text
     */
    private $strUsername;

    /**
     * @var int
     * @tableColumn postacomment_date
     */
    private $intDate;

    /**
     * @var string
     * @tableColumn postacomment_page
     */
    private $strAssignedPage;

    /**
     * @var string
     * @tableColumn postacomment_systemid
     */
    private $strAssignedSystemid;

    /**
     * @var string
     * @tableColumn postacomment_language
     */
    private $strAssignedLanguage;

    /**
     * Constructor to create a valid object
     *
     * @param string $strSystemid (use "" on new objects)
     */
    public function __construct($strSystemid = "") {

        $this->setArrModuleEntry("modul", "postacomment");
        $this->setArrModuleEntry("moduleId", _postacomment_modul_id_);

        parent::__construct($strSystemid);
    }

    public function getStrDisplayName() {
        return $this->getStrTitle();
    }

    /**
     * Returns the icon the be used in lists.
     * Please be aware, that only the filename should be returned, the wrapping by getImageAdmin() is
     * done afterwards.
     *
     * @return string the name of the icon, not yet wrapped by getImageAdmin(). Alternatively, you may return an array containing
     *         [the image name, the alt-title]
     */
    public function getStrIcon() {
        return "icon_comment.png";
    }

    /**
     * In nearly all cases, the additional info is rendered left to the action-icons.
     *
     * @return string
     */
    public function getStrAdditionalInfo() {
        return timeToString($this->intDate);
    }

    /**
     * If not empty, the returned string is rendered below the common title.
     *
     * @return string
     */
    public function getStrLongDescription() {
        return uniStrTrim($this->strComment, 120);
    }


    /**
     * Returns a list of posts
     *
     * @param bool $bitJustActive
     * @param string $strPagefilter
     * @param string $strSystemidfilter false to ignore the filter
     * @param string $strLanguagefilter
     * @param bool $intStart
     * @param bool $intEnd
     *
     * @return class_module_postacomment_post[]
     */
    public static function loadPostList($bitJustActive = true, $strPagefilter = "", $strSystemidfilter = "", $strLanguagefilter = "", $intStart = false, $intEnd = false) {
        $arrReturn = array();
        $arrParams = array();
        $strFilter = "";
        if($strPagefilter != "") {
            $strFilter .= " AND postacomment_page = ? ";
            $arrParams[] = $strPagefilter;
        }

        if($strSystemidfilter !== false) {
            $strFilter .= " AND postacomment_systemid = ? ";
            $arrParams[] = $strSystemidfilter;
        }

        if($strLanguagefilter != "") {//check against '' to remain backwards-compatible
            $strFilter .= " AND (postacomment_language = ? OR postacomment_language = '')";
            $arrParams[] = $strLanguagefilter;
        }
        if($bitJustActive) {
            $strFilter .= " AND system_status = 1 ";
        }

        $strQuery = "SELECT system_id
					 FROM "._dbprefix_."postacomment,
						  "._dbprefix_."system
					 WHERE system_id = postacomment_id "
					 . $strFilter ."
					 ORDER BY postacomment_page ASC,
						      postacomment_language ASC,
							  postacomment_date DESC";

        $arrComments = class_carrier::getInstance()->getObjDB()->getPArray($strQuery, $arrParams, $intStart, $intEnd);

        if(count($arrComments) > 0) {
            foreach($arrComments as $arrOneComment) {
                $arrReturn[] = new class_module_postacomment_post($arrOneComment["system_id"]);
            }
        }

        return $arrReturn;
    }

    /**
     * Counts the number of posts currently in the database
     *
     * @param bool $bitJustActive
     * @param string $strPageid
     * @param bool|string $strSystemidfilter false to ignore the filter
     * @param string $strLanguagefilter
     *
     * @return int
     */
    public static function getNumberOfPostsAvailable($bitJustActive = true, $strPageid = "", $strSystemidfilter = false, $strLanguagefilter = "") {
        $strQuery = "SELECT COUNT(*) FROM "._dbprefix_."postacomment, "._dbprefix_."system WHERE system_id = postacomment_id ";
        $arrParams = array();

        if($strPageid != "") {
            $strQuery .= " AND postacomment_page= ?";
            $arrParams[] = $strPageid;
        }

        if($bitJustActive) {
            $strQuery .= " AND system_status = 1 ";
        }

        if($strSystemidfilter !== false) {
            $strQuery .= " AND postacomment_systemid = ? ";
            $arrParams[] = $strSystemidfilter;
        }

        if($strLanguagefilter != "") {//check against '' to remain backwards-compatible
            $strQuery .= " AND (postacomment_language = ? OR postacomment_language = '')";
            $arrParams[] = $strLanguagefilter;
        }

        $arrRow = class_carrier::getInstance()->getObjDB()->getPRow($strQuery, $arrParams);
        return $arrRow["COUNT(*)"];
    }

    /**
     * Called whenever a records was deleted using the common methods.
     * Implement this method to be notified when a record is deleted, e.g. to to additional cleanups afterwards.
     * There's no need to register the listener, this is done automatically.
     *
     * Make sure to return a matching boolean-value, otherwise the transaction may be rolled back.
     *
     * @param $strSystemid
     * @param string $strSourceClass
     *
     * @return bool
     */
    public function handleRecordDeletedEvent($strSystemid, $strSourceClass) {
        $bitReturn = true;
        //module installed?
        if($strSourceClass == "class_module_postacomment_post" || class_module_system_module::getModuleByName("postacomment") == null)
            return true;

        //ok, so search for a records matching
        $arrPosts1 = class_module_postacomment_post::loadPostList(false, $strSystemid);
        $arrPosts2 = class_module_postacomment_post::loadPostList(false, "", $strSystemid);

        //and delete
        foreach($arrPosts1 as $objOnePost) {
            $bitReturn &= $objOnePost->deleteObject();
        }

        foreach($arrPosts2 as $objOnePost) {
            $bitReturn &= $objOnePost->deleteObject();
        }

        return $bitReturn;
    }


    public function getStrTitle() {
        return $this->strTitle;
    }

    public function getStrComment() {
        return $this->strComment;
    }

    public function getStrUsername() {
        return $this->strUsername;
    }

    public function getIntDate() {
        if($this->intDate == null || $this->intDate == "") {
            $this->intDate = time();
        }

        return $this->intDate;
    }

    public function getStrAssignedPage() {
        return $this->strAssignedPage;
    }

    public function getStrAssignedSystemid() {
        return $this->strAssignedSystemid;
    }
    public function getStrAssignedLanguage() {
        return $this->strAssignedLanguage;
    }

    public function setStrTitle($strTitle) {
        $this->strTitle = $strTitle;
    }
    public function setStrComment($strComment) {
        $this->strComment = $strComment;
    }
    public function setStrUsername($strUsername) {
        $this->strUsername = $strUsername;
    }
    public function setIntDate($intDate) {
        $this->intDate = $intDate;
    }
    public function setStrAssignedPage($strAssignedPage) {
        $this->strAssignedPage = $strAssignedPage;
    }
    public function setStrAssignedSystemid($strAssignedSystemid) {
        $this->strAssignedSystemid = $strAssignedSystemid;
    }
    public function setStrAssignedLanguage($strAssignedLanguage) {
        $this->strAssignedLanguage = $strAssignedLanguage;
    }


}
