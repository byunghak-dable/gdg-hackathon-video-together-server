<?php

namespace core\router;

use FFI\Exception;

class Router
{
    // GET과 POST를 구별해서 저장하기 위해 각각의 key 생성
    protected $routes = [
        'GET' => [],
        'POST' => [],
        'PUT' => [],
        'DELETE' => []
    ];

    // $router 객체를 생성, $file = router.php 파일을 로드하여 routes 데이터 입력 후 반환하는 메소드
    public static function load($file)
    {
        // 자기 자신 객체를 생성할 때 사용
        $router = new static;

        require $file;
        return $router;
    }

    // routes.php 파일에서 라우팅 위치를 정할 때 다음 메소드를 사용하여 $routes['GET']에 저장
    public function get($uri, $controller)
    {
        $this->routes['GET'][$uri] = $controller;
    }

    // routes.php 파일에서 라우팅 위치를 정할 때 다음 메소드를 사용하여 $routes['POST']에 저장
    public function post($uri, $controller)
    {
        $this->routes['POST'][$uri] = $controller;
    }

    // routes.php 파일에서 라우팅 위치를 정할 때 다음 메소드를 사용하여 $routes['PUT']에 저장
    public function put($uri, $controller)
    {
        $this->routes['PUT'][$uri] = $controller;
    }

    // routes.php 파일에서 라우팅 위치를 정할 때 다음 메소드를 사용하여 $routes['DELETE']에 저장
    public function delete($uri, $controller)
    {
        $this->routes['DELETE'][$uri] = $controller;
    }

    public function getRoutes()
    {
        return $this->routes;
    }

    // requestType 을 선별하고 uri의 end point 를 반환
    // Memo : explode - '@'가 존재하면 string을 분할시키고 배열에 저장한다
    // Memo : splat operator(...) - 메소드 인자로 넣어주면 배열의 각 요소가 인자로 들어간다
    public function direct($uri, $requestType)
    {
        if (array_key_exists($uri, $this->routes[$requestType])) {

            // ...explode 의 결과 -> 첫 번쨰 인자 : controller 이름, 두 번째 인자 : controller클래스의 메소드 명
            return $this->callAction(
                ...explode('@', $this->routes[$requestType][$uri])
            );
        } else {
            // 동작하지 않음 -> 페이지 자체가 실행되지 않기 때문 -> 페이지 디버깅 방법 찾기
            throw new Exception('URI 정확하지 않음');
        }
    }

    // controller 객체를 생성하고 $action(controller클래스 메소드)를 실행하는 메소드
    private function callAction($controller, $action)
    {

        $controller  = "app\controllers\\{$controller}";
        $controller = new $controller;

        if (method_exists($controller, $action)) {

            return $controller->$action();
        } else {

            throw new Exception("{$controller}가 {$action}에 반응하지 않습니다.");
        }
    }
}
