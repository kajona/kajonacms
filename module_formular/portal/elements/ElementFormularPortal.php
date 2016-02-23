<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2015 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$                                   *
********************************************************************************************************/

namespace Kajona\Formular\Portal\Elements;

use Kajona\Pages\Portal\ElementPortal;
use Kajona\Pages\Portal\PortalElementInterface;
use Kajona\System\System\Classloader;
use Kajona\System\System\Exception;
use Kajona\System\System\Resourceloader;


/**
 * Portal Element to load the formular specified in the admin
 *
 * @author sidler@mulchprod.de
 *
 * @targetTable element_formular.content_id
 */
class ElementFormularPortal extends ElementPortal implements PortalElementInterface
{

    /**
     * Loads the navigation-class and passes control
     *
     * @throws Exception
     * @return string
     */
    public function loadData()
    {


        $strPath = Resourceloader::getInstance()->getPathForFile("/portal/forms/".$this->arrElementData["formular_class"]);

        if ($strPath === false) {
            throw new Exception("failed to load form-class ".$this->arrElementData["formular_class"], Exception::$level_ERROR);
        }

        $objForm = Classloader::getInstance()->getInstanceFromFilename($strPath, null, null, array($this->arrElementData));
        $strReturn = $objForm->action();

        return $strReturn;
    }

}
