<?php
/**
 * Created by PhpStorm.
 * User: awencheong
 * Date: 2015/10/13
 * Time: 15:00
 */
namespace Mob\console;

function htmlspecialchars_decode($str)
{
    return \htmlspecialchars_decode($str, ENT_QUOTES);
}

function diff($val1, $val2)
{
    if (is_array($val1) && is_array($val2)) {
        if (count($val1) != count($val2)) return false;
        foreach($val1 as $k => $v) {
            if (!in_array($v, $val2)) return false;
        }
        return true;
    } else {
        return $val1 == $val2;
    }
}

function diffStrict($val1, $val2)
{
    if (is_array($val1) && is_array($val2)) {
        if (count($val1) != count($val2)) {
            return false;
        }
        $keys1 = array_keys($val1);
        $keys2 = array_keys($val2);
        foreach ($keys1 as $k) {
            if (!in_array($k, $keys2, true) || !in_array($val1[$k], $val2, true)) {
                return false;
            }
        }
        return true;
    } else {
        return $val1 === $val2;
    }
}

function compareStrict($val1, $val2, $diff)
{

}


function explodeComm($str)
{
    $rule = trim($str, ',');
    $rule = explode(',', $rule);
    if (!$rule) {
        $rule = array();
    }
    return $rule;
}


function php_ini($config_file)
{
    foreach (get_config($config_file) as $name => $val) {
        ini_set($name, $val);
        if ($val != ini_get($name)) {
            die("failed to set config [$name]=>$val  from  file $config_file\n");
        }
    }
    return;
}

function get_config($config_file)
{
    $ini_config = array();
    if (file_exists($config_file) && ($lines = array_filter(explode("\n", file_get_contents($config_file))))) {
        foreach ($lines as $l) {
            if ($l[0] != '#') {
                $sp = strpos($l, "=");
                $name = trim(substr($l, 0, $sp));
                $value = trim(substr($l, $sp+1));
                if ($name !== '' && $value !== '') {
                    $ini_config[$name] = $value;
                }
            }
        }
    }
    return $ini_config;
}


function get($arr, $key, $default = '') {
    return isset($arr[$key])? $arr[$key]: $default;
}

function get1($arr, $key, $function = 'trim') {
    $value = isset($arr[$key])? $arr[$key]: '';
    $function && $value = $function($value);
    return $value;
}

function getIosVersionCode($versionName) {
    $version = explode(".", $versionName);
    switch (count($version)) {
        case 1:
            $version[] = "0";
            $version[] = "0";
            break;
        case 2:
            $version[] = "0";
            break;
        case 3:
            break;
    }
    $versionCode = 0;
    foreach ($version as $v) {
        $versionCode = $versionCode * 100 + (int)$v;
    }
    return $versionCode;
}

function getAndroidVersionCode($versionName) {
    // http://developer.android.com/guide/topics/manifest/uses-sdk-element.html
    $versionConf = array(
        '6.0' => 23,
        '5.1' => 22,
        '5.0' => 21,
        '5' => 21,
        '4.4' => 19,
        '4.3' => 18,
        '4.2' => 17,
        '4.1' => 16,
        '4.0.4' => 15,
        '4.0.3' => 15,
        '4.0' => 14,
        '4' => 14,
        '3.2' => 13,
        '3.1' => 12,
        '3.0' => 11,
        '3' => 11,
        '2.3.4' => 10,
        '2.3.3' => 10,
        '2.3' => 9,
        '2.2' => 8,
        '2.1' => 7,
        '2.0.1' => 6,
        '2.0' => 5,
        '2' => 5,
        '1.6' => 4,
        '1.5' => 3,
        '1.1' => 2,
        '1.0' => 1,
        '1' => 1
    );

    if (isset($versionConf[$versionName])) {
        return $versionConf[$versionName];
    }

    $arr = explode('.', $versionName);
    if (!isset($arr[0]) || !isset($arr[1])) return 0;

    $versionName = $arr[0] . '.' . $arr[1];
    if (isset($versionConf[$versionName])) {
        return $versionConf[$versionName];
    }
    if (isset($versionConf[$arr[0]])) {
        return $versionConf[$arr[0]];
    }
    return 0;
}
