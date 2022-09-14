# Hook utility

Alternative syntax for WordPress action and filter hooks. Allows removal of anonymous functions registered with the hook
container.

## Installation

`composer require seothemes/hooks`

## Examples

The usual way of adding hooks (when not using classes):

```php
function remove_home_body_class( array $classes ): array {
    return array_diff( $classes, [ 'home' ] );
}
add_filter( 'body_class', 'remove_home_body_class', 10, 1 );
```

In the example above, the function name `remove_home_body_class` must be typed twice. IDE's can help with this, but it
still results in having duplicated code.

Using the hook utility, the above example can be rewritten as follows:

*One Liner*:

```php
add_hook( 'body_class', 'remove_home_body_class', fn( $classes ) => array_diff( $classes, [ 'home' ] ), 10, 1 );
```

*Multiline*

Matches
the [@wordpress/hooks JS API](https://developer.wordpress.org/block-editor/reference-guides/packages/packages-hooks/)
syntax:

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

The function names have been reduced to one and anonymous functions can be later removed by targeting the given alias.

## How it works

Hook objects get stored in the static `$hooks` variable in the `hook_container` function. Hooks can be added and removed
from the container at any point just like regular hooks.

The `add_hook` function accepts the same arguments as `add_action` and `add_filter`, with the addition of "alias" as the
second argument.

```
string  $hook_name     Hook name, e.g `init`
string  $alias         Function alias. Used for hook removal.
Closure $callback      Actual closure to be called.
int     $priority      Hook priority.
int     $accepted_args Accepted number of arguments.
```

## Quick setup

Install with composer to your custom plugin or theme, or simply copy and paste the functions from the `hooks.php` file
to your project.

`composer require seothemes/hooks`

Once installed, add the following example for testing:

```php
namespace Company\Project;

require_once __DIR__ . '/vendor/autoload.php'; 

// Add 'test' class to body.
add_hook( 'body_class', 'add_test_class', fn( $classes ) => [ ...$classes, 'test' ] );

var_dump( hook_container() ); // Should return an array of hook objects.

// Remove
remove_hook( 'body_class', 'add_test_class' );

var_dump( hook_container() ); // Empty.
```

## Removing hooks

The "alias" argument adds an id to anonymous functions registered with the hook system, allowing them to be removed at a
later stage.

Hooks added with the `add_hook` function can only be removed with the `remove_hook` function.

The `remove_hook` unregisters the callback from WordPress, and removes the hook from the container. It accepts the same
arguments as `remove_action` and `remove_filter`:

```php
remove_hook( 'body_class', 'remove_home_body_class' );
```

## OOP

An alternative OOP option has been included in this package for those who prefer to use classes instead of functions.
Classes will be autoloaded when this package has been installed with Composer. All classes are pluggable and won't have
naming conflicts.

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

The Container class accepts an optional argument to automatically prefix all aliases with the given string. This is
useful when you want to remove all hooks added by a specific plugin or theme. Just pass in the namespace when retrieving
the instance:

```php
$hooks = ( new Factory() )->instance( 
    new Hooks( 
        new Container( __NAMESPACE__ ) 
    ) 
);
```
