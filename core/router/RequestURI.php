<?php

namespace core\router;

class RequestURI
{
    // Request_URI 중 path와 관련없는 부분 제거
    public static function uriPath()
    {
        // Memo : get 사용 시 '?'뒤 url 지우기(PHP_URL_PATH) + '/' 지우기
        return trim(
            parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH),
            '/'
        );
    }

    // GET, POST와 같은 request-type을 return 하는 메소드
    public static function defineRequestType()
    {
        return $_SERVER['REQUEST_METHOD'];
    }
}
