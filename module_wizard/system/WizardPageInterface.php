<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2016 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
********************************************************************************************************/

namespace Kajona\Statustransition\System;

use Kajona\System\Admin\AdminFormgenerator;
use Kajona\System\System\Root;

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
     * @return Root
     */
    public function newObjectInstance();

    /**
     * @param Root $objModel
     * @return \Kajona\System\Admin\AdminFormgenerator
     */
    public function getAdminForm(Root $objModel);

    /**
     * Called on each page if the user clicks the continue button and the form validation of the form returned by
     * getAdminForm was successful. At this stage the model contains all data from the submitted form but was not
     * persisted in the session
     *
     * @param Root $objModel
     * @param AdminFormgenerator $objForm
     * @return mixed
     */
    public function onSave(Root $objModel, AdminFormgenerator $objForm);

    /**
     * This method is called for each page after the last step of the wizard was successful completed. It contains the
     * model for the current step and an array containing all objects from the wizard. The key is the class name of the
     * page
     * 
     * @param Root $objModel
     * @param Root[] $arrObjects
     * @return void
     */
    public function onPersist(Root $objModel, array $arrObjects);
}
