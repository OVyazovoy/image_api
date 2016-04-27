<?php

/**
 * Created by PhpStorm.
 * User: Îëåã
 * Date: 27.04.2016
 * Time: 23:07
 */
class ImageService
{
    protected $image;

    function __construct($image)
    {
        $this->image = $image;
    }

    //function for get real width of image
    protected function getWidth() {
        return imagesx($this->image);
    }
    //function for get real height of image
    protected function getHeight() {
        return imagesy($this->image);
    }

    public function resize($width,$height) {
        $new_image = imagecreatetruecolor($width, $height);//create empty image
        imagecopyresampled($new_image, $this->image, 0, 0, 0, 0, $width, $height, $this->getWidth(), $this->getHeight());
        $this->image = $new_image;
    }

}