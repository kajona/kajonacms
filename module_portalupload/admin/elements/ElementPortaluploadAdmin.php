<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2016 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
********************************************************************************************************/

namespace Kajona\Portalupload\Admin\Elements;

use Kajona\Mediamanager\System\MediamanagerRepo;
use Kajona\Pages\Admin\AdminElementInterface;
use Kajona\Pages\Admin\ElementAdmin;


/**
 * Class to handle the admin-stuff of the portalupload-element
 *
 * @author sidler@mulchprod.de
 *
 * @targetTable element_universal.content_id
 */
class ElementPortaluploadAdmin extends ElementAdmin implements AdminElementInterface {

    /**
     * @var string
     * @tableColumn element_universal.char1
     *
     * @fieldType Kajona\Pages\Admin\Formentries\FormentryTemplate
     * @fieldLabel template
     *
     * @fieldTemplateDir /module_portalupload
     */
    private $strChar1;

    /**
     * @var string
     * @tableColumn element_universal.char2
     *
     * @fieldType dropdown
     * @fieldLabel portalupload_download
     * @fieldMandatory
     */
    private $strChar2;

    public function getAdminForm() {

        $arrDlArchives = MediamanagerRepo::getObjectList();
        $arrDlDD = array();
        if(count($arrDlArchives) > 0) {
            foreach($arrDlArchives as $objOneArchive) {
                $arrDlDD[$objOneArchive->getSystemid()] = $objOneArchive->getStrDisplayName();
            }
        }

        $objForm = parent::getAdminForm();
        $objForm->getField("char2")->setArrKeyValues($arrDlDD);
        return $objForm;
    }

    /**
     * @param string $strChar2
     */
    public function setStrChar2($strChar2) {
        $this->strChar2 = $strChar2;
    }

    /**
     * @return string
     */
    public function getStrChar2() {
        return $this->strChar2;
    }

    /**
     * @param string $strChar1
     */
    public function setStrChar1($strChar1) {
        $this->strChar1 = $strChar1;
    }

    /**
     * @return string
     */
    public function getStrChar1() {
        return $this->strChar1;
    }




}
