<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInitebb6ccacfc92d5643dd3c68f4ea3aa91
{
    public static $files = array (
        '0e6d7bf4a5811bfa5cf40c5ccd6fae6a' => __DIR__ . '/..' . '/symfony/polyfill-mbstring/bootstrap.php',
        '7b11c4dc42b3b3023073cb14e519683c' => __DIR__ . '/..' . '/ralouphie/getallheaders/src/getallheaders.php',
        'c964ee0ededf28c96ebd9db5099ef910' => __DIR__ . '/..' . '/guzzlehttp/promises/src/functions_include.php',
        'a0edc8309cc5e1d60e3047b5df6b7052' => __DIR__ . '/..' . '/guzzlehttp/psr7/src/functions_include.php',
        'd767e4fc2dc52fe66584ab8c6684783e' => __DIR__ . '/..' . '/adbario/php-dot-notation/src/helpers.php',
        '65fec9ebcfbb3cbb4fd0d519687aea01' => __DIR__ . '/..' . '/danielstjules/stringy/src/Create.php',
        '37a3dc5111fe8f707ab4c132ef1dbc62' => __DIR__ . '/..' . '/guzzlehttp/guzzle/src/functions_include.php',
        'b067bc7112e384b61c701452d53a14a8' => __DIR__ . '/..' . '/mtdowling/jmespath.php/src/JmesPath.php',
        '66453932bc1be9fb2f910a27947d11b6' => __DIR__ . '/..' . '/alibabacloud/client/src/Functions.php',
        '1cfd2761b63b0a29ed23657ea394cb2d' => __DIR__ . '/..' . '/topthink/think-captcha/src/helper.php',
    );

    public static $prefixLengthsPsr4 = array (
        't' => 
        array (
            'think\\composer\\' => 15,
            'think\\captcha\\' => 14,
        ),
        'c' => 
        array (
            'clagiordano\\weblibs\\configmanager\\' => 34,
        ),
        'a' => 
        array (
            'app\\' => 4,
        ),
        'S' => 
        array (
            'Symfony\\Polyfill\\Mbstring\\' => 26,
            'Stringy\\' => 8,
        ),
        'P' => 
        array (
            'Psr\\Http\\Message\\' => 17,
            'PHPMailer\\PHPMailer\\' => 20,
        ),
        'J' => 
        array (
            'JmesPath\\' => 9,
        ),
        'G' => 
        array (
            'GuzzleHttp\\Psr7\\' => 16,
            'GuzzleHttp\\Promise\\' => 19,
            'GuzzleHttp\\' => 11,
        ),
        'A' => 
        array (
            'AlibabaCloud\\Client\\' => 20,
            'AlibabaCloud\\' => 13,
            'Adbar\\' => 6,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'think\\composer\\' => 
        array (
            0 => __DIR__ . '/..' . '/topthink/think-installer/src',
        ),
        'think\\captcha\\' => 
        array (
            0 => __DIR__ . '/..' . '/topthink/think-captcha/src',
        ),
        'clagiordano\\weblibs\\configmanager\\' => 
        array (
            0 => __DIR__ . '/..' . '/clagiordano/weblibs-configmanager/src',
        ),
        'app\\' => 
        array (
            0 => __DIR__ . '/../..' . '/application',
        ),
        'Symfony\\Polyfill\\Mbstring\\' => 
        array (
            0 => __DIR__ . '/..' . '/symfony/polyfill-mbstring',
        ),
        'Stringy\\' => 
        array (
            0 => __DIR__ . '/..' . '/danielstjules/stringy/src',
        ),
        'Psr\\Http\\Message\\' => 
        array (
            0 => __DIR__ . '/..' . '/psr/http-message/src',
        ),
        'PHPMailer\\PHPMailer\\' => 
        array (
            0 => __DIR__ . '/..' . '/phpmailer/phpmailer/src',
        ),
        'JmesPath\\' => 
        array (
            0 => __DIR__ . '/..' . '/mtdowling/jmespath.php/src',
        ),
        'GuzzleHttp\\Psr7\\' => 
        array (
            0 => __DIR__ . '/..' . '/guzzlehttp/psr7/src',
        ),
        'GuzzleHttp\\Promise\\' => 
        array (
            0 => __DIR__ . '/..' . '/guzzlehttp/promises/src',
        ),
        'GuzzleHttp\\' => 
        array (
            0 => __DIR__ . '/..' . '/guzzlehttp/guzzle/src',
        ),
        'AlibabaCloud\\Client\\' => 
        array (
            0 => __DIR__ . '/..' . '/alibabacloud/client/src',
        ),
        'AlibabaCloud\\' => 
        array (
            0 => __DIR__ . '/..' . '/alibabacloud/sdk/src',
        ),
        'Adbar\\' => 
        array (
            0 => __DIR__ . '/..' . '/adbario/php-dot-notation/src',
        ),
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInitebb6ccacfc92d5643dd3c68f4ea3aa91::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInitebb6ccacfc92d5643dd3c68f4ea3aa91::$prefixDirsPsr4;

        }, null, ClassLoader::class);
    }
}
