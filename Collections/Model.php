<?php
/**
 * Created by PhpStorm.
 * User: Îëåã
 * Date: 03.05.2016
 * Time: 15:40
 */

namespace Collections;

use Phalcon\Mvc\Collection;
abstract class  Model extends Collection
{
    protected function beforeCreate(){
        // Set the creation date
        $this->created_at = date('Y-m-d H:i:s');
    }
    protected function beforeUpdate(){
        // Set the creation date
        $this->created_at = date('Y-m-d H:i:s');
    }


}