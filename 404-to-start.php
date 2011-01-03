<?php
/*
Plugin Name: 404-to-start
Plugin URI: http://1manfactory.com/4042start
Description: Send every 404 page not found error directly to start page to overcome problems with search engines
Version: 1.0
Author: J&uuml;rgen Schulze
Author URI: http://1manfactory.com
*/

add_action('template_redirect', 'f042start_output_header');

// Trap 404 errors and redirect them to start page
// 301=permanently moved
function f042start_output_header() {
	if ( !is_404() ) return;

	wp_redirect( home_url(), 301 );
}
?>
