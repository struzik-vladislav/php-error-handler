# PHP Error Handler [![Travis](https://img.shields.io/travis/struzik-vladislav/php-error-handler.svg)](https://travis-ci.org/struzik-vladislav/php-error-handler)

Handling PHP errors in various processors.

Types of errors that can be handled according to [set_error_handler](http://php.net/manual/en/function.set-error-handler.php):
* E_STRICT
* E_RECOVERABLE_ERROR
* E_NOTICE
* E_WARNING
* E_DEPRECATED
* E_USER_ERROR
* E_USER_NOTICE
* E_USER_WARNING
* E_USER_DEPRECATED

## Processors

### ReturnFalseProcessor
The processor for enabling native PHP handler after custom.

### LoggerProcessor
The processor for writing to the PSR-3 compatible logger like [Monolog](https://github.com/Seldaek/monolog).

### IntoExceptionProcessor
The processor for converting errors to exceptions. The basic type of throwing exception is `Struzik\ErrorHandler\Exception\ErrorException`.


## Usage

```php
<?php

use Struzik\ErrorHandler\ErrorHandler;
use Struzik\ErrorHandler\Processor\LoggerProcessor;
use Struzik\ErrorHandler\Processor\IntoExceptionProcessor;
use Struzik\ErrorHandler\Exception\ErrorException;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;

$logger = new Logger('ErrorHandler DEMO');
$logger->pushHandler(new StreamHandler('php://output', Logger::DEBUG));

$errorHandler = new ErrorHandler();
$errorHandler->pushProcessor(new IntoExceptionProcessor())
    ->pushProcessor(new LoggerProcessor($logger));

try {
    $errorHandler->set();
    trigger_error('Dummy error', E_USER_NOTICE);
    $errorHandler->restore();
} catch (ErrorException $e) {
    echo $e;
}

/*
[2017-05-13 21:42:46] ErrorHandler DEMO.NOTICE: Dummy error in /srv/php-error-handler/example.php:21 {"backtrace":"#0 /srv/php-error-handler/src/ErrorHandler.php(39): Struzik\\ErrorHandler\\Processor\\LoggerProcessor->handle(1024, 'Dummy error', '/srv/php-error-...', 21)\n#1 [internal function]: Struzik\\ErrorHandler\\ErrorHandler->handle(1024, 'Dummy error', '/srv/php-error-...', 21, Array)\n#2 /srv/php-error-handler/example.php(21): trigger_error('Dummy error', 1024)\n#3 {main}"} []

Struzik\ErrorHandler\Exception\UserNoticeException: Dummy error in /srv/php-error-handler/example.php:21
Stack trace:
#0 /srv/php-error-handler/src/ErrorHandler.php(39): Struzik\ErrorHandler\Processor\IntoExceptionProcessor->handle(1024, 'Dummy error', '/srv/php-error-...', 21)
#1 [internal function]: Struzik\ErrorHandler\ErrorHandler->handle(1024, 'Dummy error', '/srv/php-error-...', 21, Array)
#2 /srv/php-error-handler/example.php(21): trigger_error('Dummy error', 1024)
*/
```
