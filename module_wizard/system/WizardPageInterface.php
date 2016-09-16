<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2016 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
********************************************************************************************************/

namespace Kajona\Statustransition\System;

/**
 * WizardPage
 *
 * @author christoph.kappestein@artemeon.de
 * @module wizard
 */
interface WizardPageInterface
{
    /**
     * @return string
     */
    public function getTitle();

    /**
     * @return int
     */
    public function getButtonConfig();

    /**
     * @param object $objInstance
     * @param array $arrObjects
     * @return void
     */
    public function persistObject($objInstance, array $arrObjects);

    /**
     * @return object
     */
    public function newObjectInstance();
}
