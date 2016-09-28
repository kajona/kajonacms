<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2016 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$                                   *
********************************************************************************************************/

namespace Kajona\Statustransition\Admin;

use Kajona\News\System\NewsCategory;
use Kajona\System\Admin\AdminFormgenerator;
use Kajona\System\Admin\Formentries\FormentryDatetime;
use Kajona\System\Admin\LanguagesAdmin;
use Kajona\System\System\Carrier;
use Kajona\System\System\LanguagesLanguage;
use Kajona\System\System\LanguagesLanguageset;
use Kajona\System\System\Link;
use Kajona\System\System\SystemModule;
use Kajona\System\System\SystemSetting;

/**
 * Formgenerator for a statustransition flow entry
 *
 * @package module_statustransition
 * @author christoph.kappestein@gmail.com
 * @since 5.1
 */
class StatustransitionFormgenerator extends AdminFormgenerator
{
    /**
     * @inheritDoc
     */
    public function generateFieldsFromObject()
    {
        parent::generateFieldsFromObject();


    }
}
