<?php
/*
 * 'local_db' => [
        'class' => 'yii\db\Connection',
        'dsn' => 'mysql:host=127.0.0.1;dbname=yii2',
        'username' => 'root',
        'password' => '123456',
        'charset' => 'utf8mb4',
        'tablePrefix' => '',
    ],
 * */
$localDBConfig = [];
if (file_exists('./config/db_local.php')) {
    $localDBConfig = require_once ('./config/db_local.php');
}

return array_merge(
    [
        //fixed db connect
    ]
    , $localDBConfig
);
