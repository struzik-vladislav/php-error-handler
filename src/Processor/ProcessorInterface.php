<?php

namespace Struzik\ErrorHandler\Processor;

/**
 * Describe a processor instance.
 */
interface ProcessorInterface
{
    /**
     * Getting processable errors.
     *
     * @return int E_* error code
     */
    public function getErrorTypes();

    /**
     * Handle the error.
     *
     * @param int    $errno   level of the error raised
     * @param string $errstr  error message
     * @param string $errfile filename that the error was raised
     * @param int    $errline line number the error was raised
     *
     * @return mixed
     */
    public function handle($errno, $errstr, $errfile, $errline);
}
