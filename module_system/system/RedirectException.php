<?php
/*"******************************************************************************************************
*   (c) 2007-2016 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
********************************************************************************************************/

namespace Kajona\System\System;

/**
 * Exception to directly redirect the user to another url
 *
 * @author christoph.kappestein@artemeon.de
 * @since 6.1
 */
class RedirectException extends Exception
{
    /**
     * @var string
     */
    protected $strModule;

    /**
     * @var string
     */
    protected $strAction;

    /**
     * @var array
     */
    protected $arrParams;

    /**
     * @param string $strModule
     * @param string $strAction
     * @param array $arrParams
     */
    public function __construct($strModule, $strAction, array $arrParams)
    {
        parent::__construct(null, null);

        $this->strModule = $strModule;
        $this->strAction = $strAction;
        $this->arrParams = $arrParams;
    }

    /**
     * @return string
     */
    public function getHref()
    {
        return Link::getLinkAdminHref($this->strModule, $this->strAction, $this->arrParams);
    }
}
