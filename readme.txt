=== HeyPublisher Submisson Manager ===
Contributors: loudlever
Donate link: https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=6XSRBYF4B3RH6
Tags: accept submissions, anonymous, contributor, custom post interface, guest blog posts, loudlever, online applications, slushpile, submission forms, submission manager, submission, unregistered user
Requires at least: 2.8.6
Tested up to: 4.2.1
Stable Tag: 1.5.0
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

This plugin allows you as a publisher or blog owner to accept unsolicited submissions from writers without having to create an account for them.

== Description ==

This plugin allows you as to accept unsolicited submissions from writers.  You define categories and other filters to ensure you only receive the submissions that meet your publication's needs.

**How HeyPublisher Submission Manager Helps**

Normally if you wanted to allow readers to submit their writing to your publication you would need to create an account in WordPress for each writer, then educate them on how to write and edit Posts within the publishing tool.

With the HeyPublisher Submission Manager plugin submissions to your publication are instead uploaded to the  [HeyPublisher.com](http://www.heypublisher.com) web-service.  You still review the submissions within WordPress.  But you no longer have to manage user accounts or worry about unwanted posts filling up your system.

Mark submissions for review, accept submissions for publication, or reject submissions to remove them from your slush pile.  All acceptance/rejection communications with the writer are handled automatically by HeyPublisher, freeing you from the time-consuming task of having to respond to each submission individually.

You define the type of writing you want to receive.  You control the flow of unsolicited content coming into your publication.  You have more time to run your publication.  

== About Us ==

HeyPublisher is developed by the fine folks at [Loudlever, Inc.](http://loudlever.com).

== Installation ==

**Install Plugin**

* Download the zip file and save it in your `WordPress/wp-content/plugins` directory.

* Extract the files to a subdirectory called `heypublisher-submission-manager`.

* Active the plugin via the WordPress plugin menu.  

Once activated you will need to 'validate' the plugin with the HeyPublisher web-service.  To do this click on the HeyPublisher link in the newly created menu and follow the on-screen instructions.

**Validate Plugin**

To validate the plugin you will need to provide basic information about your publication and yourself as the administrator of this publication:

* Your publication name
* Your publication url
* Your email address
* A password

If your publication already exists in the [HeyPublisher.com](http://heypublisher.com/publishers/search) database, enter the name _exactly as it appears_ in our database.  If it doesn't already exist, simply enter the name as you want it to appear within our database.

If you already have an account with HeyPublisher.com, enter the email address and password you use to login.  If you do not already have an account, enter the email address and password you would like to use.  This username and password will be used if you ever need to reinstall or upgrade the plugin.  This information is also used to ensure that **_ONLY YOU_** can modify your publication's listing in the HeyPublisher database.

**Configure Plugin**

After the plugin has been validated, you can configure it to meet your submission requirements.  All information entered on this screen is used by HeyPublisher to help filter the submissions you receive.  The configuration sections are:

* **Publication Information:** this includes the name, URL, editor, and physical mailing address of your publication. (Screenshot 2)

* **Submission Form:** select or create the page that will contain your submission form on your website. (Screenshot 3)

* **Submission Guidelines:** select the page that contains your submission guidelines (if applicable). (Screenshot 4)

* **Submission Criteria:** select which genres of work you will accept - and how those should map to your internal categories when you "accept" a work for publication.  Additionally, you can select whether or not to accept simultaneous submissions and multiple submissions. (Screenshot 5)

* **Notification Options :** indicate the submission states where you want to send notifications to the writer.  This works in conjunction with the Response Templates feature, where you can customize the emails sent to writers.

* **Payment Options :** indicate whether or not your publication pays writers for their work.

* **Miscellaneous :** configuration to help you clean up bad HTML formatting.

Once you have made the appropriate configurations, click the "Save" button.  

== Frequently Asked Questions ==

If you have any questions not addressed here, [please email us](mailto:support@heypublisher.com?subject=Question+about+plugin).

* **What happens when I save a submission for later review?**  
If you do not allow simultaneous submissions, this puts a 'lock' on the work preventing the writer from submitting it to another press while you are considering whether or not to publish it.  The writer, however, may choose to withdraw their submission if it stays in this state for too long.

* **What happens when I accept a submission?**  
When you accept a submission, a copy of that submission is inserted as a Post into your WordPress system.  The post is marked as 'pending' so you can easily find the accepted submission and make any necessary edits to it prior to publication.

* **What happens when I reject a submission?**  
When you reject a submission it is immediately removed from your slush pile and the writer is notified of your decision automatically.  If the work had previously been 'accepted' by you, then rejecting it would also remove it from your pending posts.

* **Can I reject a published submission?**  
No - once you publish an accepted submission, the author is automatically notified that their work has been published by you and the work is removed from your "Submissions" administration  screen (Screenshot 7) 

* **We don't have submission guidelines.  Should we create them?**   
Yes - absolutely.  HeyPublisher indexes and archives all publisher submission guidelines, making them immediately searchable by writers around the world.  It's important as a publisher to be very clear with writers beforehand about what you are looking for in terms of genre, length, quality and content.

* **Can we change the content of the emails sent to writers regarding their submissions?**   
Yes - you can define custom response templates that contain whatever message you want to send to your writers.  These emails are sent automatically whenever you reject, accept, or save a submission for later review.  An email is also sent the first time an editor reads a new submission.  Click on 'Response Templates' in the side-bar and follow the on-screen instructions.

== Screenshots ==

1. Plugin Validation Screen.  This is how you connect the plugin to HeyPublisher.com.  Just input the username and password you want to create (if you don't already have a HeyPublisher account).
2. Publication Information configuration screen.  This is how you want your publication to appear within HeyPublisher's database.
3. Publication Contact information screen. Indicate how writers should contact you if they have a question about their submission.
4. Submission Form Configuration.  Here is where you create (or select) the page in your WordPress blog that will contain the submission form.  If the page doesn't already exist, click the link and the plugin will create it for you.
5. Submission Guidelines Configuration.  If your publication has a page for it's submission guidelines, select that page here.  This page will be indexed by HeyPublisher and will be promoted to our community of writers.
6. Submission Categories. Here is where you will select the types of work you want to accept, and how thesemap to the categories you've created in WordPress.
7. Payment Configuration.  If your publication pays writers for their work, indicate that here.  At this time, this is informational only.
8. Notification Configuration.  Indicate which notifications you want to send to the writer.
9. Submissions Summary.  From this screen you can view and manage the submissions received by your publication, including rejecting the submission, saving the submission for later review, or accepting the submission for publication.  Clicking on the Author's Name will bring up their bio in preview mode (if available).  Clicking on the title of the submission will allow you to 'preview' the submission.
10. Submission Status.  When 'previewing' a submission, the submission status side-bar will give you quick stats on the submission, including how many days it's been in your slushpile, and whether or not the work has been submitted to (or published by) any other publishers.
11. Email Template Summary.  Get a quick view of the custom emails you are sending to writers at each stage of the submission process.
12. Email Template Add/Edit Screen.  Add new (or edit existing) email templates. 
13. Plugin and Publisher Statistics screen.  Gives you a quick snapshot of total submissions, pending submissions, and how many writers have made comments about your publication on HeyPublisher.com
14. Dashboard Summary.  A summary of total submissions received and how many are pending review has been added to the Dashboard, providing you with a quick snapshot.
15. You can re-import a submission that has already been accepted.  Just select 'Reimport into WordPress' and click the submit button.

== Changelog ==

= 1.5.0 =

* You now have the ability to request a revision for an already accepted submission.  Since this is the place and time where you would typically want to request a revision, we thought we should let you do that.  
* You now have the ability to re-import previously accepted submissions - which is really important if you've already accepted a submission that has now been revised.  
* We fixed a bug where a "submission not found" error would display if the user deleted their submission.  While technically this makes sense, it doesn't do you any good.  What are you going to do with this knowledge?  Now we just remove the submission from the list.
* We did some general code cleanup and removed some assets that were no longer needed.  You probably didn't even know these assets were there to begin with - so we consider this a win-win.

= 1.4.5 =

* Fixed redirect error by pointing directly to www.heypublisher.com

= 1.4.4 =

* Verified works on WP 4.2.1.
* Fixed links to external documentation that were not actually there.
* Uninstall was not properly registering or cleaning up data.

= 1.4.3 =

* Verified works on WP 4.0+.

= 1.4.2 =

* You can no longer 'accept for publication' more than one submission at a time.

* When accepting a submission for publication, if the author record does not exist in your WP database you are now prompted to input the desired 'username' for the newly created author record.  If the author record already exists, then this step is skipped.

* Fixed improperly closed DIV tag on the submission summary that was causing the WordPress footer to appear mid-list.

= 1.4.1 =

* Completely rewrote the HTML cleanup code. All fonts and embedded styles are now removed from submitted works.  If you find that you're having problems with multibyte characters, turn off HTML cleanup in the Plugin Options -> Miscellaneous screen.

* Completely rewrote configuration screen to be tabbed and to better group/organize the configurations.

* Changed contact email address to support@heypublisher.com.

* Added support for sub-categories.

* Cleaned up the plugin submission pages to better handle extra long titles and to indicate when writers do not provide a return email address.

* Added Twitter as a social media login type we support.  We've removed support for Windows Live ID

= 1.3.1 =

* Fixed error that is thrown when trying to deactivate the plugin.

= 1.3.0 =

* Introduced the ability for editors to turn off the email notifications sent to writers when a submission changes state.

* Added 'Year Established', 'Monthly Circulation', 'Facebook' and 'Twitter' configuration options.  When populated, these values will be displayed with your publication listing at HeyPublisher.com

* Fixed an edge-case where publishers could continue to see withdrawn submissions.

* Better layout of the Publication Information configuration section, to separate contact info from publication info.

* Fixed issue where previously accepted submissions that were moved to the trash were not marked as 'rejected'.

* Fixed issue where accepted submissions that were scheduled for publication at a future date were not transitioning to 'published' on that date. 

= 1.2.4 =

* Fixed tagging issue that caused submissions page to throw fatal error regarding missing class file.

= 1.2.3 =

* Fixed wrapping issue seen in Dashboard widget when at small scale and in some circumstances always in XP (thanks Shrikant!).

= 1.2.2 =

* Fixes two typos, including an invalid URL.

= 1.2.1 =

* Added Submission Summary counts to the Dashboard and to the main plugin information screen.  Introduces metrics on accept vs. reject percentage.

= 1.2.0 =

* Added Custom Response Templates; the ability to customize the emails that are sent to your writers during each stage of the submission review process.

* Added summary submission statistics to the plugin 'home' page

* Added detailed statistics on the submission preview page, informing publisher if work is outstanding with another publisher (or has been previously published).

= 1.1.0 =

* Fixed an issue where the custom javascript in this plugin could possibly conflict with Admin Themes that also used javascript.

* Fixed code related to User<->Post mapping so that the plugin uses the new functions introduced in WordPress 3.0

* Removed the inclusion of the custom stylesheet on the Submission Form page, as it was conflicting with some themes.  Now, the width/height of the IFRAME in which the submission form displays is styled inline.  If you want to override the size or style of this IFRAME reset the attributes for `#heypub_submission_iframe` in your stylesheet. 

= 1.0.1 =

* Initial release of HeyPublisher Submission Manager Plugin

== Upgrade Notice ==

= 1.5.0 =

* You now have the ability to request a revision of an already accepted submission - which you will probably want at some time.  
* You now have the ability to reimport previously accepted submissions.  This is cool.  Upgrade just for this functionality.

= 1.4.5 = 

* Fixes problem where errors are being thrown because heypublisher.com domain can't be found.

= 1.4.2 = 

* Released : 2011-09-27

* Adds user creation feature request.

= 1.4.1 = 

* Released : 2011-06-25

* Fixes minor typos.

= 1.4.0 = 

* Released : 2011-06-24

* Primarily fixes UI components and HTML conversion formatting issues.  Upgrade strongly suggested.

= 1.3.2 =

* Released : 2011-03-02

* Fixes error when publisher name has special characters in it.

= 1.3.1 =

* Released : 2011-01-04

* Fixes error thrown when deactivating plugin - please upgrade.

= 1.3.0 =

* Released : 2011-01-03

* Fixes issues with state transitions when submissions are moved to trash, or scheduled for future publication.  See Changelog for details.

= 1.2.4 =

* Released : 2010-11-01

* Fixes Tagging issue. Please ugrade

= 1.2.3 =

* Released : 2010-10-30

* Fixes Dashboard wrapping issue.  Please upgrade.

= 1.2.2 =

* Released : 2010-10-27

= 1.2.1 =

* See Changelog for added functionality.  No bug fixes.

* Released : 2010-10-27

= 1.2.0 =

* See Changelog for added functionality.  No bug fixes.

* Released : 2010-10-23

= 1.1.0 =

* Upgrade to fix possible conflicts with javascript you may experience if your Admin Theme also uses javascript.

* Released : 2010-07-22

= 1.0.1 =

* Initial Release : 2010-04-29


