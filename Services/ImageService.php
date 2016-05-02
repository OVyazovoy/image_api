<?php
//namespace Services;

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
        if ($width == 0) {
            $width = $this->getWidth();
        }
        if ($height == 0) {
            $width = $this->getHeight();
        }
        imagecopyresampled($new_image, $this->image, $width, $height, 0, 0, $width, $height, $this->getWidth(), $this->getHeight());
        $this->image = $new_image;
    }

    /**
     * check dir exist if not create it
     * @param $dir
     * @return bool
     */
    public static function checkDirExist($dir)
    {
        if (!is_dir($dir)) {
            return mkdir($dir);
        }
        return true;
    }

    public static function saveOrigin($file)
    {
        $file_name = $file->getName();
        $file_type = $file->getType();

        if (self::trueType($file_type)) {
            $move_result = $file->moveTo(Constants::DEF_PATH . '/' . $file_name);
            if ($move_result) {
                return Constants::DEF_PATH . '/' . $file_name;
            }
        }

        return false;
    }

    protected function trueType($type)
    {
        return in_array($type, Constants::confirmTypes());
    }
}