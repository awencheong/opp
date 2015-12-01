<?php
/**
 * Created by PhpStorm.
 * User: awencheong
 * Date: 2015/9/15
 * Time: 16:34
 */
namespace Mob\console\lib;

class   Curl
{
    private $error = '';
    public function lastError() {
        return $this->error;
    }

    public function post(
        $url,
        $post = array(),
        array $headers = array('content-type:text/html;charset=utf8')
    )
    {
        foreach ($post as $name => $value) {
            if (is_string($value) && preg_match('/^@(.*)$/', $value, $match)) {
                if (class_exists('\CURLFile') && file_exists($match[1])) {
                    $post[$name] = new \CURLFile(realpath($match[1]));
                }
            }
        }
        foreach ($headers as $h) {
            header($h);
        }
        $ch = curl_init($url);
        //加@符号curl就会把它当成是文件上传处理
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
        $result = curl_exec($ch);
        if ($result === false) {
            $this->error = curl_error($ch);
        }
        curl_close($ch);
        return $result;
    }
}