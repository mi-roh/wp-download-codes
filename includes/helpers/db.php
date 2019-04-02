<?php
/**
 * WP Download Codes Plugin
 * 
 * FILE
 * includes/helpers/db.php
 *
 * DESCRIPTION
 * Database related plugin functions (creation and modification of releases, download codes etc.).
 *
 */

/**
 * Returns the name of the downloads table.
 */
function dc_tbl_downloads() {
   global $wpdb;
   
   return $wpdb->prefix . "dc_downloads";
}

/**
 * Returns the name of the releases table.
 */
function dc_tbl_releases() {
   global $wpdb;
   
   return $wpdb->prefix . "dc_releases";
}

/**
 * Returns the name of the codes table.
 */
function dc_tbl_codes() {
   global $wpdb;
   
   return $wpdb->prefix . "dc_codes";
}

/**
 * Returns the name of the code groups table.
 */
function dc_tbl_code_groups() {
   global $wpdb;
   
   return $wpdb->prefix . "dc_code_groups";
}

/**
 * Output a list of download codes.
 */
function dc_list_codes( $release_id, $group, $show = TRUE )
{
   global $wpdb;
   
   if (!$group) $group = 'all';
   
   echo '<div id="dc_list-' . $group . '" class="updated fade dc_list" ' . ( $show ? '' : 'style="display: none;"' ) . '>';
    
   $codes = dc_get_codes( $release_id, $group );
		   
   if ( $codes ) 
   {
	   foreach ( $codes as $code ) {
		   echo '<p>'. $code->code_prefix . $code->code_suffix . '</p>' . "\n";
	   }
   }
   else {
	   echo '<p>No download codes</p>';
   }
   
   echo '</div>';
}

/**
 * Output a list of downloads.
 */
function dc_list_downloads( $release_id, $group, $show = TRUE, $action = '' )
{
   global $wpdb;
   
   // Get release
   $release = dc_get_release( $release_id );

   echo '<div id="dc_downloads-' . ( '' != $group ? $group : $release_id ) . '" class="dc_downloads" ' . ( $show ? '' : 'style="display: none;"' ) . '>';
   
   if ($group == '') $group = 'all';
   
   if ( !$show ) {
      echo '<h3>Download Report</h3>' . "\n";
      echo '<p>for <em>' . $release->title . '</em></p>' . "\n";
   }
   
   $downloads = $wpdb->get_results( 
			"SELECT 	r.title, 
				     r.artist,
				     c.code_prefix,
				     c.code_suffix,
				     d.ID,
				     d.IP,
				     DATE_FORMAT(d.started_at, '%b %e, %Y @ %H:%i:%S') AS download_time 
			FROM 		(" . dc_tbl_releases() . " r 
			INNER JOIN 	" . dc_tbl_codes() . " c 
			ON 			c.release = r.ID) 
			INNER JOIN 	" . dc_tbl_downloads() . " d 
			ON 			d.code = c.ID 
			WHERE 		r.ID = $release_id " . ( $group == 'all' ? "" : "AND c.group = $group" ) . " 
			ORDER BY 	d.started_at" );
   
   if ( $downloads )
   {
      // Include form to reset codes
      if ( '' != $action ) {
	 echo '<form action="' . $action . '" method="post">';
	 echo '<input type="hidden" name="release" value="' . $release_id . '" />';
      } 

      echo '<table class="widefat">';
      echo '<thead><tr>';
      
      // Include form to reset codes
      if ( '' != $action ) {
	echo '<th><input type="checkbox" class="cb-select-all" rel="' . ( $group == 'all' ? $release_id : $group ) . '" /></th>'; 
      }
      
      echo '<th>Code</th><th>IP Address</th><th>Date</th></tr></thead>';
      foreach ( $downloads as $download ) {
	 echo '<tr>';
	 
	 // Include form to reset codes
	 if ( '' != $action ) {
	    echo '<td><input type="checkbox" class="cb-related-' . ( $group == 'all' ? $release_id : $group ) . '" name="download-ids[]" id="download-id-' . $download->ID . '" value="' . $download->ID . '" /></td>';
	 }
	 
	 echo '<td>' . $download->code_prefix . $download->code_suffix . '</td>';
	 echo '<td>' . $download->IP . '</td>';
	 echo '<td>' . $download->download_time . '</td>';
	 echo '</tr>' . "\n";
      }
      echo '</table>';
      
      // Include form to reset codes
      if ( '' != $action ) {
	 echo '<p class="submit">';
	 echo '<input type="submit" name="submit" class="button-secondary" value="Clear Selected Downloads" />';
	 echo '</p>';
	 echo '</form>';
      }
   } 
   else {
      echo '<p>No downloads yet</p>';
   }
   echo '</div>';
}

/**
 * Gets all the code groups for a release
 */
function dc_get_code_groups( $release_id )
{
   global $wpdb;
   $groups = $wpdb->get_results( 
		     "SELECT 	r.ID, 
					     r.title, 
					     r.artist, 
					     r.filename, 
					     COUNT(d.ID) AS downloads,
					     COUNT(DISTINCT d.code) AS downloaded_codes,
					     c.code_prefix, 
					     c.group, 
					     c.final, 
					     COUNT(DISTINCT c.ID) AS codes, 
					     MIN(c.code_suffix) as code_example 
		     FROM 		" . dc_tbl_releases() . " r 
		     LEFT JOIN 	(" . dc_tbl_codes() . " c
		     LEFT JOIN 	". dc_tbl_downloads() . " d 
		     ON 			d.code = c.ID) 
		     ON 			c.release = r.ID 
		     WHERE 		r.ID = $release_id 
		     GROUP BY 	r.ID, 
					     r.filename, 
					     r.title, 
					     r.artist, 
					     c.code_prefix, 
					     c.group, 
					     c.final 
		     ORDER BY 	c.code_prefix" );
   
   return $groups;
}

function dc_get_codes( $release_id, $group )
{
   global $wpdb;
   
   if (!$group) $group = 'all';
   
   $codes = $wpdb->get_results( "
	   SELECT 		r.title, 
				   r.artist, 
				   c.code_prefix, 
				   c.code_suffix 
	   FROM 		" . dc_tbl_releases() . " r 
	   INNER JOIN 	" . dc_tbl_codes() . " c 
	   ON 			c.release = r.ID 
	   WHERE 		r.ID = $release_id " . ( $group == 'all' ? "" : "AND c.group = $group" ) . " 
	   ORDER BY 	c.group, c.code_prefix, c.code_suffix" );
   
   return $codes;
}

/**
 * Finalize the code group for a release.
 */
function dc_finalize_codes( $release_id, $group )
{
   global $wpdb;
   return $wpdb->query( "UPDATE " . dc_tbl_codes() . " SET `final` = 1 WHERE `release` = $release_id AND `group` = $group" );
}

/**
 * Delete the code group for a release.
 */
function dc_delete_codes( $release_id, $group )
{
   global $wpdb;
   
   // Delete codes
   $deleted_count = $wpdb->query( "DELETE FROM " . dc_tbl_codes() . " WHERE `release` = $release_id AND `group` = $group" );
   
   // Delete all code groups which are not used any more
   $wpdb->query( "DELETE FROM " . dc_tbl_code_groups() . " WHERE `ID` NOT IN (SELECT `group` FROM " . dc_tbl_codes() . ")" );
   
   return $deleted_count;
}

/**
 * Get all the releases.
 */
function dc_get_releases()
{
   global $wpdb;
   return $wpdb->get_results( 
	   "SELECT 	r.ID, 
	   r.title, 
	   r.artist, 
	   r.hosting_type,
	   r.filename,
	   r.url,
	   ccq.code_count AS codes,
	   dcq.download_count AS downloads
	   FROM 	(" . dc_tbl_releases() . " r 
			   LEFT JOIN (SELECT `release`, COUNT(*) AS code_count
				   FROM " . dc_tbl_codes() . "
				   GROUP BY `release`) AS ccq 
			   ON ccq.release = r.ID)
			   LEFT JOIN (SELECT `release`, COUNT(*) AS download_count
				   FROM ". dc_tbl_downloads() . " d
				   INNER JOIN " . dc_tbl_codes() . " c
				   ON d.code = c.id
				   GROUP BY `release`) AS dcq
			   ON dcq.release = r.ID
	   ORDER BY 	r.artist, r.title");
}

/**
 * Get a particular release.
 */
function dc_get_release( $release_id )
{
   global $wpdb;
   return $wpdb->get_row( "SELECT * FROM " . dc_tbl_releases() . " WHERE ID = $release_id ");
}

/**
 * Generate codes for a release.
 */
function dc_generate_codes( $release_id, $prefix, $number_of_codes, $number_of_characters )
{
   global $wpdb;
   
   // Make sure all fields are filled out
   if ( !is_numeric($number_of_codes) || !is_numeric($number_of_characters) ) {
      return dc_admin_message( "Make sure that the code quantity and length are valid numbers", "error" );
   }
   
   // Create new code group
   $wpdb->insert(	dc_tbl_code_groups(), 
			array( 'release' => $release_id ),
			array( '%d' ) );
   $group_id = $wpdb->insert_id;

   // Create desired number of random codes
   for ( $i = 0; $i < $number_of_codes; $i++ ) {	
      // Create random code
      $code_unique = false;
      while ( !$code_unique ) {
	 $suffix = rand_str( $number_of_characters );
	 
	 // Check if code already exists
	 $code_db = $wpdb->get_row( "SELECT ID FROM " . dc_tbl_codes() . " WHERE code_prefix = `$prefix` AND code_suffix = `$suffix` AND `release` = " . $release_id );
	 $code_unique = ( sizeof( $code_db ) == 0);			
      }
      
      // Insert code
      $wpdb->insert(	dc_tbl_codes(), 
			array( 'code_prefix' => $prefix, 'code_suffix' => $suffix, 'group' => $group_id, 'release' => $release_id ),
			array( '%s', '%s', '%d', '%d' ) );
   }
   
   return dc_admin_message( $number_of_codes . " code" . ( $number_of_codes != 1 ? "s have" : " has" ) . " been created" );
}

/**
 * Import existing codes for a release.
 */
function dc_import_codes( $release_id, $prefix, $list_of_codes )
{
   global $wpdb;
   
   // Prepare array of import codes
   $arr_codes = split( "\n", str_replace( "\r", '', $list_of_codes ) );
      
   // Verify code prefixes before inserting
   if ( '' != $prefix ) {
      foreach ( $arr_codes as $code ) {
	 // Check if code starts with prefix, otherwise return error message
	 if ( '' != $code && strpos( $code, $prefix ) !== 0) {
	    return dc_admin_message( 'Not all codes start with the given prefix "' . $prefix . '"', 'error' );
	 }
      }
   }
   
   // Verify that none of the existing codes collides with the import codes
   $existing_codes = $wpdb->get_results( 'SELECT CONCAT(code_prefix, code_suffix) AS `code` FROM ' . dc_tbl_codes() . ' WHERE CONCAT(code_prefix, code_suffix) IN ("' . join( '","', $arr_codes ) . '")', ARRAY_N );
   if ( sizeof( $existing_codes ) > 0 ) {
      $existing_codes_list = array();
      foreach ( $existing_codes as $existing_code ) {
	 array_push( $existing_codes_list, $existing_code[0] );
      }
      return dc_admin_message( 'The codes could not be imported, because the following codes already exist for this release: ' . join( ", ", $existing_codes_list) , 'error' );
   }
   
   // Create new code group
   $wpdb->insert(	dc_tbl_code_groups(), 
			array( 'release' => $release_id ),
			array( '%d' ) );
   $group_id = $wpdb->insert_id;

   // Import codes
   $number_of_codes = 0;
   foreach ( $arr_codes as $code ) {	
      if ( '' != $code ) {      
	 // Insert code
	 $wpdb->insert(	dc_tbl_codes(), 
			   array( 'code_prefix' => $prefix,
				 'code_suffix' => substr( $code, strlen( $prefix )),
				 'group' => $group_id,
				 'release' => $release_id ),
			   array( '%s', '%s', '%d', '%d' ) );
	 $number_of_codes++;
      }
   }
   
   return dc_admin_message( $number_of_codes . " code" . ( $number_of_codes != 1 ? "s have" : " has" ) . " been imported" );
}

/**
 * Reset the downloads for a release.
 */
function dc_reset_downloads( $download_ids )
{
   global $wpdb;
   if (!$download_ids) return 0;
   
   return $wpdb->query( 'DELETE FROM ' . dc_tbl_downloads() . ' WHERE `ID` IN ("' . join( '","', $download_ids ) . '")' );
}

/**
 * Add a new release.
 */
function dc_add_release()
{
   global $wpdb;
   
   $title = trim($_POST['title']);
   $artist = trim($_POST['artist']);
   $hosting_type = $_POST['hosting_type'];
   $downloads = $_POST['downloads'];
   $filename = $_POST['filename'];
   $url = $_POST['url'];
   
   $errors = array();
   
   // Check if all fields have been filled out properly
   if ( '' == $title ) {
      $errors[] = "The title must not be empty";	
   }
   if ( dc_is_default_hosting_type( $hosting_type ) && '' == $filename ) {
      $errors[] = "Please choose a valid file for this release";	
   }
   if ( 'REDIRECT_URL_OPEN' == $hosting_type && !dc_is_valid_url($url) ) {
      $errors[] = "Please choose a valid URL for this release";	
   }
   if ( !is_numeric( $downloads ) ) {
      $errors[] = "Allowed downloads must be a number";
   }
   
   // Update or insert if no errors occurred.
   if ( !sizeof($errors) ) 
   {
      return 
      $wpdb->insert(	dc_tbl_releases(), 
			array( 'title' => $title, 'artist' => $artist, 'filename' => $filename, 'hosting_type' => $hosting_type, 'url' => $url, 'allowed_downloads' => $downloads),
			array( '%s', '%s', '%s', '%s', '%s', '%d' ) );
   } else
   {
      return $errors;
   }
}

/**
 * Edit a release.
 */
function dc_edit_release()
{
   global $wpdb;

   $title = trim($_POST['title']);
   $artist = trim($_POST['artist']);
   $downloads = $_POST['downloads'];
   $hosting_type = $_POST['hosting_type'];
   $release_id = $_POST['release'];
   $filename = $_POST['filename'];
   $url = $_POST['url'];
   
   $errors = array();
   
   // Check if all fields have been filled out properly
   if ( '' == $title ) {
      $errors[] = "The title must not be empty";	
   }
   if ( dc_is_default_hosting_type( $hosting_type ) && '' == $filename ) {
      $errors[] = "Please choose a valid file for this release";	
   }
   if ( 'REDIRECT_URL_OPEN' == $hosting_type && !dc_is_valid_url($url) ) {
      $errors[] = "Please choose a valid URL for this release";	
   }
   if ( !is_numeric( $downloads ) ) {
      $errors[] = "Allowed downloads must be a number";
   }
   
   // Update or insert if no errors occurred.
   if ( !sizeof($errors) ) 
   {
      return 
      $wpdb->update(	dc_tbl_releases(), 
			array( 'title' => $title, 'artist' => $artist, 'filename' => $filename, 'hosting_type' => $hosting_type, 'url' => $url, 'allowed_downloads' => $downloads),
			array( 'ID' => $release_id ),
			array( '%s', '%s', '%s', '%s', '%s', '%d' ) );
   } else
   {
      return $errors;
   }
}

/**
 * Delete a release
 */
function dc_delete_release( $release_id )
{
   global $wpdb;
   
   $result = 0;
   
   // Delete release
   $result += $wpdb->query( $wpdb->prepare( "DELETE FROM " . dc_tbl_releases() . " WHERE `ID` = %d", array( intval( $release_id ) ) ) );
   
   // Delete code groups
   $result += $wpdb->query( $wpdb->prepare( "DELETE FROM " . dc_tbl_code_groups() . " WHERE `release` = %d", array( intval( $release_id ) ) ) );
   
   // Delete codes
   $result += $wpdb->query( $wpdb->prepare( "DELETE FROM " . dc_tbl_codes() . " WHERE `release` = %d", array( intval( $release_id ) ) ) );
   
   return $result;
}
?>