<?php

/**
 * Created by PhpStorm.
 * User: Юыху
 * Date: 27.04.2016
 * Time: 23:07
 */
class ImageService
{
    protected $image;
    protected $image_type;

    function __construct($image)
    {
        $this->load($image);
    }

    protected function load($filename)
    {
        $image_info = getimagesize($filename);
        $this->image_type = $image_info[2];
        if ($this->image_type == IMAGETYPE_JPEG) {
            $this->image = imagecreatefromjpeg($filename);
        } elseif ($this->image_type == IMAGETYPE_GIF) {
            $this->image = imagecreatefromgif($filename);
        } elseif ($this->image_type == IMAGETYPE_PNG) {
            $this->image = imagecreatefrompng($filename);
        }
    }

    public function save($filename, $image_type = IMAGETYPE_JPEG, $compression = 75, $permissions = null)
    {
        $result = false;
        if ($image_type == IMAGETYPE_JPEG) {
            $result = imagejpeg($this->image, $filename, $compression);
        } elseif ($image_type == IMAGETYPE_GIF) {
            $result = imagegif($this->image, $filename);
        } elseif ($image_type == IMAGETYPE_PNG) {
            $result = imagepng($this->image, $filename);
        }
        if ($permissions != null) {
            $result = chmod($filename, $permissions);
        }
        return $result;
    }

    //function for get real width of image
    protected function getWidth()
    {
        return imagesx($this->image);
    }

    //function for get real height of image
    protected function getHeight()
    {
        return imagesy($this->image);
    }

    public function resize($width, $height)
    {
        $new_image = imagecreatetruecolor($width, $height);//create empty image
        if($width == 0){
            $width = $this->getWidth();
        }
        if($height == 0){
            $width = $this->getHeight();
        }
        imagecopyresampled($new_image, $this->image, 0, 0, 0, 0, $width, $height, $this->getWidth(), $this->getHeight());
        var_dump($this->getWidth());die;
        $this->file = $new_image;
    }

}