<?php
/**
 * WP Download Codes Plugin
 * 
 * FILE
 * includes/helpers/messages.php
 *
 * DESCRIPTION
 * Handling of messages for admins and users.
 *
 */
 
/**
 * Apply basic formatting to status messages for admins
 */
function dc_admin_message( $message, $class = 'updated')
{
	return '<div id="message" class="' . $class  .'"><p>' . $message . '</p></div>';
}

/**
 * Get a message (configurable via plugin settings)
 */
function dc_msg( $str_msg ) {
	// Try to get option for desired message
	$str_return = get_option( 'dc_msg_' . $str_msg );
	
	if ( '' == $str_return ) {
		// Default messages
		switch ( $str_msg ) {
			case 'code_enter': 
				$str_return = 'Enter download code:';
				break;
			case 'code_valid': 
				$str_return = 'Thank you for entering a valid download code! Please proceed with the download by clicking the following link:';
				break;
			case 'code_invalid': 
				$str_return = 'You have entered an invalid download code, please try again.';
				break;
			case 'max_downloads_reached': 
				$str_return = 'You have reached the maximum number of allowed downloads for this code. Please refer to the administrator for information about reactivating your code.';
				break;
			case 'max_attempts_reached': 
				$str_return = 'You have had too many unsuccessful download attempts today. Please wait and try again.';
				break;
		}
	}
	return $str_return;
}
?>