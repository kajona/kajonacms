<?php

interface interface_image_operation {

    public function render(&$objResource);

    public function getCacheIdValues();
}