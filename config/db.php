<?php

return [
    'db' => [
        'class' => 'yii\db\Connection',
        'dsn' => 'mysql:host=localhost;dbname=generate',
        'username' => 'root',
        'password' => '',
        'charset' => 'utf8mb4',
        // Schema cache options (for production environment)
        //'enableSchemaCache' => true,
        //'schemaCacheDuration' => 60,
        //'schemaCache' => 'cache',
    ],
    'local_db' => [
        'class' => 'yii\db\Connection',
        'dsn' => 'mysql:host=127.0.0.1;dbname=yii2',
        'username' => 'root',
        'password' => '123456',
        'charset' => 'utf8mb4',
    ],
    'testDB' => [
        'class' => 'yii\db\Connection',
        'dsn' => 'mysql:host=localhost;dbname=bill',
        'username' => 'root',
        'password' => '',
        'charset' => 'utf8mb4',
    ],
];
