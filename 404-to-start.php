<?php
/*
Plugin Name: 404-to-start
Plugin URI: http://1manfactory.com/4042start
Description: Send every 404 page not found error directly to start page (or any other page/site) to overcome problems with search engines
Version: 1.3.1
Author: J&uuml;rgen Schulze
Author URI: http://1manfactory.com
License: GNU GPL
*/

/*  Copyright 2010 Juergen Schulze, 1manfactory.com (email : 1manfactory@gmail.com)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
*/


f042start_set_lang_file();
add_action('admin_init', 'f042start_register_settings' );
add_action('admin_menu', 'f042start_plugin_admin_menu');
add_action('template_redirect', 'f042start_output_header');
register_activation_hook(__FILE__, 'f042start_activate');
register_deactivation_hook(__FILE__, 'f042start_deactivate');
register_uninstall_hook(__FILE__, 'f042start_uninstall');

function f042start_register_settings() { // whitelist options
	register_setting( 'f042start_option-group', 'f042start_type' );
	register_setting( 'f042start_option-group', 'f042start_target', 'f042start_check_values');
	
}

function keine_ahnung() {
}

function f042start_set_lang_file() {
	# set the language file
	$currentLocale = get_locale();
	if(!empty($currentLocale)) {
		$moFile = dirname(__FILE__) . "/lang/" . $currentLocale . ".mo";
		if(@file_exists($moFile) && is_readable($moFile)) load_textdomain('f042start', $moFile);
	}
}

function f042start_deactivate() {
	// needed
	delete_option('f042start_type');
	delete_option('f042start_target');
}

function f042start_activate() {
	# setting default values
	add_option('f042start_type', '301');
	add_option('f042start_target', home_url());
}


function f042start_uninstall() {
	# delete all data stored
	delete_option('f042start_type');
	delete_option('f042start_target');
}

function f042start_plugin_admin_menu() {
	add_options_page(__('404 to Start Settings', 'f042start'), "404 to Start", 9, basename(__FILE__), 'f042start_plugin_options');
}


function f042start_plugin_options(){

	if (!current_user_can('manage_options'))  {
		wp_die( __('You do not have sufficient permissions to access this page.') );
	}
	
	if (get_option("f042start_target")=="") {
		// setting target value to home url if empty (maybe because of plugin update)
		add_option('f042start_target', home_url());
	}
	
	print '<div class="wrap">';
	
	print '<h2>'.__('404 to Start Settings', 'f042start').'</h2>';

	print '<form method="post" action="options.php" id="f042start_form">';
	wp_nonce_field('update-options', '_wpnonce');
	settings_fields( 'f042start_option-group');

	print'
		<input type="hidden" name="page_options" value="f042start_type, f042start_target" />
		<table class="form-table">
		<tr valign="top">
		<th scope="row">'.__('404 Redirect', 'f042start').'</th>
		</tr>
		<tr>
		<td>
		<input type="radio" name="f042start_type" value="off" '.f042start_checked("f042start_type", "off").'/> '.__('off', 'f042start').' <br />
		<input type="radio" name="f042start_type" value="301" '.f042start_checked("f042start_type", "301").'/> '.__('301 - Moved permanently', 'f042start').'<br />
		<input type="radio" name="f042start_type" value="302" '.f042start_checked("f042start_type", "302").'/> '.__('302 - Found/ Moved temporarily (not recommended)', 'f042start').'<br />
		</td>
		</tr>
		<tr>
		<td>
		'.__('Target Url: (Start with http://)', 'f042start').' <input type="text" name="f042start_target" value="'.get_option("f042start_target").'" size="100">
		</td>
		</tr>
		</table>
		<input type="hidden" name="action" value="update" />

	';


	print '<p class="submit"><input type="submit" name="submit" value="'.__('Save Changes', 'f042start').'" /></p>';

	print '</form>';
	print '</div>';

	print '<br /><br /><br />';
	require_once('whatsup.php');
	
}

// sanitize function
function f042start_check_values($input) {
	# check target url
	if (!f042start_is_valid_url($input)) {
		add_settings_error('f042start_option-group', 'settings_updated', __('This is not a valid Url', 'f042start'), $type = 'error');
	}
	return $input;
}


function f042start_checked($checkOption, $checkValue) {
	return get_option($checkOption)==$checkValue ? " checked" : "";
}


// Trap 404 errors and redirect them to start page
// 301=permanently moved
// 302=temporary
function f042start_output_header() {
	
	if ( !is_404() || get_option("f042start_type")=="off" ) return;
	
	# setting default target to prevent errors
	if (get_option('f042start_target')=="") {
		$target=home_url();
	} else {
		$target=get_option("f042start_target");
	}
	
	wp_redirect( $target, get_option("f042start_type") );
}


# http://www.roscripts.com/snippets/show/156
function f042start_is_valid_url ( $url )
{
		$url = @parse_url($url);

		if ( ! $url) {
			return false;
		}

		$url = array_map('trim', $url);
		$url['port'] = (!isset($url['port'])) ? 80 : (int)$url['port'];
		$path = (isset($url['path'])) ? $url['path'] : '';

		if ($path == '')
		{
			$path = '/';
		}

		$path .= ( isset ( $url['query'] ) ) ? "?$url[query]" : '';

		if ( isset ( $url['host'] ) AND $url['host'] != gethostbyname ( $url['host'] ) )
		{
			if ( PHP_VERSION >= 5 )
			{
				$headers = get_headers("$url[scheme]://$url[host]:$url[port]$path");
			}
			else
			{
				$fp = fsockopen($url['host'], $url['port'], $errno, $errstr, 30);

				if ( ! $fp )
				{
					return false;
				}
				fputs($fp, "HEAD $path HTTP/1.1\r\nHost: $url[host]\r\n\r\n");
				$headers = fread ( $fp, 128 );
				fclose ( $fp );
			}
			$headers = ( is_array ( $headers ) ) ? implode ( "\n", $headers ) : $headers;
			return ( bool ) preg_match ( '#^HTTP/.*\s+[(200|301|302)]+\s#i', $headers );
		}
		return false;
}

?>