<?php
namespace Fordebug;

use app\Cmd;

class CmdDebugMod
{

    public function analyze($name, $age = 12, array $detail = array())
    {
        return array(
            array(
                "name" => $name,
                "age" => $age
            ),
            $detail
        );
    }

    public function table(array $head, array $rows, $null = '-')
    {
        foreach ($rows as &$r) {
            $tmp = array();
            foreach ($head as $h) {
                if (isset($r[$h])) {
                    $tmp[] = $r[$h];
                } else {
                    $tmp[] = $null;
                }
            }
            $r = $tmp;
        }
        return array(
            'head' => $head,
            'body' => $rows
        );
    }
}

class CmdTest extends \PHPUnit_Framework_TestCase
{

    public function testBaseNamespace()
    {
        $arg = array(
            "./exec.php",
            "--CmdDebugMod/analyze",
            "awen",
            "12",
            '{"name":"awen2"}',
            "--/json_encode",
            "--/json_decode",
            '$1',
            true
        );
        $c = new Cmd($arg);
        $res = $c->exec("/Fordebug");
        $this->assertEquals($res, 
            array(
                array(
                    "name" => "awen",
                    "age" => 12
                ),
                array(
                    "name" => "awen2"
                )
            ));
        
        $arg = array(
            "./exec.php",
            "--Fordebug/CmdDebugMod/analyze",
            "awen",
            "12",
            '{"name":"awen2"}',
            "--json_encode",
            "--json_decode",
            '$1',
            true
        );
        $c = new Cmd($arg);
        $res = $c->exec("/");
        $this->assertEquals($res, 
            array(
                array(
                    "name" => "awen",
                    "age" => 12
                ),
                array(
                    "name" => "awen2"
                )
            ));
    }

    public function testCmd()
    {
        $arg = array(
            "./exec.php",
            "--CmdDebugMod/analyze",
            "awen",
            "12",
            "@./abc.json",
            " --aaaa",
            "--CmdDebugMod/table",
            '["name", "age"]',
            '$1'
        );
        $c = new Cmd($arg);
        $this->assertEquals($c->script, "./exec.php");
        $this->assertEquals($c->cmds['CmdDebugMod/analyze'], 
            array(
                "awen",
                "12",
                "@./abc.json",
                " --aaaa"
            ));
        $this->assertEquals($c->cmds['CmdDebugMod/table'], 
            array(
                array(
                    "name",
                    "age"
                ),
                '$1'
            ));
        
        $arg = array(
            './exec.php',
            'abc',
            'efg',
            '--sdkDetect/Fortest',
            '--/json',
            '--/spec-func',
            123
        );
        $c->init($arg);
        $this->assertEquals($c->cmds['sdkDetect/Fortest'], array());
        $this->assertEquals($c->cmds['/json'], array());
        $this->assertEquals($c->cmds['/spec-func'], array(
            123
        ));
        $this->assertEquals(count($c->cmds), 3);
    }

    public function testMod()
    {
        $c = new Cmd();
        $c->cmds = array(
            'CmdDebugMod/analyze' => array(
                'awen',
                '12',
                array(
                    "name" => "awen1"
                )
            ),
            'CmdDebugMod/table' => array(
                array(
                    'name',
                    'age'
                ),
                '$1'
            )
        );
        $res = $c->exec("/Fordebug");
        $this->_testExec($res);
        
        $fpath = "./abc.json";
        $res = file_put_contents($fpath, '{"name":"awen1"}');
        $this->assertEquals(true, $res !== false);
        $c->cmds = array(
            'CmdDebugMod/analyze' => array(
                'awen',
                '12',
                '@./abc.json'
            ),
            'CmdDebugMod/table' => array(
                array(
                    'name',
                    'age'
                ),
                '$1'
            )
        );
        $res = $c->exec("Fordebug");
        $this->_testExec($res);
        unlink($fpath);
    }

    private function _testExec($res)
    {
        $this->assertEquals($res['head'], array(
            "name",
            "age"
        ));
        $this->assertEquals($res['body'][0], array(
            "awen",
            "12"
        ));
        $this->assertEquals($res['body'][1], array(
            "awen1",
            "-"
        ));
    }
}
