<?php

namespace Struzik\ErrorHandler\Processor;

use Struzik\ErrorHandler\Exception\ErrorException;

class IntoExceptionProcessorTest extends \PHPUnit_Framework_TestCase
{
    public function testGetSetErrorTypes()
    {
        $processor = new IntoExceptionProcessor();
        $this->assertEquals(error_reporting(), $processor->getErrorTypes());

        $errorTypes = E_ALL & ~E_USER_ERROR;
        $processor->setErrorTypes($errorTypes);
        $this->assertEquals($errorTypes, $processor->getErrorTypes());
    }

    public function testHandle()
    {
        $this->expectException(ErrorException::class);

        $processor = new IntoExceptionProcessor();
        $processor->handle(E_USER_NOTICE, 'Dummy error', __FILE__, __LINE__);
    }
}
