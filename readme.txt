=== HeyPublisher Submisson Manager ===
Contributors: heypublisher, aguywithanidea, loudlever
Donate link: https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=6XSRBYF4B3RH6
Tags: accept submissions, anonymous, contributor, custom post interface, guest blog posts, online applications, slushpile, submission form, submission manager, submission, unregistered user, heypublisher
Requires at least: 4.0
Tested up to: 5.5
Stable Tag: 3.1.1
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

HeyPublisher gives you a better way of managing submissions directly within WordPress.

== Description ==

This plugin allows you as to accept submissions from writers.  You define categories and other filters to ensure you only receive the submissions that meet your publication's needs.  And there is no need to create user accounts for writers just so they can submit content to your publication.

More information about this plugin can be found at: https://github.com/HeyPublisher/heypublisher-submission-manager

== Changelog ==

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
