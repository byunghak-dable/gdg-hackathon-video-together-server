<?php

namespace app\controllers;

use core\mvc\Controller;
use core\AppUtils;

class PageController extends Controller
{
    // ------------------------ 로그인/회원가입 관련 ------------------------
    public function signUp()
    {
        $clientBodyData = $this->getInputByJson();
        $model = $this->createModel('PageModel');

        switch ($_SERVER['REQUEST_METHOD']) {

            case "POST":
                switch ($clientBodyData['request']) {
                    case 'checkDuplicatedEmail':
                        $result = $model->checkDuplicatedEmail($clientBodyData['data']);
                        echo json_encode($result);
                        $this->getMethodResponseCode($result, 200, 204);
                        break;

                    case 'uploadUser':
                        $result = $model->uploadUser($clientBodyData['data']);
                        echo json_encode($result);
                        $this->getMethodResponseCode($result, 200, 204);
                        break;

                    case 'uploadUserProfile':
                        $result = $model->uploadUserProfile($clientBodyData['data']);
                        echo json_encode($result);
                        $this->getMethodResponseCode($result, 200, 204);
                        break;
                }
                break;
        }
    }

    public function signIn()
    {
        $clientBodyData = $this->getInputByJson();
        $model = $this->createModel('PageModel');

        switch ($_SERVER['REQUEST_METHOD']) {

            case "POST":
                switch ($clientBodyData['request']) {
                    case 'signIn':
                        $result = $model->signIn($clientBodyData['data']);
                        echo json_encode($result);
                        $this->getMethodResponseCode($result, 200, 204);
                        break;

                    case 'uploadFirebaseToken':
                        $result = $model->uploadToken($clientBodyData['data']);
                        echo json_encode($result);
                        $this->getMethodResponseCode($result, 200, 204);
                }
                break;
        }
    }

    // ------------------------ 친구 관련 ------------------------
    public function friendList()
    {
        // $clientBodyData = $this->getInputByJson();
        $model = $this->createModel('PageModel');

        switch ($_SERVER['REQUEST_METHOD']) {

            case "GET":
                switch ($_GET['request']) {
                    case 'getFriendList':
                        $result = $model->getFriendList($_GET);
                        echo json_encode($result);
                        $this->getMethodResponseCode($result, 200, 204);
                        break;
                }
                break;
        }
    }

    public function addFriend()
    {
        $clientBodyData = $this->getInputByJson();
        $model = $this->createModel('PageModel');

        switch ($_SERVER['REQUEST_METHOD']) {

                // TODO : status code 클라에서 받지 못하는 중 fix 되면 바꾸기
            case "GET":
                switch ($_GET['request']) {
                    case 'searchFriend':
                        $result = $model->searchFriend($_GET);
                        echo json_encode($result);
                        // $this->getMethodResponseCode($result, 200, 204);
                        break;
                }
                break;

            case "POST":
                switch ($clientBodyData['request']) {
                    case 'addFriend':
                        $result = $model->addFriend($clientBodyData['data']);
                        echo json_encode($result);
                        $this->getMethodResponseCode($result, 200, 204);
                        break;
                }
                break;
        }
    }

    // ------------------------ 채팅 관련 ------------------------
    public function chat()
    {
        $clientBodyData = $this->getInputByJson();
        $model = $this->createModel('PageModel');

        switch ($_SERVER['REQUEST_METHOD']) {

            case "GET":
                switch ($_GET['request']) {
                    case 'getChatRoomList':
                        $result = $model->getChatRoomList($_GET);
                        echo json_encode($result);
                        $this->getMethodResponseCode($result, 200, 204);
                        break;

                    case 'getChatMessage':
                        $result = $model->getChatMessage($_GET);
                        echo json_encode($result);
                        $this->getMethodResponseCode($result, 200, 204);
                        break;
                }
                break;


            case "POST":
                switch ($clientBodyData['request']) {
                    case 'addChatRoom':
                        $result = $model->addChatRoom($clientBodyData['data']);
                        echo json_encode($result);
                        $this->getMethodResponseCode($result, 200, 204);
                        break;

                    case 'uploadChatMessage':
                        $result = $model->uploadChatMessage($clientBodyData['data']);
                        echo json_encode($result);
                        $this->getMethodResponseCode($result, 200, 204);
                        break;
                }
                break;
        }
    }

    // ------------------------ 유투브 관련 ------------------------
    public function youtube()
    {
        $clientBodyData = $this->getInputByJson();
        $model = $this->createModel('PageModel');

        switch ($_SERVER['REQUEST_METHOD']) {

            case "GET":
                switch ($_GET['request']) {
                    case 'getChannelVideos':
                        $result = $model->getYoutubeChannelVideos($_GET);
                        echo json_encode($result);
                        $this->getMethodResponseCode($result, 200, 204);
                        break;

                    case 'getNextPage':
                        $result = $model->getNextPageYoutube($_GET);
                        echo json_encode($result);
                        $this->getMethodResponseCode($result, 200, 204);
                        break;

                    case 'test':
                        $model->test();
                        break;
                }
                break;

            case "POST":
                switch ($clientBodyData['request']) {
                    case 'inviteFriends':
                        $result = $model->inviteFriends($clientBodyData['data']);
                        echo json_encode($result);
                        $this->getMethodResponseCode($result, 200, 204);
                        break;
                }
                break;
        }
    }
}
