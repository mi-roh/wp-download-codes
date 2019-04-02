<?php
/**
 * WP Download Codes Plugin
 * 
 * FILE
 * includes/helpers/file.php
 *
 * DESCRIPTION
 * Functionality related to handling of files (locations, file types, file size formatting, etc.).
 *
 */
 
 /**
 * Returns the full path of the download file location.
 */
function dc_file_location() {
	// Get location of download file (for compatibility of older versions)
	$dc_file_location = ( '' == get_option( 'dc_file_location' ) ? get_option( 'dc_zip_location' ) : get_option( 'dc_file_location' ) );

	// Check if location is an absolute or relative path
	if ( strlen( $dc_file_location ) > 0 && '/' == substr( $dc_file_location, 0, 1 ) ) {
		// Absolute locations are returned directly
		return $dc_file_location;
	}
	else {
		// Relative locations are returned with the respective upload path directory
		$wp_upload_dir = wp_upload_dir();
		$upload_path = get_option( 'upload_path' );
		
		if ( ( strlen( $upload_path ) > 0 ) && ( substr( $wp_upload_dir['basedir'], 0, strlen( $upload_path ) ) == $upload_path ) ) {
			return  $upload_path . '/' . $dc_file_location;
		}
		else {
			return $wp_upload_dir['basedir'] . '/' . $dc_file_location;
		}
	}
}

/**
 * Returns a list of allowed file types.
 */
function dc_file_types() {
	$str_file_types = get_option( 'dc_file_types' );
	
	if ( '' == $str_file_types ) {
		$arr_file_types = explode( ',', DC_FILE_TYPES);
	}
	else {
		$arr_file_types = explode( ',', $str_file_types );
	}
	
	// Trim white space
	array_walk($arr_file_types, 'dc_trim_value');
	
	return $arr_file_types;
}

/**
 * Converts bytes into meaningful file size
 */
function format_bytes( $filesize ) 
{
    $units = array( ' B', ' KB', ' MB', ' GB', ' TB' );
    for ( $i = 0; $filesize >= 1024 && $i < 4; $i++ ) $filesize /= 1024;
    return round($filesize, 2) . $units[$i];
}

/**
 * Returns the MIME content type of a given file.
 */
function get_mime_content_type( $file )
{
	$mime_types = array(
			"pdf"=>"application/pdf"
			,"exe"=>"application/octet-stream"
			,"zip"=>"application/zip"
			,"docx"=>"application/msword"
			,"doc"=>"application/msword"
			,"xls"=>"application/vnd.ms-excel"
			,"ppt"=>"application/vnd.ms-powerpoint"
			,"gif"=>"image/gif"
			,"png"=>"image/png"
			,"jpeg"=>"image/jpg"
			,"jpg"=>"image/jpg"
			,"mp3"=>"audio/mpeg"
			,"wav"=>"audio/x-wav"
			,"mpeg"=>"video/mpeg"
			,"mpg"=>"video/mpeg"
			,"mpe"=>"video/mpeg"
			,"mov"=>"video/quicktime"
			,"avi"=>"video/x-msvideo"
			,"3gp"=>"video/3gpp"
			,"css"=>"text/css"
			,"jsc"=>"application/javascript"
			,"js"=>"application/javascript"
			,"php"=>"text/html"
			,"htm"=>"text/html"
			,"html"=>"text/html"
	);

	$extension = strtolower(end(explode('.',$file)));
	
	return $mime_types[$extension];
}

/**
 * Returns available hosting types.
 */
function dc_get_hosting_types()
{
	return array(
			"WP_DIRECT" => "Direct Hosting (Wordpress File System)",
			"REDIRECT_URL_OPEN" => "Redirect to different location (Dropbox etc.)"
	);
}

/**
 * Returns if hosting type is default (WP_DIRECT)
 */
function dc_is_default_hosting_type($hosting_type)
{
	return ( '' == $hosting_type || 'WP_DIRECT' == $hosting_type );
}
?>