#!/bin/bash
cd /var/www/
composer config -g repo.packagist composer https://mirrors.aliyun.com/composer/
composer require easyswoole/easyswoole=3.x -vvv
composer require swoole/ide-helper:@dev -vvv
composer require easyswoole/swoole-ide-helper -vvv
composer require easyswoole/orm -vvv
composer require easyswoole/http-annotation -vvv
composer require easyswoole/ddl -vvv
composer require easyswoole/i18n -vvv
composer require easyswoole/session=2.x -vvv
php vendor/easyswoole/easyswoole/bin/easyswoole install
php easyswoole start