<?php

namespace Collections;


class User extends Model
{

    public function getSource()
    {
        return "users";
    }

    public function beforeCreate()
    {
        $this->token = $this->getToken();

        if($this->token)
            return parent::beforeCreate();

        return false;
    }

    public function getToken()
    {
        $token = md5($this->generateRandomString()) . md5($this->generateRandomString());
        $users = $this::findByToken($token);

        if($users)
            $this->getToken();

        return $token;
    }

    private function generateRandomString($length = 10)
    {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        return $randomString;
    }


    public static function returnUser($id)
    {
        try {
            $result = self::findById($id);
        } catch (MongoException $ex) {
            $result = false;
        }
        return $result;
    }

    public static function findByToken($token)
    {
        return self::findFirst(
            [
                [
                    'token' => $token
                ]
            ]
        );
    }
}