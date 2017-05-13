<?php

namespace Struzik\ErrorHandler\Processor;

use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;

/**
 * Processing the raised error in the PSR-3 compatible logger.
 */
class LoggerProcessor implements ProcessorInterface
{
    /**
     * @var int
     */
    private $errorTypes;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * Initializes a new instance of the processor.
     *
     * @param LoggerInterface $logger instance of the logger
     */
    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
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
        $level = $this->getAssociatedLogLevel($errno);

        return $this->logger->log(
            $level,
            sprintf('%s in %s:%s', $errstr, $errfile, $errline),
            [
                'backtrace' => (new \Exception(''))->getTraceAsString(),
            ]
        );
    }

    /**
     * Getting the log level constant associated with error code.
     *
     * @param int $errno level of the error raised
     *
     * @return string
     */
    private function getAssociatedLogLevel($errno)
    {
        $associations = [
            E_WARNING => LogLevel::WARNING,
            E_NOTICE => LogLevel::NOTICE,
            E_USER_ERROR => LogLevel::CRITICAL,
            E_USER_WARNING => LogLevel::WARNING,
            E_USER_NOTICE => LogLevel::NOTICE,
            E_STRICT => LogLevel::WARNING,
            E_RECOVERABLE_ERROR => LogLevel::CRITICAL,
            E_DEPRECATED => LogLevel::INFO,
            E_USER_DEPRECATED => LogLevel::INFO,
        ];

        if (isset($associations[$errno])) {
            return $associations[$errno];
        }

        return LogLevel::CRITICAL;
    }
}
