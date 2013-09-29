<?php

abstract class class_image_abstract_operation implements interface_image_operation {

    /**
     * @param $objResource
     * @param $arrColor
     * @return int
     */
    protected function allocateColor($objResource, $arrColor)
    {
        $intColor = null;

        if (sizeof($arrColor) == 3) {
            $intColor = imagecolorallocate($objResource, $arrColor[0], $arrColor[1], $arrColor[2]);
        } else if (sizeof($arrColor) == 4) {
            $intColor = imagecolorallocatealpha($objResource, $arrColor[0], $arrColor[1], $arrColor[2], $arrColor[3]);
        }

        return $intColor;
    }
}