<?php
/**
 * WP Download Codes Plugin
 * 
 * FILE
 * includes/admin/download-code-settings.php
 *
 * DESCRIPTION
 * Contains functions to maintain the general functions of the plugin.
 */

/**
 * General download code plugin settings
 */
function dc_manage_settings() {
	echo '<div class="wrap">';
	echo '<h2>Download Codes &raquo; Settings</h2>';
	
	// Overwrite existing options
	if ( isset( $_POST['submit'] ) ) {
		$dc_file_location = trim( ( '' != trim( $_POST['dc_file_location_abs'] ) ? $_POST['dc_file_location_abs'] : $_POST['dc_file_location'] ) );
		$dc_max_attempts = $_POST['dc_max_attempts'];
		
		// Update zip location
		if ( $dc_file_location != '' ) {
			if ( substr( $dc_file_location, -1 ) != '/' ) {
				$dc_file_location .= '/';
			}
			update_option( 'dc_file_location', $dc_file_location );
		}
		
		// Update number of maximum attempts
		if ( is_numeric( $dc_max_attempts ) ) {
			update_option( 'dc_max_attempts' , $dc_max_attempts );
		}
		
		// Update file types
		if ( '' != trim( $_POST['dc_file_types'] ) ) {
			update_option( 'dc_file_types' , trim( $_POST['dc_file_types'] ) );
		}
		
		// Update character list
		update_option( 'dc_code_chars' , $_POST['dc_code_chars'] == '' ? DC_CODE_CHARS : $_POST['dc_code_chars'] );
		
		// Update header settings
		update_option( 'dc_header_content_type' , $_POST['dc_header_content_type'] == '' ? DC_HEADER_CONTENT_TYPE : $_POST['dc_header_content_type'] );
		
		// Update xsenfile enabled flag
		update_option( 'dc_xsendfile_enabled' , isset( $_POST['dc_xsendfile_enabled'] ) ? 'true' : 'false' );
				
		// Update messages
		update_option( 'dc_msg_code_enter' , $_POST['dc_msg_code_enter'] );
		update_option( 'dc_msg_code_valid' , $_POST['dc_msg_code_valid'] );
		update_option( 'dc_msg_code_invalid' , $_POST['dc_msg_code_invalid'] );
		update_option( 'dc_msg_max_downloads_reached' , $_POST['dc_msg_max_downloads_reached'] );
		update_option( 'dc_msg_max_attempts_reached' , $_POST['dc_msg_max_attempts_reached'] );
		
		// Print message
		echo dc_admin_message( 'The settings have been updated.' );	
	}
	
	echo '<form action="admin.php?page=dc-manage-settings" method="post">';

	echo '<h3>File Settings</h3>';

	echo '<table class="form-table">';

	/**
	 * Location of download files
	 */
	
	echo '<tr valign="top">';
	echo '<th scope="row"><label for="settings-location">Location of download files</label></th>';
	
	if ( '' == get_option( 'dc_file_location' ) || ( '' != get_option( 'dc_file_location' ) && '/' != substr( get_option( 'dc_file_location' ), 0, 1 ) ) ) {
		// If current location of download files is empty or relative, try to locate the upload folder
		$wp_upload_dir = wp_upload_dir();
		$files = scandir( $wp_upload_dir['basedir'] );	
		
		echo '<td>' . $wp_upload_dir['basedir']  . '/ <select name="dc_file_location" id="settings-location">';
		foreach ( $files as $folder ) {
			if ( is_dir( $wp_upload_dir['basedir'] . '/' . $folder ) && $folder != '.' && $folder != '..' ) {
				echo '<option' . ( $folder . '/' == get_option( 'dc_file_location' ) ? ' selected="selected"' : '' ) . '>' . $folder . '</option>';
			}
		}
		echo '</select>';
		
		// Provide possibility to define upload path directly
		echo '<p>If the upload folder cannot be determined or if the release management does not work (or if you want to have another download file location) you may specify the absolute path of the download file location here:</p>';
		echo '<input type="text" name="dc_file_location_abs" class="large-text" / >';
		
		echo '</td>';
	}
	else {
		echo '<td><input type="text" name="dc_file_location" id="settings-location" class="large-text" value="' . get_option( 'dc_file_location' ) . '" /></td>';
	}
	
	echo '</tr>';
	
	echo '<tr valign="top">';
	echo '<th scope="row"><label for="settings-max">Maximum attempts</label></th>';
	echo '<td><input type="text" name="dc_max_attempts" id="settings-max" class="small-text" value="' . dc_max_attempts() . '" />';
	echo ' <span class="description">Maximum invalid download attempts</span></td>';	
	echo '</tr>';
	
	echo '<tr valign="top">';
	echo '<th scope="row"><label for="settings-filetypes">Allowed file types</label></th>';
	echo '<td><input type="text" name="dc_file_types" id="settings-filetypes" class="regular-text" value="' . ( implode( ', ', dc_file_types() ) ) . '" />';
	echo ' <span class="description">Separated by comma</span></td>';	
	echo '</tr>';
	
	echo '<tr valign="top">';
	echo '<th scope="row"><label for="settings-chars">Allowed characters</label></th>';
	echo '<td><input type="text" name="dc_code_chars" id="settings-chars" class="regular-text" value="' . dc_code_chars() . '" />';
	echo ' <span class="description">Codes will contain a random mix of these characters</span></td>';	
	echo '</tr>';
	
	echo '</table>';
	
	/**
	 * Headers
	 */
	
	echo '<h3>Header Settings</h3>';
	
	echo '<p>Finetune request headers to fix client-server issues:</p>';
	
	echo '<table class="form-table">';
	
	// Content type
	$dc_header_content_type = dc_header_content_type();
	$content_type_options = array( 'Default (MIME Type)', 'application/force-download', 'application/octet-stream', 'application/download' );
	echo '<tr valign="top">';
	echo '<th scope="row"><label for="headers-content-type">Content type</label></th>';
	echo '<td><select name="dc_header_content_type" id="headers-content-type">';
	foreach ( $content_type_options as $option) {
		echo '<option' . ( $option == $dc_header_content_type ? ' selected="selected"' : '') . '>' . $option . '</option>';
	}
	echo '</select> <span class="description">Override default content type (which is the MIME type of the download file)</span></td>';	
	echo '</tr>';
	
	// Support for x-sendfile
	echo '<tr valign="top">';
	echo '<th scope="row"><label for="headers-xsendfile-enabled">Apache X-Sendfile</label></th>';
	echo '<td><input type="checkbox" name="dc_xsendfile_enabled" id="dc-xsendfile-enabled" ' . ( dc_xsendfile_enabled() ? 'checked' : '') . ' />';
	echo '<span class="description">Only check this setting if Apache\'s x-sendfile module is installed and configured properly</span>';
	echo '</td>';	
	echo '</tr>';
	
	echo '</table>';
	
	/**
	 * Messages
	 */
	
	echo '<h3>Messages</h3>';
	
	echo '<p>Specify custom messages that your users see while downloading releases:</p>';

	echo '<table class="form-table">';
	
	echo '<tr valign="top">';
	echo '<th scope="row"><label for="settings-msg-enter">"Enter code"</label></th>';
	echo '<td><input type="text" name="dc_msg_code_enter" id="settings-msg-enter" class="large-text" value="' . dc_msg( 'code_enter' ) . '" /></td>';
	echo '</tr>';

	echo '<tr valign="top">';
	echo '<th scope="row"><label for="settings-msg-valid">"Code valid"</label></th>';
	echo '<td><input type="text" name="dc_msg_code_valid" id="settings-msg-valid" class="large-text" value="' . dc_msg( 'code_valid' ) . '" /></td>';
	echo '</tr>';

	echo '<tr valign="top">';
	echo '<th scope="row"><label for="settings-msg-invalid">"Code invalid"</label></th>';
	echo '<td><input type="text" name="dc_msg_code_invalid" id="settings-msg-invalid" class="large-text" value="' . dc_msg( 'code_invalid' ) . '" /></td>';
	echo '</tr>';

	echo '<tr valign="top">';
	echo '<th scope="row"><label for="settings-msg-downloads">"Maximum downloads reached"</label></th>';
	echo '<td><input type="text" name="dc_msg_max_downloads_reached" id="settings-msg-downloads" class="large-text" value="' . dc_msg( 'max_downloads_reached' ) . '" /></td>';
	echo '</tr>';

	echo '<tr valign="top">';
	echo '<th scope="row"><label for="settings-msg-attempts">"Maximum attempts reached"</label></th>';
	echo '<td><input type="text" name="dc_msg_max_attempts_reached" id="settings-msg-attempts" class="large-text" value="' . dc_msg( 'max_attempts_reached' ) . '" /></td>';
	echo '</tr>';
	
	echo '</table>';
	
	echo '<p class="submit">';
	echo '<input type="submit" name="submit" class="button-primary" value="Save Changes" />';
	echo '</p>';
	echo '</form>';

	echo '</div>';
}
?>