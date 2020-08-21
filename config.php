<?php
// 앱에 필요한 config 정보를 저장하는 배열을 반환한다
return [
    'database' => [
        'connection' => 'mysql:host=127.0.0.1',
        'userName' => 'root',
        'password' => '!qudgkr931123',
        'dbName' => 'video_together',
        'options' => [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_ERRMODE => PDO::ERRMODE_WARNING
        ]
    ],

    // TODO : 변경하기
    'firebaseMessaging' => [
        'url' => 'https://fcm.googleapis.com/fcm/send',
        'serverKey' => 'AAAAL4p8Ym0:APA91bFRHkutbA9DmQVJnHbTu1YdqS9dU9RAzQ5TaHW5JZC1tnEjXbZgiUSz0fbj0eemBDn1OKkd_U9NeeUjG_IO1284d44eWCg-1nRpHFZaofGpuLiJLZUBqtN6HzPIb5kbb5ruLFul'
    ]
];
