=== Plugin Name ===
Contributors: misanthrop
Donate link: http://wordpress.org/extend/plugins/wp-download-codes/
Tags: download, download code, code generator
Requires at least: 2.5.3
Tested up to: 5.0.3
Stable tag: 2.5.3.1

The plugin enables to generation and management of download codes for all types of files (zip, mp3, ...).

== Description ==

The plugin enables to generation and management of download codes for different types of files (zip, mp3, ...).
It was mainly written to enable the download of digital-only releases or of soundfiles complementary to Vinyl records or CDs using dedicated download codes. Such codes could be printed on the release covers or on separate download cards or simply distributed via email.
Of course there can be other use cases because the file download is not restricted to soundfiles.

With the plugin you can:

*   Create and manage **releases**, which can be standalone files or items bundled as zips (e.g. digital versions of vinyl albums).
*   Specify the allowed number of downloads for each release.
*   Create alphanumeric **download codes** for each release/file using a prefix for each code. The number of characters can be specified for each code.
*   Review downloads codes and set them to "final" when you want to use and distribute them.
*   Export final download codes in a plain list from which they can be used for example to print the codes on download cards using the mail merge functionality of your favorite office suite.
*   Analyze the use of the download codes.

== Installation ==

1. Upload the `wp-download-codes` folder to the `/wp-content/plugins/` directory.
1. Activate the plugin through the 'Plugins' menu in WordPress.
1. Create a folder within the `wp-content` directory and upload one or several zip files via FTP.
1. Optionally protect the folder via CHMOD in order to avoid unauthorized downloads using direct file links.
1. Go to the 'Settings' page and set the zip folder specified above. Alternatively, you can define an absolute path to the download folder.
1. Create a new release and assign a valid file to it. (The file extension like 'zip' or 'mp3' must be listed in the allowed file extensions under 'Settings'.)
1. Create new or import existing download codes for the release via 'Manage Codes' and make them final.
1. Put `[download-code id="xyz"]` in a page or post, where "xyz" is the ID of the respective release. Alternatively, you can write `[download-code]` without an ID to allow any download code. In the latter case, the download code should be assigned directly to a release.
1. Optionally provide your users with a direct link to the download code form using the "yourcode" query parameter (e.g. http://yourwordpressblog.com/download/?yourcode=DOWNLOADCODEXYZ).

== Frequently Asked Questions ==

= How can I influence the characters used to create the random codes from? =

Go to 'Settings' and modify the list of allowed characters.

= How can I reset one or several download codes which have already been used? =

Go to 'Manage Codes', select the release, click 'View report', select one or several downloads and click 'Clear Selected Downloads' at the bottom.

= Can I have download forms for several releases? =

No, currently each download code form must have assigned the ID of a specific release.

= Why do I have to upload the zip files via FTP?  =

Most providers do not allow an upload quota which is sufficient to upload larger zip files. Therefore, an option using an upload form has not been considered yet.

= Can I influence the request headers which are being sent for each download file? =

Yes, you can override the content type header which by default sends the MIME content type of the download file. If this does not work in your environment, you can specify alternative fixed headers like application/download.

= Can I use the x-sendfile header functionality provided by my Apache server? =

Yes, if you have an Apache server running with mod_xsendfile (https://tn123.org/mod_xsendfile/) being installed and configured properly, you can turn on the respective setting in the Download Codes settings. The download then uses the plain x-sendfile header instead of streaming it with the general logic.

== Screenshots ==


== Changelog ==

= 2.5.3.1 =
* Made minor improvements for empty "Hosting Type" values

= 2.5.3 =
* Added "Hosting Type" field to releases and added hosting type to store the download file in an external location

= 2.5.1 =
* Fixed bug in release management (no files are shown when PHP version >7 is used)

= 2.5.0 =
* Fixed bug on 'Manage Release' page where after 2.4.0 the same report was shown for all releases.
* Removed ob_end_flush() before streaming download as this has caused issues for users. If with this measure new issues would pop-up this needs to be made configurable.
* Included optimizations from Max Brokman (initialization of $num_download_files, $id and $bar, inclusion of $wpdb->prepare() for security reasons).
* Added ID of release to 'Manage Codes' page so that it is easier to determine which ID a release has.
* Added shortcode example to 'Manage Codes' page in order to simplify the insertion of shortcodes into pages.
* Fixed behavior with multiple shortcodes appearing on the same page so that only results and links are shown which are related to the release for which the code was entered.
* Added experimental functionality to define an anchor with a shortcode (e.g. [download-code id="3" anchor="myanchor"]) in order to allow people to automatically scroll to the download link after the code was entered.

= 2.4.0 =
* Consolidated and incorporated several additional best practices to handle file downloads in order to solve current issues with certain client-server constellations.
* Added ID of release to 'Manage Release' list so that it can be used more easily with the `[download-code id="xyz"]` shortcode.
* Enabled support for Apache header x-sendfile (if configured in the settings).
* Fixed bug with file extensions for release files which did not match a length of 3 (e.g. '.epub').

= 2.3.0 =
* Increased maximum number of download codes which can be created for one group from 9999 to 99999.
* Introduced new feature to import existing codes for a release (in case users want to migrate their codes or create codes outside the plugin).
* Modified the default constant for the allowed characters in order to avoid misleading ambiguities between 'O' (the character) and '0' (the number). 
* Improved the resetting of codes.
* Improved deletion of releases.

= 2.2.0 =
* Refined download headers to hopefully fix remaining client-server issues with the plugin.
* Introduced admin setting DC_HEADER_CONTENT_TYPE to override the default MIME content type header with others.

= 2.1.3 =
* Improved query for a quicker display of releases in "Manage Releases"

= 2.1.2 =
* As suggested by allolex, an issue with truncated downloads was tried to be fixed by completely flushing the download stream.

= 2.1.1 =
* Included MYSQL patch from Sean Patrick Rhorer to enable display of releases even with a restrictive host setting for max join size

= 2.1 =
* Added feature for direct download code links through the query parameter "yourcode"

= 2.0 =
* Added an (optional) artist field so that releases can have a title (album name) and artist
* Introduced "code groups" in order to be able to created and delete batches of code groups with the same code prefixes
* Added a new field to the settings page so that users can customize what set of characters their download codes are composed from (this also avoid confusion between numbers and letter like between 0 and O as well as between l and 1)
* Changed the download link that users click on to display the release's title and the file size (rather than using the filename as the link text)
* Updated HTML markup for the forms and tables so that it matches WordPress conventions (label tags, descriptions, etc)
* Moved a lot of the common DB calls into individual functions in dc_functions.php
* Added a JS confirm alert if you try to delete a release or a batch of download codes to avoid accidental deletions
* Added a similar JS confirm before the user finalizes a batch of codes (since it's irreversible)
* Added "lightbox" style popups to display the list of download codes or the download report. It also works on the off-chance that JS isn't enabled.

= 1.3 =
* Changed download mechanism in order to get fix the header issues appearing with many firefox versions

= 1.2.2 =
* Changed menu order in adminstration
* Fixed the determination of the upload path which was sometimes not working for blank installations
* Tried to fix the download file streaming as there are still problems with some browser and OS combinations

= 1.2.1 =
* Fixed HTML rendering of "Settings" form in plugin administration

= 1.2 =
* Added possibility to specify the absolute path for the download file location in 'Settings'. This should help if in your wordpress installation the upload folder cannot be determined.

= 1.1 = 
* Added functionality to edit list of allowed file types.
* Added annotation to documentation about folder protection to avoid unauthorized downloading.
* Applied minor bug fixes.

= 1.0.9 =
* Fixed download problem on WP sites with blog URL differing from WP URL

= 1.0.8 =
* Enabled reset of specifc or all download codes.
* Enabled modification of download form messages in the `Settings` dialog.
* Enabled the [download-code] shortcode without having to provide the release ID.
* Fixed problems ocurring from side effects with other plugins during the sending of filestream headers.
* Fixed problem with insufficient memory occuring in some PHP environments.

= 1.0.7 =
* Fixed different behavior of upload path determination (absolute, relative).

= 1.0.6 =
* Fixed side effects to media library.

= 1.0.5 =
* Added header for information about the length of the downloaded file.
* Fixed deletion of session.

= 1.0.4 =
* Fixed "Make final" functionality for WP 2.7.
* Introduced differentiation between absolute and relative upload paths.

= 1.0.3 =
* Added "mp3" to the allowed file types.
* Reworked constraints for fields on 'Manage Releases'. 

= 1.0.2 =
* Bug fix: (Existing) zip folders below the upload directory can now be selected via drop-down.
* Bug fix: On the 'Manage Codes' page, the non-existence of releases was handled (link to 'Add new release' sub page was displayed').

= 1.0.1 =
* Improved editing and addition of releases.
* Corrected setting of options during initialization.

= 1.0.0 =
* Initial version.

== Arbitrary section ==
