# Hook utility

Allows removing of WordPress hooks with anonymous functions.

## Examples 

Standard way of writing hooks:

```php
add_filter( 'body_class', 'remove_home_body_class' );
function remove_home_body_class( array $classes ): array {
    return array_diff( $classes, [ 'home' ] );
}
```

There are at least two problems with the above example:

1. Function name is repeated and must be typed twice
2. Anonymous functions are not supported


With  the `add_hook` function:

```php
add_hook( 'body_class', 'remove_home_body_class', fn( $classes ) => array_diff( $classes, [ 'home' ] ) );
```

The function names have been reduced to one and anonymous functions can be later removed.

The syntax also matches the [@wordpress/hooks JS API](https://developer.wordpress.org/block-editor/reference-guides/packages/packages-hooks/) more closely:

```php
addFilter( 
    'body_class', 
    'remove_home_body_class', 
    fn( $classes ) => array_diff( $classes, [ 'home' ] ) 
);
```

## How it works

Hook objects are stored in the static `$hooks` variable in the `hook_container` function. Hooks can be added and removed from the container at any point just like regular hooks.

The `add_hook` function accepts the same arguments as `add_action` and `add_filter`, with the addition of "alias" as the second argument.

```
string  $hook_name Hook name or array of hook names.
string  $alias
Closure $callback
int     $priority
int     $accepted_args
```

### Alias

The "alias" argument adds an id to anonymous functions registered with the hook system, allowing them to be removed afterwards.

Another bonus of having an "alias" argument is that all function aliases can be automatically prefixed with the `hook_namespace` filter (must be placed before any calls to the hook system):

```php
add_filter( 'hook_namespace', fn( $alias ) => __NAMESPACE__ . '\\' . $alias );
```

## Removing hooks

Hooks added with the `add_hook` function can only be removed with the `remove_hook` function.

The `remove_hook` unregisters the callback from WordPress, and removes the hook from the container. It accepts the same arguments as `remove_action` and `remove_filter`:

```php
remove_hook( 'body_class', 'remove_home_body_class' );
```

## Quick setup

Install with composer to your custom plugin or theme:

`composer require seothemes/hooks`

Or simply copy and paste the functions from the `hooks.php` file to your project.

Once installed, add the following example for testing:

```php
namespace Company\Project;

require_once __DIR__ . '/vendor/autoload.php'; 

add_filter( 'hook_namespace', fn( string $alias ) : string => __NAMESPACE__ . '\\' . $alias );
add_hook( 'body_class', 'test', fn( $classes ) => [ ...$classes, 'test' ] );
remove_hook( 'body_class', 'test' );
var_dump( hook_container() );
```
