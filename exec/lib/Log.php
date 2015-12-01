<?php
/**
 * Created by PhpStorm.
 * User: awencheong
 * Date: 2015/10/29
 * Time: 11:17
 */

namespace Mob\console\lib;

class Log
{
    private $path = null;
    private $preLine = null;
    private $errmsg = null;

    /*
     * @param   $pattern,  a string like :
     *              /data/path/{Ymd-H:i:s}/{PID}.log
     *          let's say current time is  20151029 10:00:00 and the current processor id is 9901,  then  it will be mapped to
     *              /data/path/20151029-10:00:00/9901.log
     *
     * @return  $path
     */
    public static function path($pattern)
    {
        $path = $pattern;
        preg_match_all('/({[YmdHis:\-_,.\s]+})/', $path, $match);

        if ($match) {
            $time = time();
            $replace = array();
            foreach ($match[0] as $m) {
                $replace[$m] = @Date(trim($m, "{}"), $time);
            }
            $path = strtr($path, $replace);
        }
        if (strpos($path, '{PID}')) {
            $path = strtr($path, array('{PID}'=>getmypid()));
        }
        return $path;
    }

    public function lastErrorMessage() {
        return $this->errmsg;
    }

    private function error($errmsg) {
        $this->errmsg = $errmsg;
        $this->path = null;
        $this->preLine = null;
    }

    /*
     * @param   $path, path patter,  see definition of  Log::path()
     * @param   $preLine, see definition of Log::path()
     */
    public function __construct($path, $preLine = '[{Y-m-d H:i:s}]')
    {
        $this->path = self::path($path);
        if (!file_exists($this->path)) {
            $dirname = dirname($this->path);
            if (!is_dir($dirname)) {
                if (false === @mkdir($dirname, 0777, true)) {
                    // clean out, , make sure this log is not writable
                    $last_err = error_get_last();
                    $err = $last_err['message']."; failed to mkdir [$dirname]";
                    trigger_error ($err, E_USER_NOTICE );
                    return $this->error($err);
                }
            }
            if (!is_writable($dirname)) {
                // clean out, , make sure this log is not writable
                $err = "Dir not able to write [$dirname]";
                trigger_error ($err, E_USER_NOTICE );
                return $this->error($err);
            }
        }
        $this->preLine = $preLine;
    }

    public function log($str)
    {
        $bytes = 0;
        if ($this->path) {
            $preLine = '';
            if ($this->preLine) {
                $preLine = self::path($this->preLine);
            }
            if (($bytes = file_put_contents($this->path, $preLine . $str . "\n", FILE_APPEND)) === false) {
                return false;
            }
        }
        return $bytes;
    }

    public function logArr(array $line, $split = "\t")
    {
        return $this->log(implode($split, $line));
    }

    public function logJson($line)
    {
        return $this->log(json_encode($line));
    }
}