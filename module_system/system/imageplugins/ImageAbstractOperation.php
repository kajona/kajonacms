<?php
/*"******************************************************************************************************
*   (c) 2013-2015 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$	                                            *
********************************************************************************************************/

namespace Kajona\System\System\Imageplugins;


abstract class ImageAbstractOperation implements ImageOperationInterface {

    /**
     * @param int $intWidth
     * @param int $intHeight
     *
     * @return resource
     */
    protected static function createImageResource($intWidth, $intHeight) {
        $objResource = imagecreatetruecolor($intWidth, $intHeight);
        imagealphablending($objResource, false); //crashes font-rendering, so set true before rendering fonts
        imagesavealpha($objResource, true);
        return $objResource;
    }

    /**
     * @param resource $objResource
     * @param array $arrColor
     * @return int
     */
    protected function allocateColor($objResource, $arrColor)
    {
        $intColor = null;

        if (sizeof($arrColor) == 3) {
            $intColor = imagecolorallocate($objResource, $arrColor[0], $arrColor[1], $arrColor[2]);
        } elseif (sizeof($arrColor) == 4) {
            $intColor = imagecolorallocatealpha($objResource, $arrColor[0], $arrColor[1], $arrColor[2], $arrColor[3]);
        }

        return $intColor;
    }
}