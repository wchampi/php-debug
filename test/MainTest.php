<?php

use Ch\Debug\Debug;
use Ch\Debug\Dumper\SlackWebhookDumper;

class MainTest extends PHPUnit_Framework_TestCase
{
    public function testEnvDebug()
    {
        putenv('DEBUG=*');

        $object = new stdClass();
        $object->a = 1;
        $object->b = 'test';
        $object->c = true;
        $result = Debug::_($object, 'tag');
        $this->assertTrue($result);

        $tag = 'string to filter';
        $pos = 1;
        $result = Debug::_($pos, $tag);

        $this->assertTrue($result);
        putenv('DEBUG=');
    }

    public function testGetDebug()
    {
        $_GET['DEBUG'] = '*';

        $varGet = 'GET';
        $result = Debug::_($varGet);

        $this->assertTrue($result);
        $_GET['DEBUG'] = '';
    }

    public function testCookieDebug()
    {
        $_COOKIE['DEBUG'] = '*';

        $varCookie = 'COOKIE';
        $result = Debug::_($varCookie);

        $this->assertTrue($result);
    }

}