<?php

namespace core\database;

use PDO;
use PDOException;

class DBConnection
{
    // config.php에서 반환하는 배열 중 key : database인 정보를 파라미터로 받는다
    public static function connectDB($databaseConfig)
    {
        try {

            return new PDO(
                $databaseConfig['connection'] . ';dbname=' . $databaseConfig['dbName'],
                $databaseConfig['userName'],
                $databaseConfig['password'],
                $databaseConfig['options']
            );
        } catch (PDOException $e) {

            die($e->getMessage());
        }
    }
}
