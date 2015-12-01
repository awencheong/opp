<?php
/**
 * Created by PhpStorm.
 * User: awencheong
 * Date: 2015/10/30
 * Time: 11:09
 */

$cfg = array(
    'MONGODB_MASTER' => array(
        'host' => '10.234.187.237',
        'port' => 27018
    ),
    'MONGODB_SLAVE' => array(
        'host' => '10.109.172.71',
        'port' => 27017
    ),
    'queue' => array(
        'sync' => array(
            'host' => '172.31.23.150',
            'port' => '11300',
        ),
    ),
    'MYSQL' => array(
        'host' => '10.234.187.237',
        'port' => 3306,
        'username' => 'root',
        'password' => 'TNKq6de8ttjGq4aB',
        'database' => 'mob_adn'
    ),
    'REDSHIFT' => array(
        'host' => 'adndataup.cj0ro5bbcusg.us-east-1.redshift.amazonaws.com',
        'port' => 5439,
        'database' => 'data',
        'username' => 'root',
        'password' => 'adn2015DATA',
    ),
);
return $cfg;