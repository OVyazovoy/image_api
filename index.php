<?php
require_once 'config\main.php';
require_once 'Collections\Images.php';

use \Collections\Images;
use Phalcon\Mvc\Micro;

$app = new Micro();

//test actions
$app->get('/', function () use ($app) {
    echo "<h1>All work</h1>";
});

//add image
$app->post('/api/image', function () use ($app) {

});

//all images
$app->get('api/images', function () use ($app) {
    $images = Images::find();
    return $images;
});



$app->handle();