<?php
    return [
        'SERVER_NAME' => "EasySwoole",
        'MAIN_SERVER' => [
            'LISTEN_ADDRESS' => '0.0.0.0',
            'PORT' => 80,
            'SERVER_TYPE' => EASYSWOOLE_WEB_SERVER,
            //可选为 EASYSWOOLE_SERVER  EASYSWOOLE_WEB_SERVER EASYSWOOLE_WEB_SOCKET_SERVER,EASYSWOOLE_REDIS_SERVER
            'SOCK_TYPE' => SWOOLE_TCP,
            'RUN_MODEL' => SWOOLE_PROCESS,
            'SETTING' => [
                'worker_num' => 8,
                'reload_async' => true,
                'max_wait_time' => 3
            ],
            'TASK' => [
                'workerNum' => 4,
                'maxRunningNum' => 128,
                'timeout' => 15
            ]
        ],
        'TEMP_DIR' => null,
        'LOG_DIR' => null,
        'MYSQL' => [
            'host' => 'mysql',
            'user' => 'root',
            'password' => 'root',
            'database' => 'es_admin_api',
            'charset' => 'utf8mb4'
        ],
//    前端地址
        'FRONT_END_URL' => 'http://app.ngrok.shuimengzhi.com',
//    前端域名
        'FRONT_END_DOMAIN' => 'ngrok.shuimengzhi.com',
//    默认语言
        'LANG' => 'Cn',
//    拥有后台最高权限
        'TOP_ADMIN_ID' => 1,
    ];
