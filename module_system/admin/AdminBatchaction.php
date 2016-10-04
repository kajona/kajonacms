<?php
/*"******************************************************************************************************
*   (c) 2007-2016 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$	                                            *
********************************************************************************************************/

namespace Kajona\System\Admin;



/**
 * A massaction is a single, descriptive object to be rendered by the admin-toolkit.
 * Each action may be called iterative for a set of systemid.
 * The action is triggered by an ajax-request, the target-url is specified by the respective property.
 * The target-url should provide a %systemid% element, being replaced before the triggering of the request.
 *
 *
 * @author sidler@mulchprod.de
 * @since 4.0
 * @package module_system
 */
class AdminBatchaction {

    private $strIcon;
    private $strTitle;
    private $strTargetUrl;
    private $bitRenderInfo;
    private $strOnClickHandler = "";

    function __construct($strIcon, $strTargetUrl, $strTitle, $bitRenderInfo = false) {
        $this->strIcon = $strIcon;
        $this->strTargetUrl = $strTargetUrl;
        $this->strTitle = $strTitle;
        $this->bitRenderInfo = $bitRenderInfo;
        $this->updateOnClick();
    }

    private function updateOnClick()
    {
        $this->strOnClickHandler = "require('lists').triggerAction('{$this->strTitle}', '{$this->strTargetUrl}', ".($this->getBitRenderInfo() ? "1" : "0").");";
    }

    public function setStrIcon($strIcon) {
        $this->strIcon = $strIcon;
    }

    public function getStrIcon() {
        return $this->strIcon;
    }

    public function setStrTargetUrl($strTargetUrl) {
        $this->updateOnClick();
        $this->strTargetUrl = $strTargetUrl;
    }

    public function getStrTargetUrl() {
        return $this->strTargetUrl;
    }

    public function setStrTitle($strTitle) {
        $this->updateOnClick();
        $this->strTitle = $strTitle;
    }

    public function getStrTitle() {
        return $this->strTitle;
    }

    public function setBitRenderInfo($bitRenderInfo)
    {
        $this->updateOnClick();
        $this->bitRenderInfo = (bool) $bitRenderInfo;
    }

    public function getBitRenderInfo()
    {
        return $this->bitRenderInfo;
    }

    /**
     * @return mixed
     */
    public function getStrOnClickHandler()
    {
        return $this->strOnClickHandler;
    }

    /**
     * @param mixed $strOnClickHandler
     */
    public function setStrOnClickHandler($strOnClickHandler)
    {
        $this->strOnClickHandler = $strOnClickHandler;
    }


}
