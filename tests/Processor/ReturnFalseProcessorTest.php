<?php

namespace Struzik\ErrorHandler\Processor;

class ResurnFalseProcessorTest extends \PHPUnit_Framework_TestCase
{
    public function testGetSetErrorTypes()
    {
        $processor = new ReturnFalseProcessor();
        $this->assertEquals(error_reporting(), $processor->getErrorTypes());

        $errorTypes = E_ALL & ~E_USER_ERROR;
        $processor->setErrorTypes($errorTypes);
        $this->assertEquals($errorTypes, $processor->getErrorTypes());
    }

    public function testHandle()
    {
        $processor = new ReturnFalseProcessor();
        $this->assertEquals(false, $processor->handle(E_USER_NOTICE, 'Dummy error', __FILE__, __LINE__));
    }
}
