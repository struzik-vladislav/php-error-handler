<?php

namespace Struzik\ErrorHandler;

use Struzik\ErrorHandler\Processor\LoggerProcessor;
use Struzik\ErrorHandler\Processor\ProcessorInterface;
use Struzik\ErrorHandler\Processor\ReturnFalseProcessor;
use Struzik\ErrorHandler\Exception\LogicException;
use Psr\Log\NullLogger;

class ErrorHandlerTest extends \PHPUnit_Framework_TestCase
{
    public function testSetValidProcessorsStack()
    {
        $validStack = [
            new LoggerProcessor(new NullLogger()),
            new ReturnFalseProcessor(),
        ];
        $errorHandler = new ErrorHandler();
        $this->assertSame($errorHandler, $errorHandler->setProcessorsStack($validStack));
    }

    public function testInvalidTypeOfProcessor()
    {
        $this->expectException(\TypeError::class);

        $invalidStack = [
            new \stdClass(),
            new ReturnFalseProcessor(),
        ];
        $errorHandler = new ErrorHandler();
        $errorHandler->setProcessorsStack($invalidStack);

        $errorHandler->setProcessorsStack(new \stdClass());
    }

    public function testInvalidTypeOfProcessorsStack()
    {
        $this->expectException(\TypeError::class);

        $errorHandler = new ErrorHandler();
        $errorHandler->setProcessorsStack(new \stdClass());
    }

    public function testGetProcessorsStack()
    {
        $stack = [
            new LoggerProcessor(new NullLogger()),
            new ReturnFalseProcessor(),
        ];
        $errorHandler = new ErrorHandler();
        $errorHandler->setProcessorsStack($stack);

        $this->assertEquals($stack, $errorHandler->getProcessorsStack());
    }

    public function testPushProcessor()
    {
        $falseProcessor = new ReturnFalseProcessor();
        $loggerProcessor = new LoggerProcessor(new NullLogger());
        $errorHandler = new ErrorHandler();
        $expectedStack = [];

        array_unshift($expectedStack, $falseProcessor);
        $this->assertSame($errorHandler, $errorHandler->pushProcessor($falseProcessor));
        $this->assertEquals($expectedStack, $errorHandler->getProcessorsStack());

        array_unshift($expectedStack, $loggerProcessor);
        $this->assertSame($errorHandler, $errorHandler->pushProcessor($loggerProcessor));
        $this->assertEquals($expectedStack, $errorHandler->getProcessorsStack());
    }

    public function testPopProcessor()
    {
        $loggerProcessor = new LoggerProcessor(new NullLogger());
        $falseProcessor = new ReturnFalseProcessor();

        $stack = [
            $loggerProcessor,
            $falseProcessor,
        ];
        $errorHandler = new ErrorHandler();
        $errorHandler->setProcessorsStack($stack);

        $this->assertSame($loggerProcessor, $errorHandler->popProcessor());
        $this->assertSame($falseProcessor, $errorHandler->popProcessor());

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
