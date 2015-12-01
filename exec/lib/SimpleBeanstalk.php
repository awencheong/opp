<?php
/**
 * Created by PhpStorm.
 * User: awencheong
 * Date: 2015/10/30
 * Time: 15:40
 */
namespace	Mob\console\lib;

use Pheanstalk\Pheanstalk;

class SimpleBeanstalk extends Pheanstalk
{
    private $tube;

    public function setTube($tube)
    {
        $this->tube = $tube;
    }

    public function getTube()
    {
        return $this->tube;
    }
}