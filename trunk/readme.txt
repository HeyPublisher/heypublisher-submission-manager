=== Plugin Name ===
Contributors: Loudlever. 
Donate link: https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=6XSRBYF4B3RH6
Tags: anonymous, post, submit, submission, unregistered users, custom post interface, plugin, slushpile, HeyPublisher, Loudlever
Requires at least: 2.8.6
Tested up to: 3.0.1
Stable Tag: 1.2.4

This plugin allows you as a publisher or blog owner to accept unsolicited submissions from writers without having to create an account for them.

== Description ==

**About HeyPublisher Submission Manager**

This plugin allows publishers and blog owners to accept unsolicited submissions from unregistered users.  You no longer have to create accounts for each and every writer in your system who wants to submit content to you for consideration.  Instead, submissions to your website are handled by the [HeyPublisher.com](http://www.heypublisher.com) web-service via this plugin.  

You define the type of writing you want to receive.  You control the flow of unsolicited content coming into your publication.  You have more time to run your publication.  

Think of us as a "digital [slush pile](http://en.wikipedia.org/wiki/Slush_pile)" for your website.

Use of this plugin allows you to quickly and easily preview submissions, mark submissions for 'review', accept submissions for publication, or reject submissions, removing them from your slush pile.  All acceptance/rejection communications with the writer are handled automatically by the web-service, freeing you from the time-consuming task of having to respond to each submission individually.

HeyPublisher Submission Manager allows you to:

* Define specific genres of work you will accept and limit the submissions to only works within those genres.

* Enforce your publication's simultaneous submission rules.  If you don't accept simultaneous submissions, the plugin will prevent writers from submitting work to you and another press simultaneously.

* Prevent writers from spamming you with submissions (unless you accept multiple submissions)

* Publish your submission guidelines in [HeyPublisher's Publisher Database](http://heypublisher.com/publishers/search), where they can be discovered by thousands of creative writers world-wide.

**How it Works**

This plugin allows you to keep your database clear of unsolicited submissions and unnecessary user accounts.

All submissions are stored remotely on our servers so we can filter out the submissions you don't want to see.  The only time a submission or user account is ever copied over to your system is when you "accept" a submission for publication.

Spam submissions are a virtual impossibility.  Since all writers must have valid accounts with HeyPublisher before they can submit work to your publication, we prevent spam from getting in.  And since you as a publisher can toggle on or off the ability to accept simultaneous submissions and multiple submissions, you can very easily control the flow of new content coming to your publication.

== About Us ==

Developed by the fine folks at [Loudlever, Inc.](http://www.loudlever.com), HeyPublisher is the premier online tool for writers to discover publishers and new writing markets.  

At Loudlever, Inc. we believe writers and publishers should be well-matched — compatible in a long-term relationship.  This is why we're applying the same computer algorithms used by online dating sites to our tools to ensure writers and publishers are perfectly paired.

When writers and publishers are perfectly matched, magic can happen.  Writers are more creative.  Publishers are able to get issues out faster.

We are Loudlever.  Creativity.  Accelerated.

== Installation ==

**Install Plugin**

* Download the zip file and save it in your `wordpress/wp-content/plugins` directory.

* Extract the files to a subdirectory called `heypublisher-submission-manager`.

* Active the plugin via the usual Wordpress plugin menu.  

Once activated you will need to 'validate' the plugin with the HeyPublisher webservice.  To do this, click on the HeyPublisher link in the newly created side-nav box and follow the on-screen instructions.

**Validate Plugin**

To validate the plugin you will need to provide some basic information about your publication and yourself as the administrator of this publication:

* Your publication name
* Your publication url

If your publication already exists in the [HeyPublisher.com](http://heypublisher.com/publishers/search) database, enter the name _exactly as it appears_ in our database.  If it doesn't already exist, simply enter the name as you want it to appear within our database.

* Your email address
* A password

If you already have an account with HeyPublisher.com, enter the email address and password you use to login.  If you do not already have an account, enter the email address and password you would like to use.  This username and password will be used if you ever need to reinstall or upgrade the plugin.  This information is also used to ensure that **_ONLY YOU_** can modify your publication's listing in the HeyPublisher database.

**Configure Plugin**

After the plugin has been validated, you can configure it to meet your submission requirements.  All information entered on this screen is used by HeyPublisher to help filter the submissions you receive.  The configuration sections are:

* **Publication Information:** this includes the name, URL, editor, and physical mailing address of your publication. (Screenshot 2)

* **Submission Form:** select or create the page that will contain your submission form on your website. (Screenshot 3)

* **Submission Guidelines:** select the page that contains your submission guidelines (if applicable). (Screenshot 4)

* **Submission Criteria:** select which genres of work you will accept - and how those should map to your internal categories when you "accept" a work for publication.  Additionally, you can select whether or not to accept simultaneous submissions and multiple submissions. (Screenshot 5)

* **Payment Options :** indicate whether or not your publication pays writers for their work.

* **Miscellaneous :** configuration to help you clean up bad HTML formatting.

Once you have made the appropriate configurations, click the "Save" button.  

== Frequently Asked Questions ==

If you have any questions not addressed here, [please email us](mailto:wordpress@loudlever.com?subject=Question+about+plugin).

* **What happens when I accept a submission?**  
When you accept a submission, a copy of that submission is inserted as a Post into your Wordpress system.  The post is marked as 'pending' so you can easily find the accepted submission and make any necessary edits to it prior to publication.

* **What happens when I reject a submission?**  
When you reject a submission it is immediately removed from your slush pile and the writer is notified of your decision automatically.  If the work had previously been 'accepted' by you, then rejecting it would also remove it from your pending posts.

* **Can I reject a published submission?**  
No - once you publish an accepted submission, the author is automatically notified that their work has been published by you and the work is removed from your "Submissions" administration  screen (Screenshot 7) 

* **What happens when I save a submission for later review?**  
If you do not allow simultaneous submissions, this puts a 'lock' on the work preventing the writer from submitting it to another press while you are considering whether or not to publish it.  The writer, however, may choose to withdraw their submission if it stays in this state for too long.

* **We don't have submission guidelines.  Should we create them?**   
Yes - absolutely.  HeyPublisher indexes and archives all publisher submission guidelines, making them immediately searchable by writers around the world.  It's important as a publisher to be very clear with writers beforehand about what you are looking for in terms of genre, length, quality and content.

* **Can we style the submission form to better align with our look and feel?**   
Yes you can.  Your theme's stylesheet is used by the plugin when rendering the submission form.  If you want to change the look of an element on the submission form, simply declare a style for that element in your theme's stylesheet and it will take effect immediately.  Read the [online style guide](http://www.loudlever.com/docs/plugins/wordpress/style_guide) for more detailed information.

* **Can we change the content of the emails sent to writers regarding their submissions?**   
Yes, as of version 1.2.0 you can define custom response templates that contain whatever message you want to send to your writers.  These emails are automatically whenever you reject, accept, or save a submission for later review.  An email is also sent the first time an editor reads a new submission.  Click on 'Response Templates' in the side-bar and follow the on-screen instructions.

== Screenshots ==

1. Plugin Validation Screen.  This is how you connect the plugin to HeyPublisher.com.  Just input the username and password you want to create (if you don't already have a HeyPublisher account).
2. Publication Information configuration screen.  This is how you want your publication to appear within HeyPublisher's database.
3. Submission Form Configuration.  Here is where you create (or select) the page in your Wordpress blog that will contain the submission form.  If the page doesn't already exist, click the link and the plugin will create it for you.
4. Submission Guidelines Configuration.  If your publication has a page for it's submission guidelines, select that page here.  This page will be indexed by HeyPublisher and will be promoted to our community of writers.
5. Submission Criteria Configuration. Here is where you will select the specific HeyPublisher genres of work you want to accept, and how those genres map to the categories you've created in Wordpress.
6. Payment Configuration.  If your publication pays writers for their work, indicate that here.  At this time, this is informational only.
7. Submissions Summary.  From this screen you can view and manage the submissions received by your publication, including rejecting the submission, saving the submission for later review, or accepting the submission for publication.  Clicking on the Author's Name will bring up their bio in preview mode (if available).  Clicking on the title of the submission will allow you to 'preview' the submission.
8. Miscellaneous Configuration.  Should be self-explanatory.
9. Submission Status.  When 'previewing' a submission, the submission status side-bar will give you quick stats on the submission, including how many days it's been in your slushpile, and whether or not the work has been submitted to (or published by) any other publishers.
10. Response Template Summary.  Get a quick view of the custom responses you are sending to writers at each stage of the submission process.
11. Response Template Add/Edit Screen.  Add new (or edit existing) response templates. 
12. Plugin and Publisher Statistics screen.  Gives you a quick snapshot of total submissions, pending submissions, and how many writers have made comments about your publication on HeyPublisher.com
13. Dashboard Summary.  A summary of total submissions received and how many are pending review has been added to the Dashboard, providing you with a quick snapshot.

== Changelog ==

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

* Fixed code related to User<->Post mapping so that the plugin uses the new functions introduced in Wordpress 3.0

* Removed the inclusion of the custom stylesheet on the Submission Form page, as it was conflicting with some themes.  Now, the width/height of the IFRAME in which the submission form displays is styled inline.  If you want to override the size or style of this IFRAME reset the attributes for `#heypub_submission_iframe` in your stylesheet. 

= 1.0.1 =

* Initial release of HeyPublisher Submission Manager Plugin

== Upgrade Notice ==

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


