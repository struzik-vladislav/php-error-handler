<?php

namespace Struzik\ErrorHandler;

use Struzik\ErrorHandler\Processor\ProcessorInterface;
use Struzik\ErrorHandler\Exception\LogicException;

class ErrorHandlerTest extends \PHPUnit_Framework_TestCase
{
    public function testSetValidProcessorsStack()
    {
        $validStack = [
            $this->createMock(ProcessorInterface::class),
            $this->createMock(ProcessorInterface::class),
        ];
        $errorHandler = new ErrorHandler();
        $this->assertSame($errorHandler, $errorHandler->setProcessorsStack($validStack));
    }

    public function testInvalidTypeOfProcessor()
    {
        if (version_compare(phpversion(), '7.0.0') >= 0) {
            $this->expectException(\TypeError::class);
        } else {
            $this->expectException(\PHPUnit_Framework_Error::class);
        }

        $invalidStack = [
            new \stdClass(),
            $this->createMock(ProcessorInterface::class),
        ];
        $errorHandler = new ErrorHandler();
        $errorHandler->setProcessorsStack($invalidStack);

        $errorHandler->setProcessorsStack(new \stdClass());
    }

    public function testInvalidTypeOfProcessorsStack()
    {
        if (version_compare(phpversion(), '7.0.0') >= 0) {
            $this->expectException(\TypeError::class);
        } else {
            $this->expectException(\PHPUnit_Framework_Error::class);
        }

        $errorHandler = new ErrorHandler();
        $errorHandler->setProcessorsStack(new \stdClass());
    }

    public function testGetProcessorsStack()
    {
        $stack = [
            $this->createMock(ProcessorInterface::class),
            $this->createMock(ProcessorInterface::class),
        ];
        $errorHandler = new ErrorHandler();
        $errorHandler->setProcessorsStack($stack);

        $this->assertEquals($stack, $errorHandler->getProcessorsStack());
    }

    public function testPushProcessor()
    {
        $firstProcessor = $this->createMock(ProcessorInterface::class);
        $secondProcessor = $this->createMock(ProcessorInterface::class);
        $errorHandler = new ErrorHandler();
        $expectedStack = [];

        array_unshift($expectedStack, $firstProcessor);
        $this->assertSame($errorHandler, $errorHandler->pushProcessor($firstProcessor));
        $this->assertEquals($expectedStack, $errorHandler->getProcessorsStack());

        array_unshift($expectedStack, $secondProcessor);
        $this->assertSame($errorHandler, $errorHandler->pushProcessor($secondProcessor));
        $this->assertEquals($expectedStack, $errorHandler->getProcessorsStack());
    }

    public function testPopProcessor()
    {
        $firstProcessor = $this->createMock(ProcessorInterface::class);
        $secondProcessor = $this->createMock(ProcessorInterface::class);

        $stack = [
            $firstProcessor,
            $secondProcessor,
        ];
        $errorHandler = new ErrorHandler();
        $errorHandler->setProcessorsStack($stack);

        $this->assertSame($firstProcessor, $errorHandler->popProcessor());
        $this->assertSame($secondProcessor, $errorHandler->popProcessor());

        $this->expectException(LogicException::class);
        $errorHandler->popProcessor();
    }

    public function testExcuteHandleOnEmptyStack()
    {
        $errorHandler = new ErrorHandler();
        $this->assertEquals(null, $errorHandler->handle(E_USER_NOTICE, 'Dummy error', __FILE__, __LINE__));
    }

    public function testExecuteHandleOnNonEmptyStack()
    {
        $firstProcessor = $this->createMock(ProcessorInterface::class);
        $firstProcessor->expects($this->once())
            ->method('getErrorTypes')
            ->will($this->returnValue(E_ALL));
        $firstProcessor->expects($this->once())
            ->method('handle');

        $secondProcessor = $this->createMock(ProcessorInterface::class);
        $secondProcessor->expects($this->once())
            ->method('getErrorTypes')
            ->will($this->returnValue(E_ALL & ~E_USER_NOTICE));
        $secondProcessor->expects($this->never())
            ->method('handle');

        $stack = [
            $firstProcessor,
            $secondProcessor,
        ];

        $errorHandler = new ErrorHandler();
        $errorHandler->setProcessorsStack($stack);

        $this->assertEquals(null, $errorHandler->handle(E_USER_NOTICE, 'Dummy error', __FILE__, __LINE__));
    }
}
