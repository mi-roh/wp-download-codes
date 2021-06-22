<?php
/**
 * WP Download Codes Plugin
 * 
 * FILE
 * includes/helpers/string.php
 *
 * DESCRIPTION
 * Additional string functions.
 *
 */
 
/**
 * Callback function to trim array value white space
 */
function dc_trim_value(&$value) { 
    $value = trim($value); 
}

/**
 * Checks if given string is a valid URL
 */
function dc_is_valid_url($url) {
	$urlregex = "%^(?:(?:https?|ftp)://)(?:\S+(?::\S*)?@|\d{1,3}(?:\.\d{1,3}){3}|(?:(?:[a-z\d\x{00a1}-\x{ffff}]+-?)*[a-z\d\x{00a1}-\x{ffff}]+)(?:\.(?:[a-z\d\x{00a1}-\x{ffff}]+-?)*[a-z\d\x{00a1}-\x{ffff}]+)*(?:\.[a-z\x{00a1}-\x{ffff}]{2,6}))(?::\d+)?(?:[^\s]*)?$%iu";

	return preg_match($urlregex, $url);
}

/**
 * Generate a random character string
 */
function rand_str( $length = 32, $chars = '' ){
	// Character list
    if ($chars == '') $chars = dc_code_chars();

    // Length of character list
    $chars_length = (strlen($chars) - 1);

    // Start our string
    $string = $chars[rand(0, $chars_length)];
   
    // Generate random string
    for ($i = 1; $i < $length; $i = strlen($string))
    {
        // Grab a random character from our list
        $r = $chars[rand(0, $chars_length)];
       
        // Make sure the same two characters don't appear next to each other
        if ($r != $string[$i - 1]) $string .=  $r;
    }
   
    // Return the string
    return $string;
}
?>
