<?php
/*
Plugin Name: WP Download Codes
Plugin URI: http://wordpress.org/extend/plugins/wp-download-codes/

Description: The plugin enables to generation and management of download codes for .zip files. It was written to enable the free download of records and CDs with dedicated codes printed on the cover of the releases or on separate download cards.

Version: 2.5.3.1
Author: misanthrop, spalmer
Author URI: http://www.misantropolis.de, http://quoperative.com

	Copyright 2009-2019 Armin Fischer  (email : misantropolis@gmail.com)

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
	Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA

/**
 * Default values (constants)
 */

define( 'DC_MAX_ATTEMPTS', 3 );
define( 'DC_ALLOWED_DOWNLOADS', 3 );
define( 'DC_FILE_TYPES', 'zip, mp3' );
define( 'DC_CODE_CHARS', 'ABCDEFGHIJKLMNPQRSTUVWXYZ123456789-' );
define( 'DC_HEADER_CONTENT_TYPE', 'Default (MIME Type)');
define( 'DC_CAPABILITY_REF', 'manage_options' );
if ( !defined( 'DC_CAPABILITY' ) ) {
    define( 'DC_CAPABILITY', DC_CAPABILITY_REF );
}

/**
 * Include helper functions
 */
include( 'includes/helpers/db.php' );
include( 'includes/helpers/file.php' );
include( 'includes/helpers/messages.php' );
include( 'includes/helpers/options.php' );
include( 'includes/helpers/string.php' );

/**
 * Include admin related functions
 */
include( 'includes/admin/main.php' ); // Successively includes further admin libraries

/**
 * Include functionality for shortcode handling
 */
include( 'includes/shortcode.php' );

/**
 * Include functionality to process downloads
 */
include( 'includes/download.php' );


add_action( 'init', function() {

    /**
     * Add dc functions to hooks
     */
    if ( is_admin() ) {
        // Initialize scripts and stylesheets
        add_action( 'admin_init', 'dc_admin_init' );

        // Create administration menu
        add_action( 'admin_menu', 'dc_admin_menu' );
    } else {
        // Send headers for file downloads
        add_action( 'send_headers', 'dc_send_download_headers' );
    }

    // Handle shortcode for [download-code id="..."]
    add_shortcode( 'download-code', 'dc_embed_download_code_form' );

} );

// Activate plugin
register_activation_hook( __FILE__, 'dc_init' );

// Uninstall plugin
if ( function_exists('register_uninstall_hook') ) {
	register_uninstall_hook(__FILE__, 'dc_uninstall');
}
