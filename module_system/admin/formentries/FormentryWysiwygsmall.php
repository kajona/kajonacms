<?php
/*"******************************************************************************************************
*   (c) 2007-2016 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
********************************************************************************************************/

namespace Kajona\System\Admin\Formentries;



/**
 * @author sidler@mulchprod.de
 * @since 4.0
 * @package module_formgenerator
 *
 * @dperecated use the wysiwyg in combination with the @ wysiwygConfig annotation
 */
class FormentryWysiwygsmall extends FormentryWysiwyg {

    public function __construct($strFormName, $strSourceProperty, $objSourceObject)
    {
        parent::__construct($strFormName, $strSourceProperty, $objSourceObject);
        $this->strToolbarset = "minimalimage";
    }




}
