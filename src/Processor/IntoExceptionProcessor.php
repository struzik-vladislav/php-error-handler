<?php

namespace Struzik\ErrorHandler\Processor;

use Struzik\ErrorHandler\Exception;

/**
 * Convert the raised error into exception.
 */
class IntoExceptionProcessor implements ProcessorInterface
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
        $class = $this->getAssociatedClass($errno);

        throw new $class($errstr, 0, $errno, $errfile, $errline);
    }

    /**
     * Getting the exception class name associated with error code.
     *
     * @param int $errno level of the error raised
     *
     * @return string
     */
    private function getAssociatedClass($errno)
    {
        $associations = [
            E_WARNING => Exception\WarningException::class,
            E_NOTICE => Exception\NoticeException::class,
            E_USER_ERROR => Exception\UserErrorException::class,
            E_USER_WARNING => Exception\UserWarningException::class,
            E_USER_NOTICE => Exception\UserNoticeException::class,
            E_STRICT => Exception\StrictException::class,
            E_RECOVERABLE_ERROR => Exception\RecoverableErrorException::class,
            E_DEPRECATED => Exception\DeprecatedException::class,
            E_USER_DEPRECATED => Exception\UserDeprecatedException::class,
        ];

        if (isset($associations[$errno])) {
            return $associations[$errno];
        }

        return Exception\ErrorException::class;
    }
}
