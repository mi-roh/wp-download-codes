/**
 * WP Download Codes Plugin
 * 
 * FILE
 * resources/wp-download-codes.js
 *
 * DESCRIPTION
 * Contains JS functions for plugin
 *
 */

var $ = jQuery.noConflict();

$(document).ready(function() {
	// Add lightbox DOM elements
	$("body").append('<div id="lightbox"><div class="close"></div><div id="lightbox-content" class="content"></div><textarea id="lightbox-textarea"></textarea></div>'); 
	$("body").append('<div id="overlay" class="overlay"></div>');	

	// Open lightbox to list download codes
	$("a.action-list").click(function() {
		var lightbox = $(this).attr("rel");
		return openLightbox(lightbox, true);	
	});
	
	// Open lightbox to list downloads
	$("a.action-report").click(function() {
		var lightbox = $(this).attr("rel");
		return openLightbox(lightbox);	
	});
	
	// Add confirm step before deleting release
	$("a.action-delete").click(function() {
		return confirm("Are you absolutely sure? This cannot be undone!");
	});
	
	// Add confirm step before finalizing codes
	$("a.action-finalize").click(function() {
		return confirm("Are you absolutely sure? Codes cannot be deleted after they're finalized. (Only the whole release can be deleted including all codes.)");
	});
	
	// Add confirm step if more than 500 download codes shall be created
	$("form#form-manage-codes").submit(function() {
		// Get number of download codes to be created
		var numberOfCodes = $('#new-quantity').val();
		
		// Check if number of codes exceeds 500
		if ($.isNumeric(numberOfCodes) && numberOfCodes >= 500) {
			return confirm("Are you sure that you want to create that many codes?");
		}
		
		return true;
	});
	
	// Close button on lightbox
	$("#lightbox .close").click(closeLightbox);
	
	// Add handlers for hosting types (admin page "Manage Releases")
	$("#hosting_type").change(function() { renderFilenameFieldByHostingType(); });
	renderFilenameFieldByHostingType();
});

/***********************
// open lightbox
*/
function openLightbox($lightbox, $selectable) {
	var selectable = $selectable ? true : false;
	var textarea;

	$("#overlay").show().live("click", closeLightbox);	
	$("#lightbox").show();
	if(selectable) {
		$("#lightbox-textarea").show().html($("#" + $lightbox).text());
		$("#lightbox-content").hide();
	} else {
		$("#lightbox-content").show().html($("#" + $lightbox).html());
		$("#lightbox-textarea").hide();
	}
	
	// Enable select/deselect all in download reports
	$("input.cb-select-all").click(function() {
		var relatedId = $(this).attr("rel");
		var checked = $(this).prop('checked');
		$("input.cb-related-" + relatedId).each(function () {
			$(this).prop('checked', checked);
		});
	});	
	return false;
}

/***********************
// close lightbox
*/
function closeLightbox() {
	$("#overlay").hide();
	$("#lightbox").hide();	
	return false;
}

function renderFilenameFieldByHostingType() {
	var hosting_type = $("#hosting_type").val();
	
	switch (hosting_type) {
		case "REDIRECT_URL_OPEN":
			$("#manage-releases-hosting-type-wp-direct").hide();
			$("#manage-releases-hosting-type-redirect-url-open").show();
			break;
		
		default:
			$("#manage-releases-hosting-type-wp-direct").show();
			$("#manage-releases-hosting-type-redirect-url-open").hide();
			break;
	}
}