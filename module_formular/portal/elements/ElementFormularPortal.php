<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2015 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$                                   *
********************************************************************************************************/

namespace Kajona\Formular\Portal\Elements;

use class_classloader;
use class_exception;
use class_resourceloader;
use Kajona\Pages\Portal\ElementPortal;
use Kajona\Pages\Portal\PortalElementInterface;


/**
 * Portal Element to load the formular specified in the admin
 *
 * @author sidler@mulchprod.de
 *
 * @targetTable element_formular.content_id
 */
class ElementFormularPortal extends ElementPortal implements PortalElementInterface {

    /**
     * Loads the navigation-class and passes control
     *
     * @throws class_exception
     * @return string
     */
    public function loadData() {


        $strPath = class_resourceloader::getInstance()->getPathForFile("/portal/forms/" . $this->arrElementData["formular_class"]);

        if($strPath === false) {
            throw new class_exception("failed to load form-class " . $this->arrElementData["formular_class"], class_exception::$level_ERROR);
        }

        $objForm = class_classloader::getInstance()->getInstanceFromFilename($strPath, null, null, array($this->arrElementData));
        $strReturn = $objForm->action();

        return $strReturn;
    }

}
