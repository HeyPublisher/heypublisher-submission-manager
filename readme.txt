=== HeyPublisher Submisson Manager ===
Contributors: heypublisher, aguywithanidea, loudlever
Donate link: https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=6XSRBYF4B3RH6
Tags: accept submissions, anonymous, contributor, custom post interface, guest blog posts, online applications, slushpile, submission form, submission manager, submission, unregistered user, heypublisher
Requires at least: 5.0
Tested up to: 5.8.0
Stable Tag: 3.3.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

HeyPublisher gives you a better way of managing submissions directly within WordPress.

== Description ==

This plugin allows you as to accept submissions from writers.  You define categories and other filters to ensure you only receive the submissions that meet your publication's needs.  And there is no need to create user accounts for writers just so they can submit content to your publication.

More information about this plugin can be found at: https://github.com/HeyPublisher/heypublisher-submission-manager

== Changelog ==

= 3.3.0 =
+ Released: 2021-09-01
+ #35 : Normalize all calls to make a URL into one function, including:
  + Removed `form_action` function and replaced all calls to it to use the the newer `nonced_url` function as it's more flexible.
+ #63 : Update Submission State diagram.
+ #64 : Remove unnecessary code.
+ #97 : Include category on submission detail screen.
+ #121 : Error page when trying to access submission with unusual file extension.
+ #122 : Add option to suppress Author Info when reviewing submissions.
+ #124 : Validate against WordPress Version 5.8.
+ #125 : Submission List and Details should use JSON API endpoint, including:
  + Removed all calls to old XML endpoint `api/v1/publisher/fetch_categories` as it's been replaced by JSON endpoint `api/publishers/:id/genres`
  + Removed all calls to old XML endpoint `api/v1/submissions/fetch_pending_submissions` as it's been replaced by JSON endpoint `api/publishers/:id/submissions`
+ Removed old `Loudlever` class and all references to it.  Relevant code was replaced by `Base` class a while ago.
+ Plugin build date display on statistics page was using the date of upgrade, not date of plugin build, so fixed this.
+ **NOTE:** Server support for plugin versions older than 2.8.0 has been removed.  Users of versions older than 2.8.0 will receive an upgrade notice and functionality will be limited.

= 3.2.1 =
+ Released: 2021-06-26
+ #118 jQuery version getting loaded with WordPress no longer returns value of current state of checkbox, instead returning initial state of checkbox.  Fixed to use `$(src).prop('checked')` instead.

= 3.2.0 =
* Released: 2021-05-31
+ #113 : Plugin version is sent to the server with all requests, so appropriate data for that version can be returned.
+ #114 : Fixing issue where file upload errors so writer knows how to fix the problem.
+ #116 : Validated functionality through WordPress Version 5.7.2.

* Fixing issue where file upload errors so writer knows how to fix the problem.
* Validated functionality through WordPress Version 5.7.2.

= 3.1.2 =
* Released: 2021-02-06
* Fixed issue #11 that was showing on new install first time options page is displayed.
* Updated the options page to make use of the new 'accepting_subs' attribute in the JSON endpoint.  This is now being used to toggle the "Accepting Submissions" dropdown.

= 3.1.1 =
* Released: 2020-09-25
* Fix bug #110 that prevented turning off the submissions form

* Released: 2020-08-30
* Fixed having the wrong year in the 2020 release notes.

= 3.1.0 =
* Released: 2020-08-30
* Fixed having the wrong year in the 2020 release notes.
* Plugin is now based on the HeyPublisher/Base class that other plugins are based upon.
* Updated all code to ensure there are no conflicts with older versions
* Validated functionality through WordPress Version 5.5.

= 3.0.1 =
* Released: 2020-06-27
* Update to updater to fix issue related to updating from GitHub when plugin was manually updated to 3.0.0.

= 3.0.0 =
* Released: 2020-06-25
* Reworked plugin to leverage new HeyPublisher JSON API.
* Publishers can now set custom submission guidelines to be displayed on HeyPublisher.com.  Before, HeyPublisher would pull these guidelines directly from the publisher website, but that sometimes resulted in marketing text getting included when it should not have been.
* Better error messaging when HeyPublisher.com is undergoing maintenance.
* Implemented caching of publication types and genres to speed up loading time
* Fixed references to non-secure URLs that were throwing errors in the console log, but did not affect performance.
* Introduced a new Config class that handles all configuration parameter settings throughout the code base.
* Plugin is now hosted on GitHub at https://github.com/HeyPublisher.

= 1.0.1 =
* Initial Release : 2010-04-29
