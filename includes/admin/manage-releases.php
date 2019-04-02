<?php
/**
 * WP Download Codes Plugin
 * 
 * FILE
 * includes/admin/manage-releases.php
 *
 * DESCRIPTION
 * Contains functions to manage the releases.
 */

/**
 * Manages releases
 */
function dc_manage_releases() {
	global $wpdb;
	
	$wpdb->query('SET OPTION SQL_BIG_SELECTS = 1');

	// Get parameters
	$get_action 	= $_GET['action'];
	$get_release 	= $_GET['release'];

	// Post parameters
	$post_action = $_POST['action'];
	$post_release = $_POST['release'];
	
	// Show page title
	echo '<div class="wrap">';
	echo '<h2>Download Codes &raquo; Manage Releases</h2>';

	switch( $get_action )
	{
		case 'edit':
		case 'add':
			// Update or insert release
			if ( isset($_POST['submit']) ) {
				
				if ( $post_action == 'add' ) {
					$result = dc_add_release();
					if ( is_array($result) )
					{
						echo dc_admin_message( implode( '</p><p>', $result ) );
					} 
					else {
						if ( $result === FALSE )
							echo dc_admin_message( 'There was an error adding the release' ); 
						else {
							echo dc_admin_message( 'The release was added successfully' );
							$add_success = true;
						}
					}
				}
				if ( $post_action == 'edit' ) {
					$result = dc_edit_release();
					if ( is_array($result) )
					{
						echo dc_admin_message( implode( '</p><p>', $result ) );
					} 
					else {
						if ( $result === FALSE )
							echo dc_admin_message( 'There was an error updating the release' ); 
						else {
							echo dc_admin_message( 'The release was updated successfully' );			
							$edit_success = true;
						}
					}
				}
			}
			break;
		case 'delete':
			$result = dc_delete_release( $get_release );
			if ( $result ) {
				echo dc_admin_message( 'The release was deleted successfully' );
			} 
			else {
				echo dc_admin_message( 'There was an error deleting the release' );
			}
			break;
	}

	if ( ( $get_action == 'edit' || $get_action == 'add' ) && !$add_success ) {
		
		//*********************************************
		// Add or edit a release
		//*********************************************
	
		// Get zip files in download folder
		$files = scandir( dc_file_location() );
		$num_download_files = 0;
		foreach ( $files as $filename ) {
			if ( in_array(strtolower( substr($filename,-3) ), dc_file_types() ) ) {
				$num_download_files++;
			}
		}
		if ( $num_download_files == 0) {
			echo dc_admin_message( 'No files have been uploaded to the releases folder: <em>' . dc_file_location() . '</em></p><p><strong>You must do this first before adding a release!</strong>' );
		}
		
		// Get current release
		if ( '' != $get_release ) {
			$release = dc_get_release( $get_release );
		}
		if ( '' != $post_release ) {
			$release = dc_get_release( $post_release );
		}
		
		// Write page subtitle
		echo '<h3>' . ( ( 'add' == $get_action ) ? 'Add New' : 'Edit' ) . ' Release</h3>';
		echo '<p><a href="admin.php?page=dc-manage-releases">&laquo; Back to releases</a></p>';
		
				
		// Display form
		echo '<form action="admin.php?page=dc-manage-releases&action=' . $get_action . '" method="post">';
		echo '<input type="hidden" name="release" value="' . $release->ID . '" />';
		echo '<input type="hidden" name="action" value="' . $get_action . '" />';
		
		echo '<table class="form-table">';
		
		// Title
		echo '<tr valign="top">';
		echo '<th scope="row"><label for="release-title">Title</label></th>';
		echo '<td><input type="text" name="title" id="release-title" class="regular-text" value="' . $release->title . '" />';
		echo '<span class="description">For example, the album title</span></td>';
		echo '</tr>';
		
		// Artist
		echo '<tr valign="top">';
		echo '<th scope="row"><label for="release-artist">Artist (optional)</label></th>';
		echo '<td><input type="text" name="artist" id="release-artist" class="regular-text" value="' . $release->artist . '" />';
		echo '<span class="description">The band or artist</span></td>';
		echo '</tr>';
		
		// Hosting Type
		echo '<tr valign="top">';
		echo '<th scope="row"><label for="hosting-type">Hosting Type</label></th>';
		echo '<td><select name="hosting_type" id="hosting_type">';
		foreach ( dc_get_hosting_types() as $key => $value ) {
			echo '<option' . ( $key == $release->hosting_type ? ' selected="selected"' : '' ) . ' value="' . $key . '">' . $value . '</option>';
		}
		echo '</select></td>';
		echo '</tr>';
		
		// Hosting Type WP_DIRECT: File
		echo '<tr valign="top" id="manage-releases-hosting-type-wp-direct" style="display: none;">';
		echo '<th scope="row"><label for="filename" id="filename-label">File</label></th>';
		echo '<td>' . dc_file_location() . ' <select name="filename" id="filename">';
		
		$allowed_file_types = dc_file_types(); // Get array of allowed file types/extensions
		
		foreach ( $files as $filename ) { // List all files matching the allowed extensions
			$file_extension_array = preg_split( '/\./', $filename );
			$file_extension = strtolower( $file_extension_array[ sizeof( $file_extension_array ) - 1 ] );
			if ( in_array( $file_extension, $allowed_file_types ) ) {
				echo '<option' . ( $filename == $release->filename ? ' selected="selected"' : '' ) . '>' . $filename . '</option>';
			}
		}
		echo '</select></td>';
		echo '</tr>';
		
		// Hosting Type REDIRECT_URL_OPEN: Url
		echo '<tr valign="top" id="manage-releases-hosting-type-redirect-url-open" style="display: none;">';
		echo '<th scope="row"><label for="url" id="url-label">URL</label></th>';
		echo '<td><input type="text" name="url" id="url" class="regular-text" value="' . $release->url . '" />';
		echo '</tr>';
		
		// Allowed downloads
		echo '<tr valign="top">';
		echo '<th scope="row"><label for="release-downloads">Allowed downloads</label></th>';
		echo '<td><input type="text" name="downloads" id="release-downloads" class="small-text" value="' . ( $release->allowed_downloads > 0 ? $release->allowed_downloads : DC_ALLOWED_DOWNLOADS ) . '" />';
		echo '<span class="description">Maximum number of times each code can be used</span></td>';
		echo '</tr>';
		
		echo '</table>';
		
		// Submit
		echo '<p class="submit">';
		echo '<input type="submit" name="submit" class="button-primary" value="' . ( $get_action == 'edit' ? 'Save Changes' : 'Add Release' ) . '" />';
		echo '</p>';

		echo '</form>';
	}
	else {
		//*********************************************
		// List releases
		//*********************************************
		
		// Write page subtitle
		echo '<h3>Releases</h3>';
		
		// Get releases
		$releases = dc_get_releases();
		
		// Check if the releases are empty
		if ( sizeof( $releases ) == 0) {
			echo dc_admin_message( 'No releases have been created yet' );
			echo '<p>You might want to <a href="admin.php?page=dc-manage-releases&action=add">add a new release</a></p>';
		}
		else {		
			echo '<table class="widefat">';
			
			echo '<thead>';
			echo '<tr><th>Title</th><th>Artist</th><th>ID</th><th>Hosting Type</th><th>File/URL</th><th>Codes</th><th>Downloaded</th><th>Actions</th></tr>';
			echo '</thead>';
			
			echo '<tbody>';
			foreach ( $releases as $release ) {
				echo '<tr>';
				echo '<td><strong>' . $release->title . '</strong></td><td>' . $release->artist . '</td>';
				echo '<td>' . $release-> ID . '</td>';
				echo '<td>' . dc_get_hosting_types()[$release->hosting_type] . '</td>';
				echo '<td>' . ( dc_is_default_hosting_type( $release->hosting_type ) ? $release->filename : $release->url ) . '</td>';
				echo '<td>' . $release->codes . '</td><td>' . $release->downloads . '</td>';
				echo '<td>';
				echo '<a href="admin.php?page=dc-manage-releases&amp;release=' . $release->ID . '&amp;action=edit" class="action-edit">Edit</a> | ';
				echo '<a href="admin.php?page=dc-manage-codes&amp;release=' . $release->ID . '" class="action-manage">Manage codes</a> | '; 
				echo '<a href="admin.php?page=dc-manage-codes&amp;release=' . $release->ID . '&amp;action=report" class="action-report" rel="dc_downloads-' . $release->ID . '">View report</a> | ';
				echo '<a href="admin.php?page=dc-manage-releases&amp;release=' . $release->ID . '&amp;action=delete" class="action-delete">Delete</a>';
				echo '</td>';
				echo '</tr>';
			}
			echo '</tbody>';
			
			echo '<tfoot>';
			echo '<tr><th>Title</th><th>Artist</th><th>ID</th><th>Hosting Type</th><th>File/URL</th><th>Codes</th><th>Downloaded</th><th>Actions</th></tr>';
			echo '</tfoot>';

			echo '</table>';
			
			foreach ( $releases as $release ) {
				dc_list_downloads( $release->ID, NULL, FALSE );
			}
		}

		// Show link to add a new release
		echo '<p><a class="button-primary" href="admin.php?page=dc-manage-releases&amp;action=add">Add New Release</a></p>';		
	}
	
	echo '</div>';
}
?>