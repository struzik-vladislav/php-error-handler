<?php

namespace Struzik\ErrorHandler\Processor;

/**
 * Processor for executing native error handler.
 * Must be placed at the bottom of the stack.
 */
class ReturnFalseProcessor implements ProcessorInterface
{
    /**
     * @var int
     */
    private $errorTypes;

    /**
     * Initializes a new instance of the processor.
     */
    public function __construct()
    {
        $this->errorTypes = error_reporting();
    }

    /**
     * Setting processable errors.
     *
     * @param int $errorTypes E_* error code
     *
     * @return self
     */
    public function setErrorTypes($errorTypes)
    {
        $this->errorTypes = $errorTypes;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getErrorTypes()
    {
        return $this->errorTypes;
    }

    /**
     * {@inheritdoc}
     */
    public function handle($errno, $errstr, $errfile, $errline)
    {
        return false;
    }
}
