


( function () {
$hooks = ( new Factory() )->instance( new Hooks( new Container( __NAMESPACE__ ) ) );

//	$hooks->add( 'body_class', 'blockify_body_class', function ( $classes ) {
//		return \array_diff( $classes, [ 'home' ] );
//	} );

//	$hooks->remove( 'body_class', 'blockify_body_class', 10 );

( new Hook( $hooks ) )
->setHookName( 'body_class' )
->setAlias( 'blockify_body_class' )
->setCallback( function ( $classes ) {
return \array_diff( $classes, [ 'home' ] );
} )
->setPriority( 10 )
->add();

} )();
