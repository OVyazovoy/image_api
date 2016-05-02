<?php
require_once 'config\main.php';
require_once 'components\Constants.php';
require_once 'Collections\Images.php';
require_once 'Services\ImageService.php';

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
    $request = new \Phalcon\Http\Request();
    $response = new Response();

    $img_width = $request->getPost("width");
    $img_height = $request->getPost("height");
    if (!isset($img_width) || !isset($img_height) || $img_width < 0 || $img_height < 0) {
        $response->setStatusCode(400, "Wrong Data");
        return $response;
    }

    //upload file
    if ($this->request->hasFiles() == true) {
        //Print the real file names and their sizes
        if(ImageService::checkDirExist(Constants::DEF_PATH)){
            foreach ($this->request->getUploadedFiles() as $file) {
                $file_name = $file->getName();
                $file_path = ImageService::saveOrigin($file);
                if(!$file_path){
                    $response->setStatusCode(400, "Wrong Data");
                    return $response;
                }
            }
        }
    } else {
        $response->setStatusCode(400, "Wrong Data");
        return $response;
    }

    $image_service = new ImageService($file_path);
    $image_service->resize($img_width, $img_height);
    $resize_image_path = Constants::DEF_PATH . '/'. $img_width . 'x' . $img_height . $file_name;
    $resize_result = $image_service->save($resize_image_path);
    if ($resize_result) {
        //new image
        $image = new Images();
        $image->origin_path = $file_path;
        $image->width = $img_width;
        $image->height = $img_height;
        $image->resize_image_path = $resize_image_path;
        $save_image_db = $image->save();
        if ($save_image_db) {
            //set response
            $res_status = 'OK';
            $res_message = $image;
        } else {
            $response->setStatusCode(400, "Bad Request");
            $res_status = 'Error';
            $res_message = 'Can`t save to database';
        }
    } else {
        $response->setStatusCode(400, "Bad Request");
        $res_status = 'Error';
        $res_message = 'Can`t resize image';
    }
    $response->setJsonContent(
        [
            'status' => $res_status,
            'messages' => $res_message,
        ]
    );
    return $response;
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

$app->put('/api/image/{id}', function ($id) use ($app) {
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