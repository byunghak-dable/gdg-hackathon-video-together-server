<?php

namespace core\mvc;

use core\mvc\View;

class Controller
{
    // protected $view;
    protected $model;

    // protected function createView($view, $data = [])
    // {
    //     $this->view = new View($view, $data);
    //     return $this->view;
    // }

    protected function createModel($model)
    {
        require "app/models/{$model}.php";
        $model = "app\models\\{$model}";
        return new $model;
    }

    protected function getInputByJson()
    {
        $jsonString = file_get_contents('php://input');
        return json_decode($jsonString, true);
    }

    protected function getMethodResponseCode($result, $successCode, $noContentsCode)
    {
        if ($result) {
            $httpResponseCode = http_response_code($successCode);
        } else {
            $httpResponseCode = http_response_code($noContentsCode);
        }

        return $httpResponseCode;
    }

    public function passResponseCode($responseCode)
    {
        return http_response_code($responseCode);
    }
}
