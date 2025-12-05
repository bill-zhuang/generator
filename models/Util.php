<?php

namespace app\models;

use Yii;
use yii\db\ActiveRecord;
use yii\db\Schema;

class Util
{
    public static function createDir($dirName)
    {
        if (!file_exists($dirName)) {
            $mask = @umask(0);
            $result = @mkdir($dirName, 0777, true);
            @umask($mask);
        }
    }

    public static function callProtectMethod($class, $method, $params = null)
    {
        $tempClass = new \ReflectionMethod($class, $method);
        $tempClass->setAccessible(true);
        return $tempClass->invoke(new $class(), $params);
    }

    public static function getProjectPath($projectName)
    {
        return realpath(__DIR__) . '/../../' . $projectName;
    }

    public static function getDB()
    {
        return Yii::$app->db;
    }

    /**
     * Created by bill.
     * @param \yii\db\TableSchema $table the table schema
     * @return array
     */
    public static function generateFieldPHPFunc($table)
    {
        $fieldFuncs = [];
        foreach ($table->columns as $column) {
            if ($column->autoIncrement) {
                continue;
            }
            switch ($column->type) {
                case Schema::TYPE_TINYINT:
                case Schema::TYPE_SMALLINT:
                case Schema::TYPE_INTEGER:
                case Schema::TYPE_BIGINT:
                    $fieldFuncs[$column->name] = 'intval';
                    break;
                case Schema::TYPE_BOOLEAN:
                    $fieldFuncs[$column->name] = 'boolval';
                    break;
                case Schema::TYPE_FLOAT:
                case 'double': // Schema::TYPE_DOUBLE, which is available since Yii 2.0.3
                case Schema::TYPE_DECIMAL:
                case Schema::TYPE_MONEY:
                    $fieldFuncs[$column->name] = 'floatval';
                    break;
                case Schema::TYPE_DATE:
                case Schema::TYPE_TIME:
                case Schema::TYPE_DATETIME:
                case Schema::TYPE_TIMESTAMP:
                    $fieldFuncs[$column->name] = '';
                    break;
                default: // strings
                    $fieldFuncs[$column->name] = '';
                    break;
            }
        }

        return $fieldFuncs;
    }

    /**
     * Created by bill
     * @param $tableName
     * @param $modelClass ActiveRecord
     * @return string|string[]|null
     * @throws \yii\db\Exception
     */
    public static function getTableComment($tableName, $modelClass = null)
    {
        if ($modelClass === null) {
            $db = self::getDB();
        } else {
            $db = $modelClass::getDb();
        }
        $createTableSql = $db->createCommand('show create table ' . $tableName)->queryAll()[0]['Create Table'];
        preg_match('/ENGINE[^\']+\'([^\']+)\'/', $createTableSql, $commentMatches);
        if (isset($commentMatches[1])) {
            $tableComment = $commentMatches[1];
        } else {
            $tableComment = '';
        }
        return preg_replace('/表$/u', '', $tableComment);
    }

    /**
     * Created by bill
     * @param $tableName
     * @param $attribute
     * @return string
     * @throws \yii\db\Exception
     */
    public static function getFieldComment($tableName, $attribute)
    {
        $createTableSql = self::getDB()->createCommand('show create table ' . $tableName)->queryAll()[0]['Create Table'];
        preg_match_all('/`([^`]+)`[^`]*?\'([^\']*)\',/', $createTableSql, $fieldComments);
        $comments = array_combine($fieldComments[1], $fieldComments[2]);
        if (isset($comments[$attribute])) {
            return $comments[$attribute];
        } else {
            return '';
        }
    }

    public static function curlGet($url, $params = [], $headers = [])
    {
        $query = http_build_query($params);
        if (!empty($query)) {
            $ch = curl_init($url . '?' . $query);
        } else {
            $ch = curl_init($url);
        }
        $options = [
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_FOLLOWLOCATION => 1,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false,
        ];
        if (!empty($headers)) {
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        }

        //curl的额外参数
        curl_setopt_array($ch, $options);
        $result = curl_exec($ch);
        curl_close($ch);

        return $result;
    }

    public static function curlGetHeader($url)
    {
        $ch = curl_init($url);
        $options = [
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_FOLLOWLOCATION => 1,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false,
            CURLOPT_HEADER => true,
            CURLOPT_HTTPHEADER => ['User-Agent:Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36']
        ];

        //curl的额外参数
        curl_setopt_array($ch, $options);
        $result = curl_exec($ch);
        $headerSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
        $header = substr($result, 0, $headerSize);
        $body = substr($result, $headerSize);

        curl_close($ch);
        return explode("\r\n", $header);
    }
}
 