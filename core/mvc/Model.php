<?php

namespace core\mvc;

class Model
{
    // controller에서 반환할 데이터
    protected $responseData = [];


    protected function getResponseData()
    {
        return $this->responseData;
    }

    protected function storeImage($encodedImage, $description)
    {
        $serverUrl = 'https://couple-space.tk/';
        $decodedString = base64_decode($encodedImage);
        $newFileName = str_replace(".", "", uniqid("{$description}:", true));
        $fileName = $newFileName . '.' . 'jpg';
        $path = 'src/images/' . $fileName;
        file_put_contents($path, $decodedString);
        return $serverUrl . $path;
    }

    protected function storeWebImage($encodedImage, $description)
    {
        $serverUrl = 'https://couple-space.tk/';
        $explodedImage = explode(',', $encodedImage);
        $decodedString = base64_decode($explodedImage[1]);
        $newFileName = str_replace(".", "", uniqid("{$description}:", true));
        $fileName = $newFileName . '.' . 'jpg';
        $path = 'src/images/' . $fileName;
        file_put_contents($path, $decodedString);
        return $serverUrl . $path;
    }

    protected function formCurrentTimeFormat()
    {
        $currentHour = (int) date('H');
        if ($currentHour > 12) {
            $messageHour = $currentHour - 12;
            $amPm = 'PM';
        } else {
            $currentHour == 0 ? $messageHour = 12 : $messageHour = $currentHour;
            $amPm = 'AM';
        }
        $messageMinute =  date('i');
        return "{$messageHour}:{$messageMinute} {$amPm}";
    }
}
