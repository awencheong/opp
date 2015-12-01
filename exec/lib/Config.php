<?php
/**
 * Created by PhpStorm.
 * User: awencheong
 * Date: 2015/11/5
 * Time: 11:30
 */

namespace Mob\console\lib;

class Config {
    private $cfg = array();
    private static $objs = array();

    private function __construct(array $cfg) {
        $this->cfg = $cfg;
    }

    /*
     * load php configuration file
     *
     * @param   $file,   php file , which return an array
     *
     *      example:
     *
     *          cfg.php
     *          <?php
     *              return array(
     *                  'mysql'=>array(
     *                      'host'=>'localhost'
     *                      'user'=>'root'
     *                      'passwd'=>'123'
     *                  )
     *              )
     *          ?>
     *
     *      Config::load("./cfg.php")->get('mysql')    will return an array :    ('host'=>'localhost', 'user'=>'root', 'passwd'=>'123')
     *      Config::load("./cfg.php")->get('pgmysql')  will return fasle
     */
    public static function load($file)
    {
        if (!isset(self::$objs[$file])) {
            $cfg = null;
            if (file_exists($file) && is_readable($file)) {
                $cfg = require($file);
            }
            if (!$cfg || !is_array($cfg)) {
                $cfg = array();
            }
            self::$objs[$file] = new self($cfg);
        }
        return self::$objs[$file];
    }

    public function get($key)
    {
        if (!isset($this->cfg[$key])) {
            return false;
        }
        return $this->cfg[$key];
    }

    public function exists($key)
    {
        return isset($this->cfg[$key]);
    }

}