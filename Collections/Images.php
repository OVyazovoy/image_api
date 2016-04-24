<?php
namespace Collections;
/**
 * Created by PhpStorm.
 * User: Юыху
 * Date: 24.04.2016
 * Time: 16:55
 */

use Phalcon\Mvc\Collection;
class Images extends Collection
{
    public function beforeCreate()
    {
        // Set the creation date
        $this->created_at = date('Y-m-d H:i:s');
    }
    public function beforeUpdate()
    {
        // Set the modification date
        $this->modified_in = date('Y-m-d H:i:s');
    }

    public function getSource()
    {
        return "images";
    }
}