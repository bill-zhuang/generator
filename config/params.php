<?php

$localParamsConfig = [];
if (file_exists('./config/params_local.php')) {
    $localParamsConfig = require_once ('./config/params_local.php');
}
return array_merge(
    [
        'adminEmail' => 'admin@example.com',
        'timeZone' => 'Asia/Shanghai',
    ]
    , $localParamsConfig
);