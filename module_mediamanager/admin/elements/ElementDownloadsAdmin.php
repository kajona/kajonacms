<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2015 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$                                  *
********************************************************************************************************/

namespace Kajona\Mediamanager\Admin\Elements;

use Kajona\Mediamanager\System\MediamanagerRepo;
use Kajona\Pages\Admin\AdminElementInterface;
use Kajona\Pages\Admin\ElementAdmin;


/**
 * Class representing the admin-part of the downloads element
 *
 * @package module_mediamanager
 * @author sidler@mulchprod.de
 * @targetTable element_downloads.content_id
 */
class ElementDownloadsAdmin extends ElementAdmin implements AdminElementInterface
{

    /**
     * @var string
     * @tableColumn element_downloads.download_id
     * @tableColumnDatatype char20
     * @fieldType dropdown
     * @fieldLabel download_id
     */
    private $strRepo;

    /**
     * @var string
     * @tableColumn element_downloads.download_template
     * @tableColumnDatatype char254
     * @fieldType template
     * @fieldLabel template
     * @fieldTemplateDir /module_mediamanager
     */
    private $strTemplate;

    /**
     * @var int
     * @tableColumn element_downloads.download_amount
     * @tableColumnDatatype int
     * @fieldType text
     * @fieldLabel download_amount
     */
    private $intAmount;


    public function getAdminForm()
    {
        //Load all archives
        $arrObjArchs = MediamanagerRepo::getObjectList();
        $arrArchives = array();
        foreach ($arrObjArchs as $objOneArchive) {
            $arrArchives[$objOneArchive->getSystemid()] = $objOneArchive->getStrDisplayName();
        }

        $objForm = parent::getAdminForm();
        $objForm->getField("repo")->setArrKeyValues($arrArchives);
        return $objForm;
    }

    /**
     * @param string $strTemplate
     */
    public function setStrTemplate($strTemplate)
    {
        $this->strTemplate = $strTemplate;
    }

    /**
     * @return string
     */
    public function getStrTemplate()
    {
        return $this->strTemplate;
    }

    /**
     * @param string $strRepo
     */
    public function setStrRepo($strRepo)
    {
        $this->strRepo = $strRepo;
    }

    /**
     * @return string
     */
    public function getStrRepo()
    {
        return $this->strRepo;
    }

    /**
     * @param int $intAmount
     */
    public function setIntAmount($intAmount)
    {
        $this->intAmount = $intAmount;
    }

    /**
     * @return int
     */
    public function getIntAmount()
    {
        return $this->intAmount;
    }


}
