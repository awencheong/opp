<?php
namespace Fordebug;

use Mob\console\lib\Doc;

class DocDebugMod
{

    public function analyze($name, $age = 12, array $detail = array())
    {}

    public function table(array $head, array $rows, $null = '-')
    {}
}

function justForTest($a, $b = "debug", array $c = array())
{
    return array(
        $a,
        $b,
        $c
    );
}

function nowWeGotAnotherFunc($a, $b = "debug", array $c = array())
{
    return array(
        $a,
        $b,
        $c
    );
}

class DocTest extends \PHPUnit_Framework_TestCase
{

    public function testHelpFunc()
    {
        $doc = new Doc();
        $res = $doc->helpFunc("ForTest");
        
        $this->assertEquals($res['fordebug\justfortest'][0]['name'], 'a');
        $this->assertEquals(isset($res['fordebug\justfortest'][0]['default']), false);
        $this->assertEquals($res['fordebug\justfortest'][0]['isArray'], false);
        
        $this->assertEquals($res['fordebug\justfortest'][1]['name'], 'b');
        $this->assertEquals($res['fordebug\justfortest'][1]['default'], 'debug');
        $this->assertEquals($res['fordebug\justfortest'][1]['isArray'], false);
        
        $this->assertEquals($res['fordebug\justfortest'][2]['name'], 'c');
        $this->assertEquals($res['fordebug\justfortest'][2]['default'], array());
        $this->assertEquals($res['fordebug\justfortest'][2]['isArray'], true);
        
        $res = $doc->helpFunc(array(
            "ForTest",
            "AnotherFunc"
        ));
        $this->assertEquals($res['fordebug\justfortest'][0]['name'], 'a');
        $this->assertEquals($res['fordebug\nowwegotanotherfunc'][0]['name'], 'a');
    }

    public function testHelpClass()
    {
        $doc = new Doc();
        $res = $doc->helpClass("DocDebug");
        
        $this->assertEquals($res['Fordebug\DocDebugMod']['analyze'][0]['name'], 'name');
        $this->assertEquals($res['Fordebug\DocDebugMod']['analyze'][1]['name'], 'age');
        $this->assertEquals($res['Fordebug\DocDebugMod']['analyze'][1]['default'], '12');
        $this->assertEquals($res['Fordebug\DocDebugMod']['analyze'][2]['name'], 'detail');
        $this->assertEquals($res['Fordebug\DocDebugMod']['analyze'][2]['default'], array());
        $this->assertEquals($res['Fordebug\DocDebugMod']['analyze'][2]['isArray'], true);
    }
}
