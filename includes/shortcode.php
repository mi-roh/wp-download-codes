<?php
/**
 * WP Download Codes Plugin
 * 
 * FILE
 * includes/shortcode.php
 *
 * DESCRIPTION
 * Contains functions for the template integration of the download codes via the [download-code ...]
 * shortcode.
 */
 
/**
 * Creates a download form for the shortcode "download-code"
 */
function dc_embed_download_code_form( $atts ) {
	global $wpdb;
	$id = "";
	$anchor = "";
	$post_code = "";
	$ret = "";
	
	// Get attributes
	extract(shortcode_atts(array(
		'id' => '0',
		'anchor' => '',
	), $atts));
	
	// Set shortcode id, i.e. the release id to which the shortcode relates. If no id is provided, this value is assumed as "all".
	$shortcode_id = ( $id == 0 ? 'all' : $id );
	
	// Check if code has been submitted for the release to which the current shortcode relates
	if (isset( $_POST['submit_' . $shortcode_id] )) {
		// Get current IP
		$IP = $_SERVER['REMOTE_ADDR'];

		// Get submitted code and release id
		$submitted_release = ( $_POST['submitted_release_' . $shortcode_id] != '' ? $_POST['submitted_release_' . $shortcode_id] : 'all' );
		$post_code = strtoupper( trim( $_POST['code_' . $shortcode_id] ) );
		
		// Get matching code record from database to check if code is valid for given release id or for all releases
		$wpdb->show_errors();
		$code = $wpdb->get_row(
			$wpdb->prepare(
			    "SELECT ID, `release` FROM " . dc_tbl_codes() . " WHERE CONCAT(code_prefix, code_suffix) = %s" . ( $submitted_release != 'all' ? ' AND `release` = %d' : '' ),
			    ( $submitted_release != 'all' ? array( $post_code, $submitted_release ) : array( $post_code ) )
			)
		);
		
		if ( $code->ID ) {
			// Get release details
			$release = $wpdb->get_row( "SELECT * FROM " . dc_tbl_releases() . " WHERE ID = " . $code->release );
			
			// Get # of downloads with this code
			$downloads = $wpdb->get_row(
				$wpdb->prepare(
				    "SELECT COUNT(*) AS downloads FROM " . dc_tbl_downloads() . " WHERE code=(SELECT ID FROM " . dc_tbl_codes() . " WHERE CONCAT(code_prefix, code_suffix) = %s )",
				    array( $post_code )
				)
			);
			
			// Start download if maximum of allowed downloads is not reached
			if ( $downloads->downloads < $release->allowed_downloads ) {
				// Set temporary download lease id
				$download_lease_id[$shortcode_id] = md5( 'wp-dl-hash' . $code->ID );
			}
			else {
				$ret = dc_msg( 'max_downloads_reached' );
			}
		}
		else {
			// Get # of attempts from this IP
			$attempts = $wpdb->get_row( "SELECT COUNT(*) AS attempts FROM " . dc_tbl_downloads() . " WHERE IP='" . $IP . "' AND code = -1 AND DATE(started_at) > DATE(CURRENT_DATE() - 1)" );		
			
			if ( $attempts->attempts < dc_max_attempts() ) {
				// Insert attempt
				$wpdb->insert(	dc_tbl_downloads(),
								array( 'code' => -1, 'IP' => $IP),
								array( '%d', '%s') );

				$ret = dc_msg( 'code_invalid' );
			}
			else {
				$ret = dc_msg( 'max_attempts_reached' );
			}	
		}
	}
	
	// Compile HTML result
	$html = '<div class="dc-download-code">';
	if ( isset($download_lease_id) && $download_lease_id[$shortcode_id] && ( $shortcode_id == 'all' || $shortcode_id == $submitted_release )) {
		// Show link for download
		$html .= '<p>' . dc_msg( 'code_valid' ) . '</p>';
		$html .= '<p><a href="' . site_url() . '/?lease=' . $download_lease_id[$shortcode_id] . '">' . ( $release->artist ? $release->artist . ' - ' : '' ) . $release->title . '</a>';
		if ( 'WP_DIRECT' == $release->hosting_type ) {
			$html .= ' ' . format_bytes( filesize( dc_file_location() . $release->filename ) );
		}
		$html .= '</p>';
	}
	else {
		// Show message
		if ( $ret != '' ) {
			$html .= '<p>' . $ret . '</p>';
		}
		
		// Display form
		$html .= '<form action="' . ( '' == $anchor ? '' : '#' . $anchor ) . '" name="dc_form" method="post">';
		$html .= '<p><input type="hidden" name="submitted_release_' . $shortcode_id . '" value="' . $shortcode_id . '" />'; 
		$html .= dc_msg( 'code_enter' ) .' <input type="text" name="code_' . $shortcode_id . '" value="' . ( $post_code != "" ? $post_code : ( $_GET['yourcode'] != "" ? $_GET['yourcode'] : "" ) ) . '" size="20" /> ';
		$html .= '<input type="submit" name="submit_' . $shortcode_id . '" value="' . __( 'Submit') . '" /></p>';
		$html .= '</form>';
	}
	$html .= '</div>';
	
	return $html;
}
?>