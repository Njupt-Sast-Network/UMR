<?php
/**
 * 配置文件
 */
return array(
    'DB_TYPE' => 'mysql',
    'DB_HOST' => $_SERVER['DB_HOST'],
    'DB_NAME' => $_SERVER['DB_NAME'],
    'DB_USER' => $_SERVER['DB_USER'],
    'DB_PWD' => $_SERVER['DB_PWD'],
    'DB_PORT' => $_SERVER['DB_PORT'],
    'DB_PREFIX' => $_SERVER['DB_PREFIX'],
    //密钥
    "AUTHCODE" => $_SERVER['AUTHCODE'],
    //cookies
    "COOKIE_PREFIX" => $_SERVER['COOKIE_PREFIX'],
);
