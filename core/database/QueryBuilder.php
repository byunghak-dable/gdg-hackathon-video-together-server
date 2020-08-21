<?php

namespace core\database;

use PDO;
use FFI\Exception;

class QueryBuilder
{
    protected $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    // 테이블을 선택하는 메소드 
    public function selectTable(String $table)
    {
        // Memo : prepare은 PDO 클래스 메소드이다.
        $statement = $this->pdo->prepare("select * from {$table}");
        $statement->execute();
        return $statement;
    }

    public function fetchAllByArray($statement)
    {
        return $statement->fetchAll();
    }

    // selectTable 메소드를 실행 후 모든 값들을 쿼리하는 메소드 
    public function fetchAllValues($statement, $dataClass)
    {

        return $statement->fetchAll(PDO::FETCH_CLASS, $dataClass);
    }

    // TODO : 메소드 명 변경하기
    public function fetchReversed($table, $columnName, $order)
    {
        $stringFormat = "select * from {$table} order by {$columnName} {$order}";
        $statement = $this->executeStateMent($stringFormat);
        return $statement->fetchAll();
    }

    // 제한된 개수만을 쿼리하는 메소드
    public function fetchLimitedValues($table, $columnName, $order, $startNumber, $endNumber, $dataClass)
    {
        $stringFormat = "select * from {$table} order by {$columnName} {$order} limit {$startNumber}, {$endNumber}";
        $statement = $this->executeStateMent($stringFormat);
        return $statement->fetchAll(PDO::FETCH_CLASS, $dataClass);
    }

    public function fetchLimitedArray($table, $columnName, $order, $startNumber, $endNumber)
    {
        $stringFormat = "select * from {$table} order by {$columnName} {$order} limit {$startNumber}, {$endNumber}";
        $statement = $this->executeStateMent($stringFormat);
        return $statement->fetchAll();
    }

    public function fetchLimitedArrayByName($table, $keyValueData, $columnName, $order, $startNumber, $endNumber)
    {
        $key = array_keys($keyValueData)[0];
        $value = array_values($keyValueData)[0];

        $stringFormat = "select * from {$table} where {$key} = {$value} order by {$columnName} {$order} limit {$startNumber}, {$endNumber}";
        $statement = $this->executeStateMent($stringFormat);
        return $statement->fetchAll();
    }

    // 특정 id 값을 선택하여 쿼리하는 메소드
    public function fetchValueByID($table, $requestID)
    {
        $stringFormat = "select * from {$table} where id={$requestID}";
        $statement = $this->executeStateMent($stringFormat);
        return $statement->fetchAll();
    }

    public function fetchLoginData($tableName, $email, $password)
    {
        $stringFormat = "select * from {$tableName} where email='{$email}' and password='{$password}'";
        $statement = $this->executeStateMent($stringFormat);
        return $statement->fetchAll();
    }

    // TODO : video together 에서만 사용
    public function fetchFriendByUserId($tableName, $userId, $friendEmail)
    {
        $stringFormat = "select * from {$tableName} where user_id='{$userId}' and email='{$friendEmail}'";
        $statement = $this->executeStateMent($stringFormat);
        return $statement->fetchAll();
    }

    // TODO : video together 에서만 사용
    public function fetchUserWithoutId($tableName, $userId, $searchEmail)
    {
        $stringFormat = "select * from {$tableName} where id!='{$userId}' and email='{$searchEmail}'";
        $statement = $this->executeStateMent($stringFormat);
        return $statement->fetchAll();
    }

    // 사용자 key 와 value로 db 검색후 데이터 쿼리하는 메소드
    public function fetchValueByName($tableName, $keyValueData)
    {
        $stringFormat = sprintf(
            'select * from %s where %s=%s',
            $tableName,
            implode(', ', array_keys($keyValueData)),
            implode(', ', array_values($keyValueData))
        );

        $statement = $this->executeStateMent($stringFormat);
        return $statement->fetchAll();
    }

    public function fetchRawsByName($tableName, $columnName, $valueList)
    {
        $stringFormat = sprintf(
            'select * from %s where %s in (%s)',
            $tableName,
            $columnName,
            implode(', ', array_values($valueList))
        );

        $statement = $this->executeStateMent($stringFormat);
        return $statement->fetchAll();
    }

    public function fetchStoryImages($tableName, $columnKeyValueData, $columnName, $valueList)
    {
        $stringFormat = sprintf(
            'select story_id, photo_path from %s where %s=%s and %s in(%s)',
            $tableName,
            implode(', ', array_keys($columnKeyValueData)),
            implode(', ', array_values($columnKeyValueData)),
            $columnName,
            implode(', ', array_values($valueList))
        );

        $statement = $this->executeStateMent($stringFormat);
        return $statement->fetchAll();
    }

    // 일정 데이터를 쿼리하는 메소드
    public function fetchRangeData($tableName, $coupleID, $columnName, $startRange)
    {
        $stringFormat = "select * from {$tableName} where couple_id={$coupleID} and {$columnName}>{$startRange}";

        $statement = $this->executeStateMent($stringFormat);
        return $statement->fetchAll();
    }

    // TODO: 채팅 데이터 가져오기 마무리하기
    public function fetchMessageToken($tableName, $roomKeyValue, $senderIDKeyValue)
    {
        $stringFormat = sprintf(
            'select * from %s where %s=%s and %s!=%s',
            $tableName,
            implode(', ', array_keys($roomKeyValue)),
            implode(', ', array_values($roomKeyValue)),
            implode(', ', array_keys($senderIDKeyValue)),
            implode(', ', array_values($senderIDKeyValue))
        );
        $statement = $this->executeStateMent($stringFormat);
        return $statement->fetchAll();
    }

    public function fetchSepcificData($tableName, $firstColumnKeyValue, $secondColumnKeyValue)
    {
        $stringFormat = sprintf(
            'select * from %s where %s=%s and %s=%s',
            $tableName,
            implode(', ', array_keys($firstColumnKeyValue)),
            implode(', ', array_values($firstColumnKeyValue)),
            implode(', ', array_keys($secondColumnKeyValue)),
            implode(', ', array_values($secondColumnKeyValue))
        );
        $statement = $this->executeStateMent($stringFormat);
        return $statement->fetchAll();
    }

    public function fetchMultipleColumn($tableName, $columnName, $valueList)
    {
        $stringFormat = sprintf(
            'select * from %s where %s in (%s)',
            $tableName,
            $columnName,
            implode(', ', array_values($valueList))
        );
        $statement = $this->executeStateMent($stringFormat);
        return $statement->fetchAll();
    }

    // 테이블에 데이터를 저장하는 메소드
    public function insertData(String $table, $keyValueData)
    {
        $stringFormat = sprintf(
            'insert into %s (%s) values (%s)',
            $table,
            implode(', ', array_keys($keyValueData)),
            implode(', ', array_values($keyValueData))
        );

        return $this->executeStateMent($stringFormat);
    }

    public function updateByName($tableName, $columnKeyValue, $keyValueData)
    {
        $stringFormat = sprintf(
            'update %s set %s = %s where %s=%s',
            $tableName,
            implode(', ', array_keys($keyValueData)),
            implode(', ', array_values($keyValueData)),
            implode(', ', array_keys($columnKeyValue)),
            implode(', ', array_values($columnKeyValue))
        );

        $this->executeStateMent($stringFormat);
    }

    public function updateMultipleValue($tableName, $columnKeyValue, $keyValueData)
    {

        // 여러 컬럼 값을 변경할 때 sql 문을 간결하게 만들 수 있도록 한다
        // 새롭게 컬럼 = 값을 value로 가지는 배열 생성 후 strintf에 넣어 sql 만듬
        for ($i = 0; $i < count($keyValueData); $i++) {
            $key = array_keys($keyValueData)[$i];
            $value = array_values($keyValueData)[$i];
            $column[$i] =  "{$key} = {$value}";
        }
        $stringFormat = sprintf(
            'UPDATE %s set %s where %s=%s',
            $tableName,
            implode(', ', $column),
            implode(', ', array_keys($columnKeyValue)),
            implode(', ', array_values($columnKeyValue))
        );
        $this->executeStateMent($stringFormat);
    }

    public function updateByID($tableName, $requestID, $keyValueData)
    {
        // 여러 컬럼 값을 변경할 때 sql 문을 간결하게 만들 수 있도록 한다
        // 새롭게 컬럼 = 값을 value로 가지는 배열 생성 후 strintf에 넣어 sql 만듬
        for ($i = 0; $i < count($keyValueData); $i++) {
            $key = array_keys($keyValueData)[$i];
            $value = array_values($keyValueData)[$i];
            $column[$i] =  "{$key} = {$value}";
        }

        $stringFormat = sprintf(
            'update %s set %s where id=%s',
            $tableName,
            implode(', ', $column),
            $requestID
        );

        $this->executeStateMent($stringFormat);
    }

    public function deleteByID($tableName, $requestID)
    {
        $stringFormat = "delete from {$tableName} where id = {$requestID}";
        $this->executeStateMent($stringFormat);
    }

    public function deleteByColumn($tableName, $columnName, $value)
    {

        $stringFormat = "delete from {$tableName} where {$columnName} = {$value}";
        $this->executeStateMent($stringFormat);
    }

    public function deleteSpecificColumn($tableName, $firstColumnKeyValue, $secondColumnKeyValue)
    {
        $stringFormat = sprintf(
            'delete from %s where %s=%s and %s=%s',
            $tableName,
            implode(', ', array_keys($firstColumnKeyValue)),
            implode(', ', array_values($firstColumnKeyValue)),
            implode(', ', array_keys($secondColumnKeyValue)),
            implode(', ', array_values($secondColumnKeyValue))
        );

        $this->executeStateMent($stringFormat);
    }

    // 페이지의 갯수를 결정하는 메소드
    public function paginationPageNum($tableName, $resultPerPage)
    {
        // 자유 계시판 계시글 전체 갯수를 받아온다.
        $totalRowCount = $this->selectTable($tableName)->rowCount();
        // 페이지의 갯수 선언
        return ceil($totalRowCount / $resultPerPage);
    }

    // 한 페이지에 보여줄 데이터를 쿼리하는 메소드
    public function pagination($tableName, $dataClass, $currentPage, $valuesPerPage)
    {
        // 데이터 저장소에서 계시판 정보 불러와야함
        $startNumber = ($currentPage - 1) * $valuesPerPage;

        return $this->fetchLimitedValues($tableName, 'id', 'desc', $startNumber, $valuesPerPage, $dataClass);
    }

    public function fetchLastInsertID()
    {
        $stringFormat = "select LAST_INSERT_ID()";
        $statement = $this->executeStateMent($stringFormat);
        return $statement->fetchAll();
    }

    // query 문을 입력받아 execute하는 메소드
    private function executeStatement($stringFormat)
    {
        try {

            $statement = $this->pdo->prepare($stringFormat);
            $statement->execute();
        } catch (Exception $th) {

            die('데이터 베이스 관련 에러 발생');
        }

        return $statement;
    }
}
