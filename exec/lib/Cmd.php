<?php
/**
 * Created by PhpStorm.
 * User: awencheong
 * Date: 2015/10/30
 * Time: 14:25
 */
namespace Mob\console\lib;

class Cmd
{
    private static $script_file_name = null;
    private static $usage = null;
    public static $params = array();

    /*
     *
     * 示例:
    Cmd::init(array(
        "campaign" => 'required',    //    (必要参数)

        "--all"=> null        // --option (可选参数, 不带子参数)

        "--campaign"=>array(  // --option (可选参数, 带子参数)
            0 => "campaign_id",
        )));

       ***  json 字符串会自动被转换为 array


     对应 cmdline:


     usage: script_file  campaign  [--all]  [--campaign <int>]

     */
    public static function init(array $usage, array $tmp_argv = array())
    {
        if ($tmp_argv) {
            $params = $tmp_argv;
        } else {
            global $argv;
            $params = $argv;
        }
        self::$script_file_name = $params[0];
        self::$usage = $usage;
        array_shift($params);

        $options = array();
        $required = array();
        $curr_option = null;
        $curr_option_num = 0;
        $curr_param_index = -1;
        while (!empty($params)) {
            $p = array_shift($params);
            if ($p == '--help') {
                self::halt('');
            }
            if (strpos($p, '--') === 0) {
                if (isset(self::$usage[$p])) {
                    $options[$p] = array();
                    $curr_option = $p;
                    $curr_option_num = 0;
                    $curr_param_index += 1;
                } else {
                    self::halt("unknown option $p");
                }

            } else if ($curr_option && is_array(self::$usage[$curr_option]) && count(self::$usage[$curr_option]) > $curr_option_num) {
                $options[$curr_option][] = self::autoJsonDecode($p);
                $curr_option_num += 1;

            } else {
                $curr_param_index += 1;
                $required[$curr_param_index] = self::autoJsonDecode($p);
            }
        }

        $p_index = -1;
        foreach (self::$usage as $name => $rule) {
            $p_index += 1;
            if (is_string($rule) && strtolower($rule) === 'required') {
                if (!isset($required[$p_index])) {
                    self::halt("$name not found in param[$p_index]");
                }
                self::$params[$name] = $required[$p_index];

            } else if (strpos($name, '--') === 0) {
                if (isset($options[$name])) {
                    if (count($options[$name]) > 0) {
                        self::$params[$name] = $options[$name];
                    } else {
                        self::$params[$name] = 1;
                    }
                }
            } else {
                self::halt("wrong param $name");
            }
        }
        return self::$params;
    }

    private static function autoJsonDecode($str)
    {
        if (json_decode($str, true) !== false && json_decode($str, true) !== null) {
            $str = json_decode($str, true);
        }
        return $str;
    }

    private static function halt($errmsg)
    {
        $usage_str = 'usage:' . self::$script_file_name;
        foreach (self::$usage as $name => $rule) {
            if (is_array($rule)) {
                $usage_str .= " [$name " . implode(" ", $rule) . "] ";
            } else if ($rule === 1) {
                $usage_str .= " [$name] ";
            } else if (strtolower($rule) === "required") {
                $usage_str .= " $name ";
            }
        }
        die("\n" . $usage_str . "\n\n\t\t" . $errmsg . "\n\n");
    }
}
