<?php
/**
 * Created by PhpStorm.
 * User: awencheong
 * Date: 2015/10/30
 * Time: 10:53
 */
namespace Mob\console\config\adn_net;

use Mob\console\lib\Config;

function config($key = null) {
    $local_file = ADN_CONSOLE_LOCAL_CONFIG;
    $release_file = realpath(__DIR__) . "/config.example.php";

    if (file_exists($local_file)) {
        return Config::load($local_file)->get($key);
    } else if (file_exists($release_file)) {
        return Config::load($release_file)->get($key);
    } else {
        return false;
    }
}
