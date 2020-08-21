<?php

namespace app\models;

use core\AppUtils;
use core\mvc\Model;

class PageModel extends Model
{
    private $userTable = 'user';
    private $firebaseTokenTable = 'firebase_token';
    private $friendTable = 'friends';

    private $chatRoomTable = 'chat_room';
    private $chatRoomParticipantsTable = 'chat_room_participants';
    private $chatMessageTable = 'chat_message';

    // ---------------------------------------------- 파이어베이스 토큰 관련 ----------------------------------------------
    public function uploadToken($clientData)
    {
        $userId = $clientData['userId'];
        $token = $clientData['token'];
        $idColumnKeyValue = ['user_id' => $userId];

        $existTokenData = AppUtils::get('database')->fetchValueByName($this->firebaseTokenTable, $idColumnKeyValue);

        if (!$token) return;
        if ($existTokenData) {
            $existToken =  $existTokenData[0]['token'];

            if ($token == $existToken) return;

            $tokenKeyValue = ['token' => "'" . $token . "'"];
            AppUtils::get('database')->updateByName($this->firebaseTokenTable, $idColumnKeyValue, $tokenKeyValue);
        } else {
            $insertedTokenData = [
                'user_id' => $userId,
                'token' => "'" . $token . "'"
            ];
            AppUtils::get('database')->insertData($this->firebaseTokenTable, $insertedTokenData);
        }
    }

    // ---------------------------------------------- 회원가입 관련 ----------------------------------------------
    public function checkDuplicatedEmail($clientData)
    {
        $userEmail = $clientData['email'];
        $searchingKeyValue = ['email' => "'" . $userEmail . "'"];
        $duplicatedEmail = AppUtils::get('database')->fetchValueByName($this->userTable, $searchingKeyValue);

        // 중복이 존재하면 invalid
        if ($duplicatedEmail) {
            $emailValidation =  false;
        } else {
            $emailValidation =  true;
        }
        return $emailValidation;
    }

    public function uploadUser($clientData)
    {
        $userEmail = $clientData['email'];
        $userPassword = $clientData['password'];
        $inputData = [
            'email' => "'" . $userEmail . "'",
            'password' => "'" . $userPassword . "'"
        ];

        AppUtils::get('database')->insertData($this->userTable, $inputData);
        $insertedId  = AppUtils::get('database')->fetchLastInsertID();
        return (int) $insertedId[0]['LAST_INSERT_ID()'];
    }

    public function uploadUserProfile($clientData)
    {
        $userId = $clientData['id'];
        $base64Image = $clientData['base64Image'];
        $userName = $clientData['name'];

        $description = 'profile';
        $imagePath = $this->storeImage($base64Image, $description);
        $uploadData = [
            'name' => "'" . $userName . "'",
            'profile_image_url' => "'" . $imagePath . "'"
        ];
        AppUtils::get('database')->updateByID($this->userTable, $userId, $uploadData);
        return true;
    }
    // ---------------------------------------------- 로그인 관련 ----------------------------------------------
    public function signIn($clientData)
    {
        $email = $clientData['email'];
        $password = $clientData['password'];
        // $firebaseMessagingToken = $clientData['firebaseMessagingToken'];

        $userData = AppUtils::get('database')->fetchLoginData($this->userTable, $email, $password);

        if ($userData) {
            return $userData[0];
        } else {
            return false;
        }
    }

    // TODO: status 코드 어디서 보낼지 정하기
    public function searchFriend($clientData)
    {
        $userId  = $clientData['userId'];
        $searchEmail = $clientData['email'];
        $duplicatedFriendData =  AppUtils::get('database')->fetchFriendByUserId($this->friendTable, $userId, $searchEmail);

        if ($duplicatedFriendData) return http_response_code(203);

        $friendData = AppUtils::get('database')->fetchUserWithoutId($this->userTable, $userId, $searchEmail);

        if ($friendData) {
            http_response_code(200);
            return $friendData[0];
        } else {
            return http_response_code(204);
        }
    }

    // ---------------------------------------------- 친구 관련 ----------------------------------------------
    public function addFriend($clientData)
    {
        $userId = $clientData['userId'];
        $friendData = $clientData['friendData'];
        $inputData = [
            'user_id' => "'" . $userId . "'",
            'friend_id' => "'" . $friendData['friend_id'] . "'",
            'email' => "'" . $friendData['email'] . "'",
            'name' => "'" . $friendData['name'] . "'",
            'profile_image_url' => "'" . $friendData['profile_image_url'] . "'",
        ];

        AppUtils::get('database')->insertData($this->friendTable, $inputData);
        return AppUtils::get('database')->fetchValueByName($this->friendTable, ['user_id' => $userId]);
    }


    public function getFriendList($clientData)
    {
        $userId = $clientData['userId'];
        return AppUtils::get('database')->fetchValueByName($this->friendTable, ['user_id' => $userId]);
    }
    // ---------------------------------------------- 채팅 관련 ----------------------------------------------
    // TODO : 이미 존재하는 채팅 방인지 확인할 것
    public function addChatRoom($clientData)
    {
        $userData = $clientData['user'];
        $participantList = $clientData['participants'];


        AppUtils::get('database')->insertData($this->chatRoomTable, []);
        $insertedIdList  = AppUtils::get('database')->fetchLastInsertID();
        $lastInsertedID = (int) $insertedIdList[0]['LAST_INSERT_ID()'];

        AppUtils::get('database')->insertData($this->chatRoomParticipantsTable, [
            'room_id' => $lastInsertedID,
            'user_id' => $userData['id']
        ]);

        foreach ($participantList as $participant) {
            AppUtils::get('database')->insertData($this->chatRoomParticipantsTable, [
                'room_id' => $lastInsertedID,
                'user_id' => $participant['friend_id']
            ]);
        }

        $responseChatRooms = AppUtils::get('database')->fetchValueByID($this->chatRoomTable, $lastInsertedID)[0];
        $responseParticipants =  AppUtils::get('database')->fetchValueByName($this->chatRoomParticipantsTable, ['room_id' => $lastInsertedID]);

        $userIds = [];
        foreach ($responseParticipants as $responseParticipant) {
            array_push($userIds,  $responseParticipant['user_id']);
        }

        $responseUserDataList = AppUtils::get('database')->fetchMultipleColumn($this->userTable, 'id', $userIds);


        return   [
            'id' => $responseChatRooms['id'],
            'last_chat_message' => $responseChatRooms['last_chat_message'],
            'participant_list' => $responseUserDataList
        ];
    }

    public function getChatRoomList($clientData)
    {
        $userId = $clientData['userId'];
        $responseChatRoomList = [];

        $participatedRoomList = AppUtils::get('database')->fetchValueByName($this->chatRoomParticipantsTable, ['user_id' => $userId]);

        foreach ($participatedRoomList as $participatedRoom) {;
            $chatRoomList = AppUtils::get('database')->fetchValueByID($this->chatRoomTable, $participatedRoom['room_id']);

            foreach ($chatRoomList as $chatRoom) {
                $participantUsers = AppUtils::get('database')->fetchValueByName($this->chatRoomParticipantsTable, ['room_id' => $chatRoom['id']]);

                $userIds = [];
                foreach ($participantUsers as $participantUser) {
                    array_push($userIds,  $participantUser['user_id']);
                }
                $responseUserDataList = AppUtils::get('database')->fetchMultipleColumn($this->userTable, 'id', $userIds);
                $responseChatRoom = [
                    'id' => $chatRoom['id'],
                    'last_chat_message' =>  $chatRoom['last_chat_message'],
                    'participant_list' => $responseUserDataList
                ];
                array_push($responseChatRoomList, $responseChatRoom);
            }
        }

        return $responseChatRoomList;
    }

    public function uploadChatMessage($clientData)
    {
        $chatData = $clientData['chatData'];
        $chatMessage = [
            'room_id' => $chatData['room_id'],
            'sender_id' => $chatData['sender_id'],
            'sender_name' => "'" . $chatData['sender_name'] . "'",
            'profile_image_url' => "'" . $chatData['profile_image_url'] . "'",
            'message' => "'" . $chatData['message'] . "'",
            'message_time' => "'" . $this->formCurrentTimeFormat() . "'"
        ];
        AppUtils::get('database')->insertData($this->chatMessageTable, $chatMessage);
        return $chatMessage;
    }

    public function getChatMessage($clientData)
    {
        $roomId = $clientData['roomId'];
        $columnKeyValue = ['room_id' => $roomId];
        return AppUtils::get('database')->fetchValueByName($this->chatMessageTable, $columnKeyValue);
    }
    // ---------------------------------------------- 유투브 관련 ----------------------------------------------
    // public function getYoutubeChannelVideos($clientData)
    // {
    //     $userInput = $clientData['youtubeChannel'];
    //     $playListResponseJson = $this->queryYoutubePlayListId($userInput);
    //     $playListResponse = json_decode($playListResponseJson, true);
    //     $channelItems = $playListResponse['items'];

    //     if (!$channelItems) return;

    //     $channelThmbnail = $channelItems[0]['snippet']['thumbnails']['default']['url'];
    //     $channelTitle = $channelItems[0]['snippet']['title'];

    //     $playListId = $channelItems[0]['contentDetails']['relatedPlaylists']['uploads'];
    //     $vieoListResposneJson = $this->queryYoutubeVideoId($playListId);
    //     $videoListResponse = json_decode($vieoListResposneJson, true);
    //     $videoList = $videoListResponse['items'];

    //     if (!$videoList) return;

    //     $youtubeVideos = [];

    //     foreach ($videoList as $videoItem) {
    //         $snippet = $videoItem['snippet'];
    //         $videoId = $snippet['resourceId']['videoId'];
    //         $title = $snippet['title'];

    //         $videoData = [
    //             'channel_title' => $channelTitle,
    //             'channel_thumbnail' => $channelThmbnail,
    //             'title' => $title,
    //             'video_id' => $videoId
    //         ];
    //         array_push($youtubeVideos, $videoData);
    //     }

    //     return $youtubeVideos;
    // }

    public function getYoutubeChannelVideos($clientData)
    {
        $userInput = $clientData['youtubeChannel'];
        $playListResponseJson = $this->queryYoutubePlayListId($userInput);
        $playListResponse = json_decode($playListResponseJson, true);
        $channelItems = $playListResponse['items'];

        if (!$channelItems) return;

        $channelThmbnail = $channelItems[0]['snippet']['thumbnails']['default']['url'];
        $channelTitle = $channelItems[0]['snippet']['title'];

        $playListId = $channelItems[0]['contentDetails']['relatedPlaylists']['uploads'];
        $vieoListResposneJson = $this->queryYoutubeVideoId($playListId);
        $videoListResponse = json_decode($vieoListResposneJson, true);
        $nexPageToken = $videoListResponse['nextPageToken'];
        $videoList = $videoListResponse['items'];

        if (!$videoList) return;

        $youtubeVideos = [];

        foreach ($videoList as $videoItem) {
            $snippet = $videoItem['snippet'];
            $videoId = $snippet['resourceId']['videoId'];
            $title = $snippet['title'];

            $videoData = [
                'channel_title' => $channelTitle,
                'channel_thumbnail' => $channelThmbnail,
                'title' => $title,
                'video_id' => $videoId
            ];
            array_push($youtubeVideos, $videoData);
        }

        return [
            'next_page_url' => "https://www.googleapis.com/youtube/v3/playlistItems?part=snippet&playlistId={$playListId}&maxResults=10&key=AIzaSyA8ePDC-Jb5sgeJYpBYF9xzUhlGA2rh7eI&pageToken=",
            'next_page_token' => $nexPageToken,
            'youtube_data_list' => $youtubeVideos
        ];
    }

    public function getNextPageYoutube($clientData)
    {
        $nextPageUrl = $clientData['nextPageUrl'];
        $nextPageToken = $clientData['nextPageToken'];
        $channelTitle = $clientData['channelTitle'];
        $channelThmbnail = $clientData['channelThumbnail'];

        $vieoListResposneJson = $this->queryNextPageYoutube("{$nextPageUrl}{$nextPageToken}");
        $videoListResponse = json_decode($vieoListResposneJson, true);
        $nexPageToken = $videoListResponse['nextPageToken'];
        $videoList = $videoListResponse['items'];

        if (!$videoList) return;

        $youtubeVideos = [];

        foreach ($videoList as $videoItem) {
            $snippet = $videoItem['snippet'];
            $videoId = $snippet['resourceId']['videoId'];
            $title = $snippet['title'];

            $videoData = [
                'channel_title' => $channelTitle,
                'channel_thumbnail' => $channelThmbnail,
                'title' => $title,
                'video_id' => $videoId
            ];
            array_push($youtubeVideos, $videoData);
        }

        return [
            'next_page_url' => $nextPageUrl,
            'next_page_token' => $nexPageToken,
            'youtube_data_list' => $youtubeVideos
        ];
    }

    // 유투브 같이보기 초대하는 메소드
    public function inviteFriends($clientData)
    {
        $inviterUserId = $clientData['inviterId'];
        $inviterName = $clientData['inviterName'];
        $friendList = $clientData['friendsIds'];
        $youtubeData = $clientData['youtubeData'];
        $data = [
            'roomId' => $inviterUserId,
            'inviterName' => $inviterName,
            'youtubeData' => $youtubeData

        ];

        $tokens = [];

        $tokenDataList = AppUtils::get('database')->fetchMultipleColumn($this->firebaseTokenTable, 'user_id', $friendList);

        foreach ($tokenDataList as $tokenData) {
            array_push($tokens, $tokenData['token']);
        }
        return $this->sendFirebaseMessage($tokens, $data);
    }

    // 파이어베이스로 파라미터의 토큰들에게 메시지를 보내달라는 요청을 보내는 메소드
    private function sendFirebaseMessage($tokens, $data)
    {
        $firebaseUrl = AppUtils::get('config')['firebaseMessaging']['url'];
        $serverKey = AppUtils::get('config')['firebaseMessaging']['serverKey'];

        $fields = array(
            'registration_ids' => $tokens,
            'data' => $data
        );

        $header = array(
            "Authorization:key = {$serverKey}",
            'Content-Type: application/json'
        );

        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => $firebaseUrl,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => json_encode($fields),
            CURLOPT_HTTPHEADER => $header
        ));

        $response = curl_exec($curl);

        curl_close($curl);
        return $response;
    }

    private function queryYoutubePlayListId($requestChannel)
    {
        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => "https://www.googleapis.com/youtube/v3/channels?part=snippet,contentDetails,statistics&forUsername={$requestChannel}&key=AIzaSyA8ePDC-Jb5sgeJYpBYF9xzUhlGA2rh7eI",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "GET",
        ));

        $response = curl_exec($curl);

        curl_close($curl);
        return $response;
    }

    private function queryYoutubeVideoId($youtubePlayListId)
    {
        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => "https://www.googleapis.com/youtube/v3/playlistItems?part=snippet&playlistId={$youtubePlayListId}&maxResults=10&key=AIzaSyA8ePDC-Jb5sgeJYpBYF9xzUhlGA2rh7eI",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "GET",
        ));

        $response = curl_exec($curl);

        curl_close($curl);
        return $response;
    }

    public function queryNextPageYoutube($nextPageUrl)
    {
        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => $nextPageUrl,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "GET",
        ));

        $response = curl_exec($curl);

        curl_close($curl);
        return $response;
    }

    public function test($nextPageUrl)
    {
        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => $nextPageUrl,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "GET",
        ));

        $response = curl_exec($curl);

        curl_close($curl);
        echo $response;
    }
}
