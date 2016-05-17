<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2014 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$                                      *
********************************************************************************************************/

namespace Kajona\Pages\Admin\Elements;

use Kajona\Pages\Admin\AdminElementInterface;
use Kajona\Pages\Admin\ElementAdmin;


/**
 * Admin class to handle a simple image src
 *
 * @package module_pages
 * @author sidler@mulchprod.de
 *
 * @targetTable element_universal.content_id
 */
class ElementImagesrcAdmin extends ElementAdmin implements AdminElementInterface
{


    /**
     * @var string
     * @tableColumn element_universal.char1
     * @tableColumnDatatype char254
     *
     * @fieldType Kajona\System\Admin\Formentries\FormentryImage
     * @fieldLabel commons_image
     *
     * @elementContentTitle
     *
     * @addSearchIndex
     * @templateExport
     */
    private $strImage = "";


    /**
     * @var string
     * @tableColumn element_universal.char2
     *
     * @fieldType Kajona\Pages\Admin\Formentries\FormentryTemplate
     * @fieldTemplateDir /element_imagesrc
     * @fieldMandatory
     * @fieldLabel template
     *
     * @addSearchIndex
     */
    private $strTemplate = "";

    /**
     * @inheritDoc
     */
    public function generateDummyContent()
    {
        parent::generateDummyContent();
        $this->setStrImage("/files/images/samples/img_3000.jpg");
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
     * @return string
     */
    public function getStrImage()
    {
        return $this->strImage;
    }

    /**
     * @param string $strImage
     */
    public function setStrImage($strImage)
    {
        $this->strImage = $strImage;
    }


}
