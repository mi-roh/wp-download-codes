<?php
/**
 * WP Download Codes Plugin
 *
 * FILE
 * includes/download.php
 *
 * DESCRIPTION
 * Processes the download of a file.
 */

/**
 * Sends headers to download file when download code was entered successfully
 */
function dc_send_download_headers() {
    global $wpdb;

    // Only continue if lease is provided as a query parameter
    if ( isset( $_GET['lease'] ) ) {
        // Get details for code and release
        $release = $wpdb->get_row(
            $wpdb->prepare(
                "SELECT r.*, c.ID as code, c.code_prefix, c.code_suffix FROM " . dc_tbl_releases() .
                " r INNER JOIN " . dc_tbl_codes() . " c ON c.release = r.ID WHERE MD5(CONCAT('wp-dl-hash',c.ID)) = %s",
                array( $_GET['lease'] )
            )
        );

        // Get # of downloads with this code
        $downloads = $wpdb->get_row(
            $wpdb->prepare(
                "SELECT COUNT(*) AS downloads FROM " . dc_tbl_downloads() . " WHERE code= %s",
                array( $release->code )
            )
        );

        // Start download if maximum of allowed downloads is not reached
        if ( $downloads->downloads < $release->allowed_downloads ) {

            // Get current IP
            $IP = $_SERVER['REMOTE_ADDR'];

            if ( is_null( dc_get_hosting_type( dc_get_hosting_types() ) ) ) {
                // No Valid Hosting Type
                return;
            }

            // Insert download in downloads table
            $wpdb->insert(
                dc_tbl_downloads(),
                array( 'code' => $release->code, 'IP' => $IP ),
                array( '%d', '%s' )
            );

            if ( dc_is_default_hosting_type( $release->hosting_type ) ) {
                // Download file directly

                // If Apache's xsendfile is enabled (must be installed and working on server side)
                if ( dc_xsendfile_enabled() ) {
                    header( 'X-Sendfile: ' . dc_file_location() . $release->filename );
                    header( 'Content-Type: application/octet-stream' );
                    header( 'Content-Disposition: attachment; filename=\"' . rawurlencode( $release->filename ) . '\"' );
                    exit;
                }

                // Increase timeout for slow connections
                set_time_limit( 0 );

                // Deactivate output compression (required for IE, otherwise Content-Disposition is ignored)
                if ( ini_get( 'zlib.output_compression' ) ) {
                    ini_set( 'zlib.output_compression', 'Off' );
                }

                // Content description
                header( 'Content-Description: File Transfer' );

                // Content disposition
                if ( strpos( $_SERVER ['HTTP_USER_AGENT'], "MSIE" ) > 0 ) {
                    header( 'Content-Disposition: attachment; filename="' . rawurlencode( $release->filename ) . '"' );
                } else {
                    header( 'Content-Disposition: attachment; filename*=UTF-8\'\'' . rawurlencode( $release->filename ) );
                }

                // Content type
                $content_type = dc_header_content_type();
                if ( $content_type == DC_HEADER_CONTENT_TYPE ) {
                    // Send MIME type of current file
                    header( 'Content-Type: ' . get_mime_content_type( dc_file_location() . $release->filename ) );
                } else {
                    // Override content type with header setting
                    header( 'Content-Type: ' . $content_type );
                }

                // Transfer encoding
                header( 'Content-Transfer-Encoding: binary' );

                // Content length
                header( 'Content-Length: ' . filesize( dc_file_location() . $release->filename ) );

                // Cache handling
                header( 'Cache-Control: must-revalidate, post-check=0, pre-check=0' );
                header( 'Pragma: public' );
                header( 'Expires: 0' );

                // Stream file
                ob_clean();
                flush();
                $handle    = fopen( dc_file_location() . $release->filename, 'rb' );
                $chunksize = 1 * ( 1024 * 1024 );
                $buffer    = '';
                if ( $handle === false ) {
                    exit;
                }
                while ( ! feof( $handle ) ) {
                    $buffer = fread( $handle, $chunksize );
                    echo $buffer;
                    flush();
                }

                // Close file
                fclose( $handle );

                // Exit
                exit;
            } else if ( 'REDIRECT_URL_OPEN' == $release->hosting_type ) {
                // Redirect to URL
                header( "Location: " . $release->url );
                die();
            }
        }
    }
}
