<?php

namespace Struzik\ErrorHandler\Processor;

use Psr\Log\NullLogger;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;

class LoggerProcessorTest extends \PHPUnit_Framework_TestCase
{
    public function testGetSetErrorTypes()
    {
        $processor = new LoggerProcessor(new NullLogger());
        $this->assertEquals(error_reporting(), $processor->getErrorTypes());

        $errorTypes = E_ALL & ~E_USER_ERROR;
        $processor->setErrorTypes($errorTypes);
        $this->assertEquals($errorTypes, $processor->getErrorTypes());
    }

    public function testNonPSR3Logger()
    {
        if (version_compare(phpversion(), '7.0.0') >= 0) {
            $this->expectException(\TypeError::class);
        } else {
            $this->expectException(\PHPUnit_Framework_Error::class);
        }

        $processor = new LoggerProcessor(new \stdClass());
    }

    public function testHandle()
    {
        $this->expectOutputRegex('/^.+/u');

        $logger = new Logger('LoggerProcessor TEST');
        $logger->pushHandler(new StreamHandler('php://output', Logger::DEBUG));

        $processor = new LoggerProcessor($logger);
        $processor->handle(E_USER_NOTICE, 'Dummy error', __FILE__, __LINE__);
    }
}
