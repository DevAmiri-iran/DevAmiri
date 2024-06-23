<?php

namespace App\System;

use JetBrains\PhpStorm\NoReturn;

trait FileMaker
{
    private static array $item = [];

    private static function Apache(): void
    {
        self::$item[base_path('.htaccess')] = <<<HTACCESS
<IfModule mod_rewrite.c>
    RewriteEngine On

    RewriteCond %{REQUEST_URI}::$1 ^(.*?/)(.*)::\\2$
    RewriteRule ^(.*)$ - [E=BASE:%1]

    RewriteCond %{HTTP_HOST} !^$
    RewriteCond %{HTTP_HOST} ^(.+)$
    RewriteRule ^(.*)$ %{ENV:BASE}/public/$1 [L]

    RewriteRule ^$ %{ENV:BASE}/public/ [L]

    RewriteRule ^(.*)$ %{ENV:BASE}/public/$1 [L]

    <IfModule mime_module>
        AddHandler application/x-httpd-ea-php82 .php .php8 .phtml
    </IfModule>
</IfModule>
HTACCESS;

        self::$item[public_path('.htaccess')] = <<<HTACCESS
<IfModule mod_rewrite.c>
    RewriteEngine On

    Header always set Strict-Transport-Security "max-age=31536000; includeSubDomains; preload"
    Header set Access-Control-Allow-Origin "*"

    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteCond %{REQUEST_FILENAME} !-l
    RewriteRule ^(.*)$ index.php?route=/$1 [QSA,L,B]
</IfModule>
HTACCESS;
    }



    private static function env(): void
    {
        $url = getCurrentUrl();
        $key = str_replace('=', '', base64_encode(random())) . '5' . str_replace('=', '', base64_encode(random()));
        self::$item[base_path('.env')] = <<<ENV
APP_URL=$url
APP_KEY=$key
APP_DEBUG=true

DB_DRIVER=mysql
DB_HOST=127.0.0.1
DB_DATABASE=a
DB_USERNAME=root
DB_PASSWORD=
DB_CHARSET=utf8
DB_COLLATION=utf8_unicode_ci
DB_PREFIX=
ENV;
    }

    private static function handel(): void
    {
        if (! file_exists(base_path('.env')))
            self::env();

        if (str_contains($_SERVER['SERVER_SOFTWARE'], 'Apache')) {
            self::Apache();
            //} elseif (str_contains($_SERVER['SERVER_SOFTWARE'], 'Nginx')) {
            //self::Nginx();
        } elseif (str_contains($_SERVER['SERVER_SOFTWARE'], 'LiteSpeed')) {
            self::Apache();
        }
    }
    #[NoReturn] public static function StartFileMaker(): void
    {
        self::handel();
        foreach (self::$item as $path => $value) {
            file_put_contents($path, $value);
        }
        redirect(getCurrentUrl());
    }
}