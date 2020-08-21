<?php

namespace core;

use FFI\Exception;

/*
웹 동작에 있어서 필요한 정보를 $registry 배열에 저장하는 static 클래스이다. 
 - 현재 이 프로젝트에서는 config 파일과 QueryBuilder 객체를 $registry 배열에 담고 있음
 - model 클래스에서 주로 사용하며, 저장해놓은 QueryBuilder 객체를 이용해 데이터베이스에 query를 요청
*/

class AppUtils
{
    protected static $registry = [];

    /* 
    $registry 배열에 데이터(객체 혹은 변수)를 추가할 때 사용하는 메소드 이다.
     - CoreBundle.php 파일에서 필요한 config 변수와 QueryBuilder 객체를 담는데 사용
    */
    public static function bind($key, $value)
    {
        static::$registry[$key] = $value;
    }

    /* 
    $registry 배열에 데이터(객체 혹은 변수)를 저장되어 있는 데이터를 사용할 때 호출하는 메소드
     - model 클래스에서 QueryBuilder 객체를 이용해 데이터 베이스에 query 요청을 하기 위해 주로 사용하고 있다.
     - modle 클래스에서 FCM을 사용할 때 필요한 정보를 config 변수에서 추출해서 사용.
    */
    public static function get($key)
    {
        if (!array_key_exists($key, static::$registry)) {

            throw new Exception("From App : {$key} 관련 값이 존재하지 않음");
        }

        return static::$registry[$key];
    }
}
