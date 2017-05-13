<?php

namespace Struzik\ErrorHandler;

use Struzik\ErrorHandler\Processor\ProcessorInterface;
use Struzik\ErrorHandler\Exception\LogicException;

/**
 * Handler of the raised errors.
 */
class ErrorHandler
{
    /**
     * @var array
     */
    private $processorsStack = [];

    /**
     * Error handler.
     *
     * @param int    $errno   level of the error raised
     * @param string $errstr  error message
     * @param string $errfile filename that the error was raised
     * @param int    $errline line number the error was raised
     *
     * @return mixed
     */
    public function handle($errno, $errstr, $errfile, $errline)
    {
        if (!$this->processorsStack) {
            return;
        }

        foreach ($this->processorsStack as $processor) {
            if (!($processor->getErrorTypes() & $errno)) {
                continue;
            }

            $lastResult = $processor->handle($errno, $errstr, $errfile, $errline);
        }

        return $lastResult;
    }

    /**
     * Enabling the error handler.
     *
     * @return mixed returned value from set_error_handler()
     */
    public function set()
    {
        return set_error_handler([$this, 'handle'], E_ALL);
    }

    /**
     * Restores the previous error handler function.
     *
     * @return bool returned value from restore_error_handler()
     */
    public function restore()
    {
        return restore_error_handler();
    }

    /**
     * Add processor in stack.
     *
     * @param ProcessorInterface $processor instance of processor
     *
     * @return self
     */
    public function pushProcessor(ProcessorInterface $processor)
    {
        array_unshift($this->processorsStack, $processor);

        return $this;
    }

    /**
     * Retrieving processor from the stack.
     *
     * @return ProcessorInterface | LogicException
     */
    public function popProcessor()
    {
        if (!$this->processorsStack) {
            throw new LogicException('You tried to pop from an empty processor stack.');
        }

        return array_shift($this->processorsStack);
    }

    /**
     * Setting the processors stack as array.
     *
     * @param ProcessorInterface[] $stack processors stack
     */
    public function setProcessorsStack(array $stack)
    {
        $this->processorsStack = [];
        foreach (array_reverse($stack) as $processor) {
            $this->pushProcessor($processor);
        }

        return $this;
    }

    /**
     * Getting the processors stack.
     *
     * @return array
     */
    public function getProcessorsStack()
    {
        return $this->processorsStack;
    }
}
