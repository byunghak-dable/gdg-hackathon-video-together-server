<?php

use core\AppUtils;
use core\database\{NameController, QueryBuilder, DBConnection, RedisHelper};

// web이 동작하는 데 필요한 모든 파일들을 묶어서 한번에 index.php 파일에서 호출
// view 필요하지 않아서 주석처리
require 'core/mvc/Controller.php';
require 'core/mvc/Model.php';
require 'core/router/Router.php';
require 'core/router/RequestURI.php';
require 'core/database/DBConnection.php';
require 'core/database/QueryBuilder.php';
require 'core/AppUtils.php';


// config와 QueryBuilder 객체를 저장하는 App객체를 어느 파일에서도 사용할 수 있게 저장
AppUtils::bind('config', require 'config.php');
AppUtils::bind('database',  new QueryBuilder(

    // config.php 파일에 있는 key-value 배열에서 database를 선택하여 인자로 사용
    DBConnection::connectDB(AppUtils::get('config')['database'])
));
