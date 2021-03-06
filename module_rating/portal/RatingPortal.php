<?php
/*"******************************************************************************************************
*   (c) 2007-2016 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$                                            *
********************************************************************************************************/

namespace Kajona\Rating\Portal;

use Kajona\Rating\System\RatingRate;
use Kajona\System\Portal\PortalController;
use Kajona\System\Portal\PortalInterface;


/**
 * Rating Portal. Helper to render a rating bar.
 *
 * @package module_rating
 * @author sidler@mulchprod.de
 * @module rating
 * @moduleId _rating_modul_id_
 */
class RatingPortal extends PortalController implements PortalInterface
{


    /**
     * Builds the rating bar available for single entries.
     * Creates the needed js-links and image-tags as defined by the template whereas the template
     * defaults to rating.tpl.
     *
     * @param float $floatRating
     * @param int $intRatings
     * @param string $strSystemid
     * @param bool $bitRatingAllowed
     * @param bool $bitPermissions
     * @param string $strTemplate
     *
     * @return string
     */
    public function buildRatingBar($floatRating, $intRatings, $strSystemid, $bitRatingAllowed = true, $bitPermissions = true, $strTemplate = "rating.tpl")
    {
        $strIcons = "";
        $strRatingBarTitle = "";

        $intNumberOfIcons = RatingRate::$intMaxRatingValue;

        if ($bitRatingAllowed && $bitPermissions) {
            for ($intI = 1; $intI <= $intNumberOfIcons; $intI++) {
                $arrTemplate = array();
                $arrTemplate["rating_icon_number"] = $intI;

                $arrTemplate["rating_icon_onclick"] = "KAJONA.portal.rating.rate('".$strSystemid."', '".$intI.".0', ".$intNumberOfIcons."); return false;";
                $arrTemplate["rating_icon_title"] = $this->getLang("rating_rate1").$intI.$this->getLang("rating_rate2");

                $strIcons .= $this->objTemplate->fillTemplateFile($arrTemplate, "/module_rating/".$strTemplate, "rating_icon");
            }
        } else {
            if (!$bitRatingAllowed) {
                $strRatingBarTitle = $this->getLang("rating_voted");
            } else {
                $strRatingBarTitle = $this->getLang("commons_error_permissions");
            }
        }

        return $this->objTemplate->fillTemplateFile(
            array(
                "rating_icons"         => $strIcons, "rating_bar_title" => $strRatingBarTitle,
                "rating_rating"        => $floatRating, "rating_hits" => $intRatings,
                "rating_ratingPercent" => ($floatRating / $intNumberOfIcons * 100),
                "system_id"            => $strSystemid, 2
            ),
            "/module_rating/".$strTemplate,
            "rating_bar"
        );
    }


    /**
     * Saves a rating to a passed rating-file
     *
     * @return string the new rating for the passed file
     * @permissions view
     */
    protected function actionSaveRating()
    {

        //rating already existing?
        $objRating = RatingRate::getRating($this->getSystemid());
        if ($objRating == null) {
            $objRating = new RatingRate();
            $objRating->setStrRatingSystemid($this->getSystemid());
            $objRating->updateObjectToDb();
        }

        $strReturn = "<rating>";
        $objRating->saveRating($this->getParam("rating"));
        $objRating->updateObjectToDb();
        $strReturn .= round($objRating->getFloatRating(), 2);

        $strReturn .= "</rating>";
        return $strReturn;
    }

}
