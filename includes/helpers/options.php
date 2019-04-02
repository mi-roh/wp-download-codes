<?php
/**
 * WP Download Codes Plugin
 * 
 * FILE
 * includes/helpers/options.php
 *
 * DESCRIPTION
 * Basic option handling.
 *
 */
 
/**
 * Returns the number of maximum attempts
 */
function dc_max_attempts() {
   return ( '' == get_option( 'dc_max_attempts' ) ? DC_MAX_ATTEMPTS : get_option( 'dc_max_attempts' ) );
}

/**
 * Returns the characters codes can be generated from
 */
function dc_code_chars() {
   return ( '' == get_option( 'dc_code_chars' ) ? DC_CODE_CHARS : get_option( 'dc_code_chars' ) );
}

/**
 * Returns header setting for content type 
 */
function dc_header_content_type() {
	return ( '' == get_option( 'dc_header_content_type' ) ? DC_HEADER_CONTENT_TYPE : get_option( 'dc_header_content_type' ) );
}

/**
 * Checks if x-sendfile support is enabled
 */
function dc_xsendfile_enabled() {
	return ( 'true' == get_option( 'dc_xsendfile_enabled' ) ? true : false );
}
?>