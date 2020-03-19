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
    'testDB' => [
        'class' => 'yii\db\Connection',
        'dsn' => 'mysql:host=localhost;dbname=bill',
        'username' => 'root',
        'password' => '',
        'charset' => 'utf8mb4',
    ],
];
