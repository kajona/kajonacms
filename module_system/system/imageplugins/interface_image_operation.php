<?php
/*"******************************************************************************************************
*   (c) 2013 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id:$	                                            *
********************************************************************************************************/

interface interface_image_operation {

    public function render(&$objResource);

    public function getCacheIdValues();
}