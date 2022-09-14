# Hook Container

Alternative syntax for managing WordPress action and filter hooks.

This project is experimental and is not intended for production use until further testing has been done. It is intended as an example to explore more flexible and minimal ways of writing WordPress hooks for smaller projects that use mostly functional programming.

There are two separate approaches included, one using only functions, and the other using OOP classes. The main purpose of this package is to provide this functionality using only functions, as there are already many OOP approaches to managing hooks. The OOP approach can be used as a comparison, or where it makes sense.

## Features

- Allows later removal of anonymous functions added with the hook container:

```php
// Add hook with anonymous arrow function:
add_hook( 'init', 'print_hello_world', fn() => print 'Hello world!' );

// Remove hook with arrow function:
remove_hook( 'init', 'print_hello_world' );
```

- Allows PHP hooks to match the [@wordpress/hooks JS API](https://developer.wordpress.org/block-editor/reference-guides/packages/packages-hooks/) syntax more closely:

```php
addFilter( 
    'body_class',
    __NAMESPACE__ . '\\remove_home_body_class',
    function( array $classes ) : array {
        return array_diff( $classes, [ 'home' ] );
    }
);
```

- Allows automatic namespacing of all callbacks in a project:

```php
namespace Company\Project;

add_filter( 
    'hook_alias', 
    fn( string $alias ) => __NAMESPACE__ . '\\' . $alias
);

add_hook( 'init', 'print_hello_world', fn() => print 'Hello world!' );

var_dump( hook_container() );

// Var dump results:
$hook_container_contents = (object) [
    'init' => [
        'Company\\Project\\print_hello_world' => [
            10 => '' // fn() => print 'Hello world!'
        ],
    ],
];
```

## Installation

`composer require seothemes/hooks`

Then load composer in your project:

```php
require_once __DIR__ . '/vendor/autoload.php';
```

## Examples

The usual way of adding hooks (when not using classes):

```php
function remove_home_body_class( array $classes ): array {
    return array_diff( $classes, [ 'home' ] );
}
add_filter( 'body_class', 'remove_home_body_class', 10, 1 );
```

In the example above, the function name `remove_home_body_class` must be typed twice. IDE's can help with this, but it still results in having duplicated code. Also, anonymous functions added using this method cannot be removed later.

Using the hook utility, the above example can be rewritten as follows:

*Multiline*

Matches the [@wordpress/hooks JS API](https://developer.wordpress.org/block-editor/reference-guides/packages/packages-hooks/) syntax:

```php
addFilter( 
    'body_class',
    'remove_home_body_class',
    fn( $classes ) => array_diff( $classes, [ 'home' ] ),
    10,
    1
);
```

Adding an action hook with an anonymous function:

```php
addAction(
    'init',
    'my_callback_alias',
    function() {
        echo 'Hello World!';
    },
    10,
    1
);
```

*One Liner*:

```php
add_hook( 'body_class', 'remove_home_body_class', fn( $classes ) => array_diff( $classes, [ 'home' ] ), 10, 1 );
```

Now the function names have been reduced to one and anonymous functions can be later removed by targeting the given alias.

## How it works

Hook objects get stored in the static `$hooks` variable in the `hook_container` function. Hooks can be added and removed from the container at any point just like regular hooks.

The `add_hook` function accepts the same arguments as `add_action` and `add_filter`, with the addition of "alias" as the second argument.

```
string  $hook_name     Hook name, e.g `init`
string  $alias         Function alias. Used for hook removal.
Closure $callback      Actual closure to be called.
int     $priority      Hook priority.
int     $accepted_args Accepted number of arguments.
```

## Quick setup

Install with composer to your custom plugin or theme, or simply copy and paste the functions from the `hooks.php` file to your project.

`composer require seothemes/hooks`

## Adding hooks

Once installed, add the following example for testing:

```php
namespace Company\Project;

require_once __DIR__ . '/vendor/autoload.php'; 

// Add 'test' class to body.
add_hook( 'body_class', 'add_test_class', fn( $classes ) => [ ...$classes, 'test' ] );

var_dump( hook_container() ); // Should return an array of hook objects.
```

Hooks can be added with any of the provided utility functions:

```php
add_hook( 'body_class', 'remove_home_body_class', fn($classes) => $classes );
addFilter( 'body_class', 'remove_home_body_class', fn($classes) => $classes );
addAction( 'body_class', 'remove_home_body_class', fn($classes) => $classes );
```

## Removing hooks

The "alias" argument adds an id to anonymous functions registered with the hook system, allowing them to be removed at a later stage.

Hooks added with the `add_hook` function can only be removed with the `remove_hook` function.

The `remove_hook` function unregisters the callback from WordPress, and removes the hook from the container. It accepts the same arguments as `remove_action` and `remove_filter`:

```php
remove_hook( 'body_class', 'remove_home_body_class' );
```

There are two wrapper functions provided for removing hooks (they both do the same):

```php
removeFilter( 'body_class', 'remove_home_body_class' );
removeAction( 'body_class', 'remove_home_body_class' );
```

## OOP

An alternative OOP option has been included in this package as another example. All classes will be autoloaded when this package has been installed with Composer. All classes (and functions) are pluggable to avoid naming conflicts.

The Factory class provides access to a single Hooks instance, add the following line anywhere in your code to get:

```php
use SEOThemes\Hooks\Factory;
use SEOThemes\Hooks\Hooks;
use SEOThemes\Hooks\Container;

$hooks = ( new Factory() )->instance( new Hooks( new Container() ) );
```

To use the Hooks class, simply add the following to your code:

```php
use SEOThemes\Hooks\Factory;
use SEOThemes\Hooks\Hooks;
use SEOThemes\Hooks\Container;

$hooks = ( new Factory() )->instance( new Hooks( new Container() ) );

$hooks->add( 'body_class', 'add_body_class', function ( $classes ) {
    $classes[] = 'test';

    return $classes;
} );

// To remove the hook uncomment this line.
// $hooks->remove( 'body_class', 'add_body_class' );
```

## Autoprefix aliases

To eliminate even more duplicated code, the hook utility also provides a way to automatically namespace all aliases. This saves adding the namespace to every `add_hook` call.

A `hook_alias` filter has been included but should be used with caution, in case other plugins are also filtering the alias:

```php
// Setting namespace directly per function:
add_hook( 
    'body_class', 
    __NAMESPACE__ . '\\remove_home_body_class', 
    fn( $classes ) => array_diff( $classes, [ 'home' ] ), 
);

// Alternatively, filter the alias:
add_filter( 'hook_alias', function( string $default, array $args ) : string {
    return  __NAMESPACE__ . '\\' . $default;
} );

// Then you can pass the alias without the namespace:
add_hook( 
    'body_class', 
    'remove_home_body_class', 
    fn( array $classes ) : array => array_diff( $classes, [ 'home' ] ), 
);
```

There are ways to determine if the callback has been added from your project, for example using `debug_backtrace`, but this has not been included in the package.

Another option is to create your own wrapper function, which adds the namespace to every alias:

```php
namespace Company\Project;

use Closure;

function addCustomHook( 
    string $hook_name, 
    string $alias, 
    Closure $callback, 
    int $priority = 10, 
    int $accepted_args = 1 
    ): bool {
    return add_hook( 
        $hook_name, 
        __NAMESPACE__ . '\\' . $alias, 
        $callback, 
        $priority, 
        $accepted_args 
    );
}
```

If using OOP, the Container class accepts an optional argument to automatically prefix all aliases within the container with the given string: 

```php
use SEOThemes\Hooks\Factory;
use SEOThemes\Hooks\Hooks;
use SEOThemes\Hooks\Container;

// Inject dependencies.
$container = new Container( __NAMESPACE__ );
$hooks     = new Hooks( $container );
$factory   = new Factory();
$instance  = $factory->instance( $hooks );

$instance->add('init', 'my_function', fn() => print 'Hello World!' );
$instance->remove('init', 'my_function' );

// Alternative short syntax.
( new Factory() )->instance( new Hooks( new Container( 'namespace' ) ) )
->add( 'init', 'new_function', fn() => print 'Hello World!' );
```

## Hook Container

The hook container stores hooks in the following structure to allow for multiple hooks and priorities:

```php
$hook_container = [
    'hook_name' => [
        'alias' => [
            'priority' =>  '', // Closure, e.g. fn() => print 'Hello World!'.
        ],
    ],
];

// A real example of var_dumping the hook container contents would look something like this:
$hook_container = [
    'init' => [
        'print_hello_world' => [
            10 => fn() => print 'Hello World!',
            11 => fn() => print 'Hello World, again!',
        ],
        'print_something_else' => [
            10 => fn() => print 'Something else!',
        ],
    ],
];
```

## Testing and contributing

All contributions are welcome. There may be better ways to achieve this that I haven't thought of, so please feel free to submit a pull request or open an issue.
