<?php
// ------------ 로그인/회원가입 관련 라우터 ------------
// 로그인
$router->post('sign-in', 'PageController@signIn');

// 회원가입
$router->get('sign-up', 'PageController@signUp');
$router->post('sign-up', 'PageController@signUp');

// ------------ 친구 관련 라우터 ------------
// 친구 추가
$router->get('add-friend', 'PageController@addFriend');
$router->post('add-friend', 'PageController@addFriend');

// 친구 목록
$router->get('friend-list', 'PageController@friendList');

// ------------ 채팅 관련 라우터 ------------
$router->get('chat', 'PageController@chat');
$router->post('chat', 'PageController@chat');

// ------------ 유투브 관련 라우터 ------------
$router->get('youtube', 'PageController@youtube');
$router->post('youtube', 'PageController@youtube');
