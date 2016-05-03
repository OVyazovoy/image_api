<?php
require_once 'config\main.php';
require_once 'components\Constants.php';
require_once 'Collections\Model.php';
require_once 'Collections\Images.php';
require_once 'Collections\User.php';
require_once 'Services\ImageService.php';

use \Collections\Images;
use Phalcon\Mvc\Micro;
use Phalcon\Http\Response;

$app = new Micro();

//test actions
$app->get('/', function () use ($app) {
    echo "<h1>All work</h1>";
});

/**
 * add one image
 */
$app->post('/api/v1/image', function () use ($app) {
    $request = new \Phalcon\Http\Request();
    $response = new Response();

    if (!$_GET['access_token']) {
        $response->setStatusCode(400, "Wrong Data");
        $response->setJsonContent(
            [
                'status' => 'ERROR',
                'messages' => 'access token',
            ]
        );
        return $response;
    }
    if (!\Collections\User::findByToken($_GET['access_token'])) {
        $response->setStatusCode(400, "Wrong Data");
        $response->setJsonContent(
            [
                'status' => 'ERROR',
                'messages' => 'No such user',
            ]
        );
        return $response;
    }

    $img_width = $request->getPost("width");
    $img_height = $request->getPost("height");
    if (!isset($img_width) || !isset($img_height) || $img_width < 0 || $img_height < 0) {
        $response->setStatusCode(400, "Wrong Data");
        return $response;
    }

    //upload file
    if ($this->request->hasFiles() == true) {
        //Print the real file names and their sizes
        foreach ($this->request->getUploadedFiles() as $file) {
            $file_name = $file->getName();
            $file_path = ImageService::saveOrigin($file);
            if (!$file_path) {
                $response->setStatusCode(400, "Wrong Data");
                $response->setJsonContent(
                    [
                        'status' => 'ERROR',
                        'messages' => 'Can`t save origin files',
                    ]
                );
                return $response;
            }
        }
    } else {
        $response->setStatusCode(400, "Wrong Data");
        $response->setJsonContent(
            [
                'status' => 'ERROR',
                'messages' => 'No files in request',
            ]
        );
        return $response;
    }

    $image_service = new ImageService($file_path);
    $image_service->resize($img_width, $img_height);
    $resize_image_path = Constants::DEF_PATH . '/' . $img_width . 'x' . $img_height . $file_name;
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

/**
 * get all images
 */
$app->get('/api/v1/images', function () use ($app) {

    $response = new Response();
    if (!$_GET['access_token']) {
        $response->setStatusCode(400, "Wrong Data");
        $response->setJsonContent(
            [
                'status' => 'ERROR',
                'messages' => 'access token',
            ]
        );
        return $response;
    }
    if (!\Collections\User::findByToken($_GET['access_token'])) {
        $response->setStatusCode(400, "Wrong Data");
        $response->setJsonContent(
            [
                'status' => 'ERROR',
                'messages' => 'No such user',
            ]
        );
        return $response;
    }
    $images = Images::find();

    $response->setJsonContent(
        [
            'status' => 'OK',
            'messages' => $images,
        ]
    );
    return $response;
});

/**
 * delete one image
 */
$app->delete('/api/v1/image/{id}', function ($id) use ($app) {

    // set response
    $response = new Response();
    if (!$_GET['access_token']) {
        $response->setStatusCode(400, "Wrong Data");
        $response->setJsonContent(
            [
                'status' => 'ERROR',
                'messages' => 'access token',
            ]
        );
        return $response;
    }
    if (!\Collections\User::findByToken($_GET['access_token'])) {
        $response->setStatusCode(400, "Wrong Data");
        $response->setJsonContent(
            [
                'status' => 'ERROR',
                'messages' => 'No such user',
            ]
        );
        return $response;
    }
    $msg = '';
    $status = 'ERROR';
    $image = ImageService::returnImage($id);

    //TODO need to check if id is mongo object id
    //if image with this id not found
    if (!$image) {
        $response->setStatusCode(409, "Conflict");
        $msg = 'Illegal request. No such image in database.';
    } else {
        if ($image->delete() == false) {
            foreach ($image->getMessages() as $message) {
                $msg[] = $message;
            }
        } else {
            $status = 'OK';
            $msg = 'The image was deleted successfully!';
        }
    }

    $response->setJsonContent(
        [
            'status' => $status,
            'messages' => $msg,
        ]
    );

    return $response;
});

/**
 * update one image
 */
$app->put('/api/v1/image/{id}', function ($id) use ($app) {
    $request = $app->request->getJsonRawBody();
    $response = new Response();
    if (!$_GET['access_token']) {
        $response->setStatusCode(400, "Wrong Data");
        $response->setJsonContent(
            [
                'status' => 'ERROR',
                'messages' => 'access token',
            ]
        );
        return $response;
    }
    if (!\Collections\User::findByToken($_GET['access_token'])) {
        $response->setStatusCode(400, "Wrong Data");
        $response->setJsonContent(
            [
                'status' => 'ERROR',
                'messages' => 'No such user',
            ]
        );
        return $response;
    }
    $msg = '';
    $status = 'ERROR';
    $image = ImageService::returnImage($id);
    if (!$image) {
        $response->setStatusCode(409, "Conflict");
        $msg = 'Illegal request. No such image in database.';
    } else {
        $image->name = $request->name;
        if ($image->update() == false) {
            foreach ($image->getMessages() as $message) {
                $msg[] = $message;
            }
        } else {
            $status = "OK";
            $msg = "The image was update successfully!";
        }
    }

    $response->setJsonContent(
        [
            'status' => $status,
            'messages' => $msg,
        ]
    );
    return $response;
});

$app->get('/api/v1/get-token', function () use ($app) {
    $response = new Response();
    $user = new \Collections\User();
    if ($user->save()) {
        return json_encode([
            'token' => $user->token,
            'id' => $user->_id,
        ]);
    }

    $response->setJsonContent(
        [
            'status' => "ERROR",
            'messages' => "Can`t add new user.",
        ]
    );
    return $response;
});


$app->get('/api/v1/user/{id}', function ($id) use ($app) {
    $response = new Response();
    $user = \Collections\User::returnUser($id);
    $status = "ERROR";
    $msg = "Can`t find user with such id.";

    if ($user) {
        $status = "OK";
        $msg = [
            'token' => $user->token,
            'created_at' => $user->created_at
        ];
    }

    $response->setJsonContent(
        [
            'status' => $status,
            'messages' => $msg,
        ]
    );
    return $response;
});

//TODO multi delete
//TODO multi update
//TODO multi add

$app->handle();