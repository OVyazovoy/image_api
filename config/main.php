<?php
// Simple database connection to localhost
Phalcon\DI::reset();
$di = new Phalcon\DI();
$di->set('mongo', function() {
    $mongo = new Mongo();
    return $mongo->selectDb("images");
}, true);
$di->set('collectionManager', function(){
    return new Phalcon\Mvc\Collection\Manager();
}, true);