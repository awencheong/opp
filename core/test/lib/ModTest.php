<?php
namespace Fordebug;

use app\Mod;

class DebugMod
{

    public function test($b, $c, $d)
    {
        return array(
            $b,
            $c,
            $d
        );
    }

    public function arr(array $data)
    {
        foreach ($data as $i => $v) {
            $data[$i] = '_' . $v;
        }
        return array_flip($data);
    }
}

function debugModTest($b, $c, $d)
{
    return array(
        $b,
        $c,
        $d
    );
}

class ModTest extends \PHPUnit_Framework_TestCase
{

    public function testSequence()
    {
        $seq = array(
            '\\Fordebug\\debugModTest' => array(
                'a' => 1,
                'c' => 2,
                'b' => 3
            ),
            '\\Fordebug\\DebugMod\\arr' => array()
        );
        $mod = new Mod();
        $res = $mod->callSequence($seq);
        $this->assertEquals($res['_3'], '0');
        $this->assertEquals($res['_2'], '1');
        $this->assertEquals($res['_'], '2');
    }

    public function testMapParamNames()
    {
        $mod = new Mod();
        $params = array(
            'a' => 1,
            'c' => 2,
            'b' => 3
        );
        $res = $mod->call('\\Fordebug\\DebugMod\\test', $params, false);
        $this->assertEquals($res[0], '1');
        $this->assertEquals($res[1], '2');
        $this->assertEquals($res[2], '3');
        
        $mod = new Mod();
        $res = $mod->call('\\Fordebug\\DebugMod\\test', $params);
        $this->assertEquals($res[0], '3');
        $this->assertEquals($res[1], '2');
        $this->assertEquals($res[2], null);
        
        $mod = new Mod();
        $res = $mod->call('\\Fordebug\\debugModTest', $params);
        $this->assertEquals($res[0], '3');
        $this->assertEquals($res[1], '2');
        $this->assertEquals($res[2], null);
    }
}
