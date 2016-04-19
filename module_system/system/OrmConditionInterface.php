<?php
/*"******************************************************************************************************
*   (c) 2007-2016 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*   $Id$                                        *
********************************************************************************************************/

namespace Kajona\System\System;

/**
 * Represents an arbitrary condition. Has methods to return a prepared SQL condition and the fitting
 * parameters
 *
 * @package Kajona\System\System
 * @author christoph.kappestein@gmail.com
 * @since 5.0
 */
interface OrmConditionInterface
{
    /**
     * The where SQL statment MUST NOT contain a leading AND
     *
     * @return string
     */
    public function getStrWhere();

    /**
     * @return array
     */
    public function getArrParams();
}
