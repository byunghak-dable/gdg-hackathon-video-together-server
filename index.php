<?php

use core\router\{Router, RequestURI};

// 에러 로그 출력하는 코드
error_reporting(E_ALL);
ini_set('display_errors', 1);

// 한국 시간 디폴트 timezone으로 변경
date_default_timezone_set('Asia/Seoul'); 

// 클라이언트 요청 제한 관련
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, PUT, GET, DELETE');
header('Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With');

// 웹이 동작하는데 필요한 util 클래스들을 묶어놓은 파일이다.
require 'core/CoreBundle.php';
require 'app/controllers/PageController.php';

// 라우팅을 위한 작업
// 입력받은 uri에서 domain(ip)를 제거해서 저장
$uri = RequestURI::uriPath();
// $router 배열을 로드한 후 direct : controller 객체 생성 및 메소드 호출
Router::load('app/routes.php')->direct($uri, RequestURI::defineRequestType());
