<?php
/*"******************************************************************************************************
*   (c) 2007-2016 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$                                   *
********************************************************************************************************/

namespace Kajona\System\System\Validators;

use Kajona\System\System\Carrier;
use Kajona\System\System\ValidatorExtendedInterface;


/**
 * A simple validator to validate a positive integer.
 *
 * @author sidler@mulchprod.de
 * @since 5.1
 */
class PosintnotzeroValidator extends PosintValidator implements ValidatorExtendedInterface {

    /**
     * Validates the passed chunk of data.
     * In most cases, this'll be a string-object.
     *
     * @param string $objValue
     * @return bool
     */
    public function validate($objValue) {
        return parent::validate($objValue) && $objValue > 0;
    }

}
