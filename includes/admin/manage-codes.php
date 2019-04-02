<?php
/**
 * WP Download Codes Plugin
 * 
 * FILE
 * includes/admin/manage-codes.php
 *
 * DESCRIPTION
 * Contains functions to manage the download codes for a release.
 */

/**
 * Manages download codes
 */
function dc_manage_codes() {
	global $wpdb;
	
	// Set option to enable big SQL selects
	$wpdb->query('SET OPTION SQL_BIG_SELECTS = 1');

	// GET parameters
	$get_release 	= $_GET['release'];
	$get_group	= $_GET['group'];
	$get_action 	= $_GET['action'];

	// POST parameters
	$post_release	= $_POST['release'];
	
	// List of releases
	$releases = dc_get_releases();
	if ( $get_release == '' &&  $post_release == '' ) {
		$release_id = $releases[0]->ID;
	} 
	elseif ( $post_release != '') {
		$release_id = $post_release;
	}
	elseif ( $get_release != '') {
		$release_id = $get_release;
	}
	
	// Show page title
	echo '<div class="wrap">';
	echo '<h2>Download Codes &raquo; Manage Codes</h2>';
		
	switch( $get_action ) 
	{
		case 'make-final':
			// Finalize new codes
			$finalize_count = dc_finalize_codes( $release_id, $get_group );
			echo dc_admin_message( '' . $finalize_count . ' download code(s) were finalized' );
			
			break;
			
		case 'delete':
			// Delete (not finalized) codes
			$deleted_count = dc_delete_codes( $release_id, $get_group );
			echo dc_admin_message( '' . $deleted_count . ' download code(s) were deleted' );
		
			break;
			
		case 'generate':
			// Generate new codes
			$message = dc_generate_codes( 	$release_id,
							strtoupper(trim( $_POST['prefix'] )),
							trim( $_POST['codes'] ),
							trim( $_POST['characters'] ) );
			echo $message;
			
			break;
		
		case 'import':
			// Import existing codes
			$message = dc_import_codes( 	$release_id,
							strtoupper(trim( $_POST['import-prefix'] )),
							trim( $_POST['import-codes'] ));
			echo $message;
			
			break;
			
		case 'reset':		
			// Reset codes
			$reset_count	= dc_reset_downloads( $_POST['download-ids'] );
			echo dc_admin_message( '' . $reset_count . ' downloads reset' );
		
			break;
	}
	
	if ( sizeof( $releases ) == 0) {
		// Display message if no release exists yet
		echo dc_admin_message( 'No releases have been created yet' );
		echo '<p><a class="button-primary" href="admin.php?page=dc-manage-releases&amp;action=add">Add New Release</a></p>';		
	}
	else {
		// There are some releases	
		echo '<form action="admin.php?page=dc-manage-codes&action=select" method="post">';
		echo '<input type="hidden" name="action" value="select" />';
		
		// Display release picker
		echo '<h3>Select a Release: ';
		echo '<select name="release" id="release" onchange="submit();">';
		foreach ( $releases as $release ) {
			echo '<option value="' . $release->ID . '"' . ( $release->ID == $release_id ? ' selected="selected"' : '' ) . '>' . ( $release->artist ? $release->artist . ' - ' : '' ) . $release->title . ' (' . $release->filename . ')</option>';
		}
		echo '</select>';
		echo '</h3>';
		echo '</form>';
		
		// Get codes for current release
		$code_groups = dc_get_code_groups( $release_id );
		$release = $code_groups[0];
		
		if ( sizeof($code_groups) > 0) {		
			// Subtitle
			echo '<h3>' . $release->artist . ' - ' . $release->title . ' (' . $release->filename . ') [ID: ' . $release->ID . ']</h3>';
			
			// Show shortcode example
			echo '<p><span class="description">Insert the following shortcode into a page or article:</span> <code>[download-code id="' . $release_id . '"]</code></p>';
					
			echo '<table class="widefat dc_codes">';
			
			echo '<thead>';
			echo '<tr><th>Prefix</th><th>Finalized</th><th>Codes</th><th>Sample Code</th><th>Downloaded</th><th>Actions</th></tr>';
			echo '</thead>';
			
			// List codes
			echo '<tbody>';
			
			// Check that codes are actual data
			if ( $code_groups[0]->group != '' ) {
				foreach ( $code_groups as $code_group ) {
					echo '<tr><td>' . $code_group->code_prefix . '</td><td>' . ( $code_group->final == 1 ? "Yes" : "No" ) . '</td>';
					echo '<td>' . $code_group->codes . '</td>';
					echo '<td>' . $code_group->code_prefix . $code_group->code_example . '</td>';
					echo '<td>' . $code_group->downloads . ' (' . $code_group->downloaded_codes . ' ' . ( $code_group->downloaded_codes == 1 ? 'code' : 'codes' ) . ')</td>';
					echo '<td>';
					
					// Link to make codes final/delete codes or to export final codes
					if ( $code_group->final == 0 ) {
						echo '<a href="admin.php?page=dc-manage-codes&amp;release=' . $release->ID . '&amp;group=' . $code_group->group . '&amp;action=make-final" class="action-finalize">Finalize</a> | ';
						echo '<a href="admin.php?page=dc-manage-codes&amp;release=' . $release->ID . '&amp;group=' . $code_group->group . '&amp;action=delete" class="action-delete">Delete</a>';
					}
					else {
						echo '<a href="admin.php?page=dc-manage-codes&amp;release=' . $release->ID . '&amp;group=' . $code_group->group . '&amp;action=list" class="action-list" rel="dc_list-' . $code_group->group . '">List codes</a> | ';
						echo '<a href="admin.php?page=dc-manage-codes&amp;release=' . $release->ID . '&amp;group=' . $code_group->group . '&amp;action=report" class="action-report" rel="dc_downloads-' . $code_group->group . '">View report</a>';
					}
					
					echo '</td></tr>';
				}				
			}
			echo '</tbody>';
			
			echo '<tfoot>';
			echo '<tr><th>Prefix</th><th>Finalized</th><th>Codes</th><th>Sample Code</th><th>Downloaded</th><th>Actions</th></tr>';
			echo '</tfoot>';
		
			echo '</table>';
			
			// Output codes and downloads for lightbox option
			foreach ( $code_groups as $code_group ) 
			{
				dc_list_codes( $release_id, $code_group->group, FALSE );
				dc_list_downloads( $release_id, $code_group->group, FALSE, 'admin.php?page=dc-manage-codes&amp;action=reset' );
			}
			
			// Show form to add codes
			echo '<form id="form-manage-codes" action="admin.php?page=dc-manage-codes&amp;action=generate" method="post">';
			echo '<input type="hidden" name="release" value="' . $release->ID . '" />';
			
			echo '<h3>Generate New Batch of Codes</h3>';
			
			echo '<table class="form-table">';

			echo '<tr valign="top">';
			echo '<th scope="row"><label for="new-prefix">Code Prefix</label></th>';
			echo '<td><input type="text" name="prefix" id="new-prefix" class="small-text" value="' . $post_prefix . '" />';
			echo ' <span class="description">First characters of each code</span></td>';
			echo '</tr>';

			echo '<tr valign="top">';
			echo '<th scope="row"><label for="new-quantity">Quantity</label></th>';
			echo '<td><input type="text" name="codes" id="new-quantity" class="small-text" maxlength="5" value="' . $post_codes .'" />';
			echo ' <span class="description">Number of codes to generate</span></td>';
			echo '</tr>';

			echo '<tr valign="top">';
			echo '<th scope="row"><label for="new-length">Length</label></th>';
			echo '<td><input type="text" name="characters" id="new-length" class="small-text" maxlength="2" value="' . ( $post_characters != '' ? $post_characters : '8' ) .'" />';
			echo ' <span class="description">Number of random characters each code contains</span></td>';
			echo '</tr>';

			echo '</table>';
			
			echo '<p class="submit">';
			echo '<input type="submit" name="submit" class="button-secondary" value="Generate Codes" />';
			echo '</p>';

			echo '</form>';
			
			// Show form to import existing codes
			echo '<form action="admin.php?page=dc-manage-codes&amp;action=import" method="post">';
			echo '<input type="hidden" name="release" value="' . $release->ID . '" />';

			echo '<h3>Import Existing Download Codes</h3>';
			
			echo '<table class="form-table">';
			
			echo '<tr valign="top">';
			echo '<th scope="row"><label for="import-prefix">Code Prefix</label></th>';
			echo '<td><input type="text" name="import-prefix" id="import-prefix" class="small-text" value="' . $_POST['import-prefix'] . '" />';
			echo ' <span class="description">First characters of each code. It is recommended that all of your codes to be imported have a common prefix. If this is not the case, this field can be left empty.</span></td>';
			echo '</tr>';

			echo '<tr valign="top">';
			echo '<th scope="row"><label for="import-codes">List of Codes</label></th>';
			echo '<td><textarea name="import-codes" id="import-codes" cols="20" rows="20" class="small-text">' . $_POST['import-codes'] .'</textarea>';
			echo ' <span class="description">Plain list of codes to be imported (separated by linebreaks)</span></td>';
			echo '</tr>';

			echo '</table>';
			
			echo '<p class="submit">';
			echo '<input type="submit" name="submit" class="button-secondary" value="Import Codes" />';
			echo '</p>';

			echo '</form>';
		}
		
		// Show list of download codes or download report in case lightbox option is not applicable
		switch( $get_action )
		{
			case 'list':
				echo '<h3>List of Download Codes</h3>';
				dc_list_codes( $release_id, $get_group );
	
				break;
			case 'report':
				echo '<h3>Code Usage Report</h3>';
				dc_list_downloads( $release_id, $get_group);	
			
				break;
		}
	}	
	echo '</div>';
}
?>