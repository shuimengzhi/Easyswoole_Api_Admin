#!/bin/bash
cd /var/www/
composer config -g repo.packagist composer https://mirrors.aliyun.com/composer/
composer install
php vendor/easyswoole/easyswoole/bin/easyswoole install
php easyswoole start