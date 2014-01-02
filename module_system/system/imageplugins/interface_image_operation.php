<?php
/*"******************************************************************************************************
*   (c) 2013-2014 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$	                                            *
********************************************************************************************************/

/**
 * Interface interface_image_operation
 * Each image-operation plugin has to implement this interface
 *
 * @since 4.3
 */
interface interface_image_operation {

    /**
     * Implement the rendering of your operation in this method
     *
     * @param resource &$objResource
     *
     * @return mixed
     */
    public function render(&$objResource);

    /**
     * Return a characteristic of your plugin in order to include it into
     * the calculated cache checksum
     *
     * @return mixed
     */
    public function getCacheIdValues();
}