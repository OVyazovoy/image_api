<?php
require_once 'config\main.php';
require_once 'Collections\Images.php';

use \Collections\Images;
use Phalcon\Mvc\Micro;
use Phalcon\Http\Response;

$app = new Micro();

//test actions
$app->get('/', function () use ($app) {
    echo "<h1>All work</h1>";
});

//add image
$app->post('/api/image', function () use ($app) {
    //upload file
    if ($this->request->hasFiles() == true) {
        //Print the real file names and their sizes
        foreach ($this->request->getUploadedFiles() as $file){ //TODO check dir exist
            $file->moveTo('files/'.$file->getName());//TODO check file type
        }
    }
    $request = $app->request->getJsonRawBody();
    //new image
    $image = new Images();
    $image->name = $request->name;
    $response = $image->save();

    return json_encode($response);
});

//get all images
$app->get('/api/images', function () use ($app) {
    $response = new Response();

    $images = Images::find();

    $response->setJsonContent(
        [
            'status' => 'OK',
            'messages' => $images,
        ]
    );
    return $response;
});

//
$app->delete('/api/image/{id}', function ($id) use ($app) {
    // set response
    $response = new Response();
    $errors = '';
    $images = Images::findById($id); //TODO need to check if id is mongo object id
    //if image with this id not found
    if (!$images) {
        $response->setStatusCode(409, "Conflict");
        $errors = 'Illegal request. No such image in database.';
        $response->setJsonContent(
            [
                'status' => 'ERROR',
                'messages' => $errors,
            ]
        );
    } else {
        if ($images->delete() == false) {
            foreach ($images->getMessages() as $message) {
                $errors[] = $message;
            }
            $response->setJsonContent(
                [
                    'status' => 'ERROR',
                    'messages' => $errors,
                ]
            );
        } else {
            $response->setJsonContent(
                [
                    'status' => 'OK',
                    'messages' => "The robot was deleted successfully!",
                ]
            );
        }
    }

    return $response;
});

$app->put('/api/image/{id}', function($id) use ($app) {
    $request = $app->request->getJsonRawBody();
    $response = new Response();
    $errors = '';
    $images = Images::findById($id); //TODO need to check if id is mongo object id
    if (!$images) {
        $response->setStatusCode(409, "Conflict");
        $errors = 'Illegal request. No such image in database.';
        $response->setJsonContent(
            [
                'status' => 'ERROR',
                'messages' => $errors,
            ]
        );
    } else {
        $images->name = $request->name;
        if ($images->update() == false) {
            foreach ($images->getMessages() as $message) {
                $errors[] = $message;
            }
            $response->setJsonContent(
                [
                    'status' => 'ERROR',
                    'messages' => $errors,
                ]
            );
        } else {
            $response->setJsonContent(
                [
                    'status' => 'OK',
                    'messages' => "The robot was update successfully!",
                ]
            );
        }
    }
    return $response;
});

$app->handle();