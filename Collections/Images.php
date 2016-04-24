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
    public function getSource()
    {
        return "images";
    }
}