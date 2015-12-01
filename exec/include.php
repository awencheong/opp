<?php
//auto load
namespace Mob\console;

include realpath(__DIR__) . "/consts.php";
include ADN_CONSOLE_ROOT . "/lib/vendor/autoload.php";
include ADN_CONSOLE_ROOT . "/lib/functions.php";

// load adn_net configuration
include ADN_CONSOLE_ROOT . "/config/adn_net/consts.php";
include ADN_CONSOLE_ROOT . "/config/adn_net/functions.php";
include ADN_CONSOLE_ROOT . "/config/adn_net/config.php";
use \Mob\console\config\adn_net;

// classes
use Mob\console\lib\SimpleBeanstalk;
use \Mob\console\lib\SimplePdo;
use \Mob\console\lib\Log;

// functions
php_ini(ADN_CONSOLE_PHP_INI);

spl_autoload_register(
    function ($class) {
        $dir = __DIR__;
        $pre_spacename = 'Mob\\console\\';
        $class = trim($class, "\\");
        if (($pos = strpos($class, $pre_spacename)) === 0) {
            $file = $dir . DIRECTORY_SEPARATOR .
                 str_replace("\\", DIRECTORY_SEPARATOR, substr($class, strlen($pre_spacename))) . ".php";
            if (file_exists($file)) {
                require $file;
            }
        }
    });

/*
 * $classLoader = new \SplClassLoader('Mob\console', __DIR__);
 * $classLoader->register();
 */
class Io
{

    public static function config($key)
    {
        if (!Config::isInit()) {
            $conf = array();
            $mongo = self::mongo();
            foreach ($mongo->config->find() as $c) {
                $conf[$c['key']] = $c['value'];
            }
            Config::init($conf);
        }
        return Config::get($key);
    }

    public static function log($type, $str)
    {
        $log = new Log(ADN_LOG_ROOT . "/console/$type/{Y-m-d}.log", "{Y-m-d H:i:s}\t");
        $log->log($str);
    }

    public static function log_mysql($sql, $return)
    {
        $log = new Log(ADN_LOG_ROOT . "/console/mysql/{Y-m-d}.log", "{Y-m-d H:i:s}\t");
        $line = array(
            $sql,
            json_encode($return)
        );
        $log->logArr($line, "\t");
    }

    public static function log_mongo($table, $op, $condition, $update = null, $option = null)
    {
        $log = new Log(ADN_LOG_ROOT . "/console/mongo/{$table}/{Y-m-d}.log", "{Y-m-d H:i:s}\t");
        $line = array(
            $op,
            json_encode($condition),
            json_encode($update),
            json_encode($option)
        );
        $log->logArr($line, "\t");
    }

    public static function log_beanstalk($str)
    {
        $log = new Log(ADN_LOG_ROOT . "/console/beanstalk/{Y-m-d}.log", "{Y-m-d H:i:s}\t");
        $log->log($str);
    }

    public static function log_error($type, $str)
    {
        $log = new Log(ADN_LOG_ROOT . "/error.log", "{Y-m-d H:i:s}\t");
        $log->log($type . "\t" . $str);
    }

    private static $mongo_dbs = array();

    public static function master_mongo()
    {
        $conf = adn_net\config('MONGODB_MASTER');
        $timeout = isset($conf['timeout']) ? intval($conf['timeout']) : 100;
        return self::mongo_db($conf['host'], $conf['port'], 'new_adn', $timeout);
    }

    private static function mongo_db($host, $port, $dbname, $timeout, $trytimes = 1)
    {
        $server = "mongodb://{$host}:{$port}";
        if (!isset(self::$mongo_dbs[$server])) {
            for ($i = $trytimes; $i > 0;) {
                $i --;
                try {
                    self::$mongo_dbs[$server] = new \MongoClient($server, 
                        array(
                            'connectTimeoutMS' => $timeout
                        ));
                    break;
                } catch (Exception $e) {
                    if ($i == 0) {
                        throw $e;
                    }
                }
            }
        }
        return self::$mongo_dbs[$server]->$dbname;
    }

    public static function mongo()
    {
        $conf = adn_net\config('MONGODB_SLAVE');
        $timeout = isset($conf['timeout']) ? intval($conf['timeout']) : 100;
        return self::mongo_db($conf['host'], $conf['port'], 'new_adn', $timeout);
    }

    /*
     * array(
     * 'sync' => array(
     * 'conn' => $conn,
     * 'tube' => 'new_adn_data'
     * )
     * )
     */
    private static $queues = array();

    const BEANSTAKL_RECONN = true;

    const BEANSTAKL_NOT_RECONN = false;

    public static function beanstalk($name = 'sync', $reconn = self::BEANSTAKL_NOT_RECONN)
    {
        $tube = 'new_adn_data';
        $queueConf = adn_net\config('queue');
        if ($reconn || !isset(self::$queues[$name])) {
            self::$queues[$name] = new SimpleBeanstalk($queueConf[$name]['host'], $queueConf[$name]['port']);
            self::$queues[$name]->setTube($tube);
        }
        return self::$queues[$name];
    }

    public static function sync($str)
    {
        if (is_array($str)) {
            $str = json_encode($str);
        }
        $queue = self::beanstalk();
        $queue->watch($queue->getTube())
            ->put($str);
    }

    public static function mysql($db = '')
    {
        $mysql_conf = adn_net\config('MYSQL');
        $dsn = "mysql:host={$mysql_conf['host']};dbname={$mysql_conf["database"]};port={$mysql_conf['port']}";
        return new SimplePdo($dsn, $mysql_conf['username'], $mysql_conf['password']);
    }

    public static function redshift()
    {
        $redshift_conf = adn_net\config('REDSHIFT');
        $dsn = "pgsql:host={$redshift_conf['host']};dbname={$redshift_conf["database"]};port={$redshift_conf['port']}";
        return new SimplePdo($dsn, $redshift_conf['username'], $redshift_conf['password']);
    }

    public static function email()
    {
        return new Email();
    }
}

class Config
{

    private static $data = null;

    public static function isInit()
    {
        return self::$data;
    }

    public static function init(array $data)
    {
        self::$data = $data;
    }

    public static function get($key)
    {
        if (!isset(self::$data[$key])) {
            return false;
        } else {
            return self::$data[$key];
        }
    }
}

class Email extends \PHPMailer
{

    public function addReceiver($addr)
    {
        $this->AddAddress($addr, "");
    }

    public function __construct()
    {
        parent::__construct();
        
        // $mail->SMTPDebug = 3; // Enable verbose debug output
        $this->SMTPKeepAlive = true;
        $this->isSMTP(); // Set mailer to use SMTP
        $this->Host = 'smtp.126.com'; // Specify main and backup SMTP servers
        $this->SMTPAuth = true; // Enable SMTP authentication
        $this->Username = 'mobvista@126.com'; // SMTP username
        $this->Password = 'mobvista123'; // SMTP password
        $this->SMTPSecure = 'tls'; // Enable TLS encryption, `ssl` also accepted
        $this->Port = 25; // TCP port to connect to
        

        $this->From = 'mobvista@126.com';
        $this->FromName = 'adn_net_mail';
        $this->isHTML(true); // Set email format to HTML
    }
}

