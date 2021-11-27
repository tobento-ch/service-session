# Session Service

Session support for any PHP applications.

## Table of Contents

- [Getting started](#getting-started)
	- [Requirements](#requirements)
	- [Highlights](#highlights)
- [Documentation](#documentation)
	- [Create Session](#create-session)
    - [Save Handlers](#save-handlers)
        - [PDO MySql Save Handler](#pdo-mysql-save-handler)
        - [Null Save Handler](#null-save-handler)
    - [Validation](#validation)
        - [Remote Addr Validation](#remote-addr-validation)
        - [Http User Agent Validation](#http-user-agent-validation)
        - [Stack Validation](#stack-validation)
        - [Custom Validation](#custom-validation)
    - [Start Session](#start-session)
    - [Interacting With Data](#interacting-with-data)
    - [Interacting With Flash Data](#interacting-with-flash-data)
    - [Miscellaneous](#miscellaneous)
    - [Save Session](#save-session)
    - [Session Middleware PSR-15](#session-middleware-psr-15)
- [Credits](#credits)
___

# Getting started

Add the latest version of the Session service project running this command.

```
composer require tobento/service-session
```

## Requirements

- PHP 8.0 or greater

## Highlights

- Framework-agnostic, will work with any project
- Decoupled design
- Native Session or Using Save Handlers
- Flashing data
- Session PSR-15 Middleware

# Documentation

## Create Session

```php
use Tobento\Service\Session\Session;
use Tobento\Service\Session\SessionInterface;
use Tobento\Service\Session\SaveHandlerInterface;
use Tobento\Service\Session\ValidationInterface;

$session = new Session(
    name: 'sess',
    maxlifetime: 1800,
    cookiePath: '/',
    cookieDomain: '',
    cookieSamesite: 'Strict',
    secure: true,
    httpOnly: true,
    saveHandler: null, // null|SaveHandlerInterface
    validation: null, // null|ValidationInterface
);

var_dump($session instanceof SessionInterface);
// bool(true)
```

If the saveHandler parameter is null, it uses native session.

**Session Factory**

You might use the session factory to create the session.

```php
use Tobento\Service\Session\SessionFactory;
use Tobento\Service\Session\SessionFactoryInterface;
use Tobento\Service\Session\SessionInterface;
use Tobento\Service\Session\SaveHandlerInterface;
use Tobento\Service\Session\ValidationInterface;

$sessionFactory = new SessionFactory();

$session = $sessionFactory->createSession('name', [
    'maxlifetime' => 1800,
    'cookiePath' => '/',
    'cookieDomain' => '',
    'cookieSamesite' => 'Strict',
    'secure' => true,
    'httpOnly' => true,
    'saveHandler' => null, // null|SaveHandlerInterface
    'validation' => null, // null|ValidationInterface
]);

var_dump($session instanceof SessionInterface);
// bool(true)
```

## Save Handlers

### PDO MySql Save Handler

You might use the PDO mysql save handler to store session data in the database.

**Database Table**

```sql
CREATE TABLE `session` (
    `id` varchar(128),
    `data` text,
    `expiry` int(14) UNSIGNED,
     PRIMARY KEY (`id`)
);
```

**Create Save Handler**

```php
use Tobento\Service\Session\PdoMySqlSaveHandler;
use Tobento\Service\Session\SaveHandlerInterface;
use PDO;

$pdo = new PDO(
    dsn: 'mysql:host=localhost;dbname=db_name',
    username: 'root',
    password: '',
    options: [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_EMULATE_PREPARES => false,
    ],
);

$saveHandler = new PdoMySqlSaveHandler(
    table: 'session',
    pdo: $pdo
);

var_dump($saveHandler instanceof SaveHandlerInterface);
// bool(true)
```

### Null Save Handler

You might use the Null save handler for testing purposes.

```php
use Tobento\Service\Session\NullSaveHandler;
use Tobento\Service\Session\SaveHandlerInterface;

$saveHandler = new NullSaveHandler();

var_dump($saveHandler instanceof SaveHandlerInterface);
// bool(true)
```

## Validation

### Remote Addr Validation

```php
use Tobento\Service\Session\RemoteAddrValidation;
use Tobento\Service\Session\ValidationInterface;

$remoteAddr = $_SERVER['REMOTE_ADDR'] ?? null;

// PSR-7
// $remoteAddr = $serverRequest->getServerParams()['REMOTE_ADDR'] ?? null;

$validation = new RemoteAddrValidation(
    remoteAddr: $remoteAddr,
    trustedProxies: ['192.168.1.1'], // or null
    remoteAddrKey: '_session_remoteAddr', // is default
);

var_dump($validation instanceof ValidationInterface);
// bool(true)

var_dump($validation->remoteAddr());
// string(9) "127.0.0.1"

var_dump($validation->trustedProxies());
// array(1) { [0]=> string(11) "192.168.1.1" }
```

### Http User Agent Validation

```php
use Tobento\Service\Session\HttpUserAgentValidation;
use Tobento\Service\Session\ValidationInterface;

$validation = new HttpUserAgentValidation(
    httpUserAgent: $_SERVER['HTTP_USER_AGENT'] ?? null,
    httpUserAgentKey: '_session_httpUserAgent' // default
);

var_dump($validation instanceof ValidationInterface);
// bool(true)

var_dump($validation->httpUserAgent());
// string(78) "Mozilla/5.0 ..."
```

### Stack Validation

You may use the Validations::class to procees multiple validations.

```php
use Tobento\Service\Session\Validations;
use Tobento\Service\Session\RemoteAddrValidation;
use Tobento\Service\Session\HttpUserAgentValidation;
use Tobento\Service\Session\ValidationInterface;

$validation = new Validations(
    new RemoteAddrValidation($_SERVER['REMOTE_ADDR'] ?? null),
    new HttpUserAgentValidation($_SERVER['HTTP_USER_AGENT'] ?? null),
);

var_dump($validation instanceof ValidationInterface);
// bool(true)

$validations = $validation->validations();
// array<int, ValidationInterface>
```

### Custom Validation

You may write your own validation by implementing the ValidationInterface:

```php
use Tobento\Service\Session\ValidationInterface;
use Tobento\Service\Session\SessionInterface;
use Tobento\Service\Session\SessionValidationException;

/**
 * ValidationInterface
 */
interface ValidationInterface
{
    /**
     * Process the validation.
     *
     * @param SessionInterface $session
     * @return void
     * @throws SessionValidationException
     */
    public function process(SessionInterface $session): void;
}
```

## Start Session

Before storing or retrieving data from the session, you need to start the session. You might use the [Session Middleware PSR-15](#session-middleware-psr-15) to do so.

```php
use Tobento\Service\Session\SessionStartException;
use Tobento\Service\Session\SessionExpiredException;
use Tobento\Service\Session\SessionValidationException;

try {
    $session->start();
} catch (SessionStartException $e) {
    // handle
} catch (SessionExpiredException $e) {
    $session->destroy();
    
    // You might to restart session and regenerate id
    // on the current request.
    $session->start();
    $session->regenerateId();
    
    // Or you might send a message to the user instead.
    
} catch (SessionValidationException $e) {
    // handle
}
```

## Interacting With Data

**set**

Stores data in the session.

```php
$session->set('key', 'value');

// using dot notation:
$session->set('meta.color', 'color');
```

**get**

Get data from the session.

```php
$value = $session->get('key');

// using dot notation:
$value = $session->get('meta.color');

// using a default value if key does not exist
$value = $session->get('key', 'default');
```

**has**

To determine if an item is present in the session returning a boolean.

```php
$has = $session->has('key');

// using dot notation:
$has = $session->has('meta.color');

// using multiple keys.
$has = $session->has('key', 'meta.color');

$has = $session->has(['key', 'meta.color']);
```

**delete**

Delete data from the session.

```php
$session->delete('key');

// using dot notation:
$session->delete('meta.color');
```

**deleteAll**

Delete all data from the session.

```php
$session->deleteAll();
```

**all**

Get all data from the session.

```php
$data = $session->all();
```

## Interacting With Flash Data

Works only if the [Save Session](#save-session) method is being called on every request end.

**flash**

Store data in the session for the current and next request.

```php
$session->flash('key', 'value');

// using dot notation:
$session->flash('message.success', 'Message');
```

**now**

Store data in the session for the current request.

```php
$session->now('key', 'value');

// using dot notation:
$session->now('message.success', 'Message');
```

**once**

Store data in the session. After first retrieving data, the flashed data will be deleted.

```php
$session->once('key', 'value');

// using dot notation:
$session->once('message.success', 'Message');
```

## Miscellaneous

**regenerateId**

Regenerates new session id. Use it when changing important user states such as log in and logout.

```php
$session->regenerateId();
```

**destroy**

Destroys the session altogether but does not regenerate its id.

```php
$session->destroy();
```

**name**

Get the session name.

```php
$name = $session->name();
```

**id**

Get the session id.

```php
$id = $session->id();
```

## Save Session

Saves and flashing the session data. You might use the [Session Middleware PSR-15](#session-middleware-psr-15) to do so.

```php
use Tobento\Service\Session\SessionSaveException;

try {
    $session->save();
} catch (SessionSaveException $e) {
    // handle
}
```

## Session Middleware PSR-15

You may use the session middleware, which starts the session, adds the session as request attribute and finally calls the save method to save session data.\
Note that the session middleware handles only the SessionExpiredException.\
You may handle SessionStartException and SessionValidationException by your application error handling middleware or write your own session middleware fitting your application.

```php
use Tobento\Service\Middleware\MiddlewareDispatcher;
use Tobento\Service\Middleware\AutowiringMiddlewareFactory;
use Tobento\Service\Middleware\FallbackHandler;
use Tobento\Service\Container\Container;
use Tobento\Service\Session\Session;
use Tobento\Service\Session\SessionInterface;
use Tobento\Service\Session\Middleware\Session as SessionMiddleware;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use Nyholm\Psr7\Factory\Psr17Factory;

// create middleware dispatcher.
$dispatcher = new MiddlewareDispatcher(
    new FallbackHandler((new Psr17Factory())->createResponse(404)),
    new AutowiringMiddlewareFactory(new Container()) // any PSR-11 container
);

$session = new Session(
    name: 'sess',
    maxlifetime: 1800,
    cookiePath: '/',
    cookieDomain: '',
    cookieSamesite: 'Strict',
    secure: true,
    httpOnly: true,
    saveHandler: null,
    validation: null,
);

$dispatcher->add(function(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface {
    
    $session = $request->getAttribute(SessionInterface::class);
    
    var_dump($session instanceof SessionInterface);
    // bool(false)
    
    return $handler->handle($request);
});

$dispatcher->add(new SessionMiddleware($session));

$dispatcher->add(function(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface {
    
    $session = $request->getAttribute(SessionInterface::class);
    
    var_dump($session instanceof SessionInterface);
    // bool(true)
    
    return $handler->handle($request);
});

$request = (new Psr17Factory())->createServerRequest('GET', 'https://example.com');

$response = $dispatcher->handle($request);
```

# Credits

- [Tobias Strub](https://www.tobento.ch)
- [All Contributors](../../contributors)