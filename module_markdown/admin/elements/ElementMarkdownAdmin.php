<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2016 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$                                   *
********************************************************************************************************/

namespace Kajona\Markdown\Admin\Elements;

use Kajona\Pages\Admin\AdminElementInterface;
use Kajona\Pages\Admin\ElementAdmin;
use Kajona\System\System\Exception;
use Kajona\System\System\Remoteloader;


/**
 * Class to handle the admin-stuff of the markdown-element
 *
 * @author sidler@mulchprod.de
 *
 * @targetTable element_universal.content_id
 */
class ElementMarkdownAdmin extends ElementAdmin implements AdminElementInterface
{

    /**
     * @var string
     * @tableColumn element_universal.char1
     *
     * @fieldType Kajona\Pages\Admin\Formentries\FormentryTemplate
     * @fieldLabel template
     *
     * @fieldTemplateDir /module_markdown
     */
    private $strTemplate = "";


    /**
     * @var string
     * @tableColumn element_universal.char2
     *
     * @fieldType Kajona\System\Admin\Formentries\FormentryText
     *
     */
    private $strSourceUrl = "";

    /**
     * Dummy property to have the remote file indexed, too
     *
     * @var string
     * @addSearchIndex
     */
    private $strContent = "";


    /**
     * @return string
     */
    public function getStrSourceUrl()
    {
        return $this->strSourceUrl;
    }

    /**
     * @param string $strSourceUrl
     */
    public function setStrSourceUrl($strSourceUrl)
    {
        $this->strSourceUrl = $strSourceUrl;
    }

    /**
     * @return string
     */
    public function getStrTemplate()
    {
        return $this->strTemplate;
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
    public function getStrContent()
    {
        if(trim($this->getStrSourceUrl()) == "") {
            return "";
        }

        $arrUrl = parse_url($this->getStrSourceUrl());

        $objLoader = new Remoteloader();
        $objLoader->setStrProtocolHeader($arrUrl["scheme"]."://");
        $objLoader->setStrHost($arrUrl["host"]);
        $objLoader->setStrQueryParams($arrUrl["path"]);
        $objLoader->setIntPort(null);

        try {
            $strReturn = $objLoader->getRemoteContent();
            if($strReturn) {
                return $strReturn;
            }
        } catch(\Exception $objEx) {
        }
        
        return "";
    }

    /**
     * @param string $strContent
     */
    public function setStrContent($strContent)
    {
        $this->strContent = $strContent;
    }

    
    

}
