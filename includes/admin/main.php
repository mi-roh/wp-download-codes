<?php
/**
 * WP Download Codes Plugin
 * 
 * FILE
 * includes/admin/main.php
 *
 * DESCRIPTION
 * Includes further libraries and covers initialization and uninstallation procedures for the plugin.
 */

/**
 * Include further admin libraries
 */
include( 'admin-menu.php' );
include( 'manage-releases.php' );
include( 'manage-codes.php' );
include( 'download-code-settings.php' );
include( 'help.php' );

/**
 * Initializes the download codes (dc) plugin
 */
function dc_init() {
	global $wpdb;

	require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
	
	$sql = "CREATE TABLE `" . dc_tbl_codes() . "` (
				   `ID` int(11) NOT NULL auto_increment,
				   `group` int(11) NOT NULL,
				   `code_prefix` varchar(20) NOT NULL,
				   `code_suffix` varchar(20) NOT NULL,
				   `release` int(11) NOT NULL,
				   `final` int(1) NOT NULL,
					PRIMARY KEY  (`ID`)
				 );";
	dbDelta( $sql );

	$sql = "CREATE TABLE `" . dc_tbl_code_groups() . "` (
				   `ID` int(11) NOT NULL auto_increment,
				   `release` int(11) NOT NULL,
					PRIMARY KEY  (`ID`)
				 );";
	dbDelta( $sql );
	
	$sql = "CREATE TABLE `" . dc_tbl_downloads() . "` (
				   `ID` int(11) NOT NULL auto_increment,
				   `IP` varchar(20) NOT NULL,
				   `started_at` timestamp NOT NULL,
				   `code` int(11) NOT NULL,
					PRIMARY KEY  (`ID`)
				 );";
	dbDelta( $sql );

	$sql = "CREATE TABLE `" . dc_tbl_releases() . "` (
				   `ID` int(11) NOT NULL auto_increment,
				   `title` varchar(100) NOT NULL,
				   `artist` varchar(100) NOT NULL,
				   `filename` varchar(300) NOT NULL,
				   `allowed_downloads` int(11) NOT NULL,
				   `hosting_type` varchar(50) NOT NULL,
				   `url` varchar(2000) NOT NULL,
				   PRIMARY KEY  (`ID`)
				 );";
	dbDelta( $sql );
	
	// In version 2.0, code groups were introduced, therefore when
	// upgrading from a prior version, it has to be ensured
	// that initial code groups are created for every group
	// of code prefixes
	
	// Retrieve all codes without a code group
	$sql = "
		SELECT	DISTINCT c.code_prefix AS `prefix`, c.release AS `release`
		FROM	". dc_tbl_codes() . " c
		WHERE	c.group IS NULL OR c.group = 0";
	$code_groups = $wpdb->get_results( $sql );
		
	foreach ( $code_groups as $code_group ) {
		// Create a new code group
		$wpdb->insert(	dc_tbl_code_groups(), array( 'release' => $code_group->release ), array ( '%d' ));
		
		// Get the id of the new code group
		$code_group_id = $wpdb->insert_id;
		
		// Update the affected codes with the new code group id
		$wpdb->update(	dc_tbl_codes(), 
						array( 'group' => $code_group_id ),
						array( 'code_prefix' => $code_group->prefix, 'release' => $code_group->release ),
						array( '%d' ),
						array( '%s', '%d' ));
	}	
	
	// Set current plugin version (for future use)
	update_option( 'dc_version', '2.0' );
}

/**
 * Uninstalls the dc plugin.
 */
function dc_uninstall() {
	global $wpdb;

	// Delete wordpress options
	delete_option( 'dc_zip_location' );
	delete_option( 'dc_max_attempts' );
	delete_option( 'dc_header_content_type' );
	delete_option( 'dc_msg_code_enter' );
	delete_option( 'dc_msg_code_valid' );
	delete_option( 'dc_msg_code_invalid' );
	delete_option( 'dc_msg_max_downloads_reached' );
	delete_option( 'dc_msg_max_attempts_reached' );
	delete_option( 'dc_file_location' );
	delete_option( 'dc_file_types' );
	delete_option( 'dc_version' );
	
	// Delete database tables
	$wpdb->query( "DROP TABLE " . dc_tbl_downloads() );
	$wpdb->query( "DROP TABLE " . dc_tbl_codes() );
	$wpdb->query( "DROP TABLE " . dc_tbl_code_groups() );
	$wpdb->query( "DROP TABLE " . dc_tbl_releases() );
}
?>