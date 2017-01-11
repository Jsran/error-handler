ErrorHandler
============

Error handling php. Can use any logger psr-3 for logging errors.

Installation
------------
```
composer require dmitry-suffi/error-handler
```

connection example
------------------

```php

$handler = new \suffi\ErrorHandler\ErrorHandler();

set_error_handler([$handler, 'errorHandler']);
set_exception_handler([$handler, 'exceptionHandler']);

```

Setting
-------

Debug mode. Displays error details.
```php

$handler->debug = true;

```

Error Logging. Can use any logger psr-3 for logging errors.
```php

$handler->writeLog = true;

$handler->logger = $logger;

```

Writing to the log information
```php

$handler->$debugLog = true;

```

When you inherit from the class, you can change the way display messages to the user fault conditioning.
```php
    //Page with an error message
    protected function htmlError(string $errstr)
    {
        return $errstr;
    }

    //Report an error for ajax-request
    protected function jsonError(string $errstr)
    {
        return json_encode(['error' => $errstr]);
    }
```