## Changelog

### 2.8.0
+ Released: 2018-02-25
+ [[#89](https://github.com/aguywithanidea/heypublisher-submission-manager/issues/89)] : UI for email response template management is now wholly within WordPress, so those funky styling issues are no longer an issue.
+ [[#90](https://github.com/aguywithanidea/heypublisher-submission-manager/issues/90)] : Added link to the HeyPublisher status page so if you get an error you'll know what's going on.
+ Verified WordPress 4.9.4

### 2.7.0
+ Released: 2018-01-07
+ [[#1](https://github.com/aguywithanidea/heypublisher-submission-manager/issues/1)] : Editors can now vote on submissions (up/down).
+ [[#82](https://github.com/aguywithanidea/heypublisher-submission-manager/issues/82)] : Editors can now add notes to each submission they're reviewing.
+ [[#87](https://github.com/aguywithanidea/heypublisher-submission-manager/issues/87)] : Fixed crummy little error that caused the summary block to collapse on the Submission Detail screen if a bio was not provided.
+ Additionally ....
  + Fixed some styling issues on the Submissions page
  + Removed code that's no longer used
  + Migrated all stylesheet generation to sass
  + Fixed javascript error that was preventing editor notes input field from displaying.  You can now send notes to writers again!!
  + Fixed errors with javascript that were preventing summary and author bio expand buttons from working in some versions of Firefox.

### 2.6.3
+ Released: 2017-12-20
+ Fixed bug which prevented rendering in PHP 7.x

### 2.6.2
+ Released: 2017-12-19
+ Verified WordPress 4.9.1

### 2.6.1
+ Released: 2017-12-17
+ [[#78](https://github.com/aguywithanidea/heypublisher-submission-manager/issues/78)] : Snitch screen added to main plugin overview page, tracking editors/admins and submissions they've touched.
+ [[#72](https://github.com/aguywithanidea/heypublisher-submission-manager/issues/72)] : the edit post screen now shows the actions taken by an editor prior to acceptacnce so you don't lose track after publication.

### 2.6.0
+ Released: 2017-11-15
+ [[#75](https://github.com/aguywithanidea/heypublisher-submission-manager/issues/75)] : Knowing which editor read or accepted a submission is now crystal clear.  A submission 'history' section has been added to the bottom of the submission detail page.
* Started adoption of HeyPublisher v2 API, which is faster and JSON based (and gets rid of all this krunk XML).

### 2.5.0
+ Released: 2017-10-15
+ [[#73](https://github.com/aguywithanidea/heypublisher-submission-manager/issues/73)] : You can now manage the email responses for the 'submitted' state.  This is the email that writers receive whenever they submit their writing to your publication.


### 2.4.0
+ Released: 2017-07-09
+ [[#66](https://github.com/aguywithanidea/heypublisher-submission-manager/issues/66)] : Allows publisher to mark a submission as "withdrawn".  Previously, if writer emailed publisher to withdraw submission (instead of withdrawing via HeyPublisher.com), the publisher could only "reject" the submission.  This introduces as softer way of getting the submission out of the system.
+ When an "accepted" submission is moved to trash or deleted from the Posts screen, it will now be marked as "withdrawn" instead of "rejected".

### 2.3.0
+ Released: 2017-05-30
+ [[#53](https://github.com/aguywithanidea/heypublisher-submission-manager/issues/53)] : Released experimental feature that prompts writers to subscribe to your mailing list when submitting work.  This feature requires you already have a MailChimp account.  (You already have a MailChimp account, don't you??)
+ Changed various text on Options page to be less pedantic and more readable.


### 2.2.0
+ Released: 2017-04-02
+ [[#37](https://github.com/aguywithanidea/heypublisher-submission-manager/issues/37)] : Changed "Year Established" to a dropdown list.  For publications published in the year "g" we apologize.
+ [[#54](https://github.com/aguywithanidea/heypublisher-submission-manager/issues/54)] : Fixed weird formatting that was occurring on the Options page in WP 4.7.3.
+ [[#40](https://github.com/aguywithanidea/heypublisher-submission-manager/issues/40)] : Validated that plugin works in WP 4.7.3.
+ [[#41](https://github.com/aguywithanidea/heypublisher-submission-manager/issues/41)] : Updated account creation to use the "Username" of the author in HeyPublisher as the Username in WP, when possible.  Email addresses are no longer used as usernames.
+ [[#50](https://github.com/aguywithanidea/heypublisher-submission-manager/issues/50)] : Removed the Dashboard widget that was not being used by anyone, ever.

### 2.1.0
+ Released : 2017-03-23
+ You now can use [Username] and [UserID] keyword substitutions in email templates (thanks for the suggestion Andy!)
+ Even though Andy didn't ask for it, we also added the ability for publishers to include their [ISSN](http://www.issn.org/understanding-the-issn/the-issn-international-register/) in their HeyPublisher listing.

### 2.0.1
+ Released : 2017-02-20
+ Fixed style sheet not properly styling custom email template edit forms.  It was ugly - we know that now.

### 2.0.0
+ Finally got around to that redesign everyone has been begging us for :)
+ The Options page is now a single form, which should prevent those pesky javascript errors that prevented some folks from fully configuring the plugin because they couldn't bring up the other tabs.
+ The uninstall plugin link is now on the first screen, beneath the Plugin Statistics - not that you'd ever want to use this, but just in case.
+ The plugin was basically overhauled, from the ground up.  It renders faster.  The code is better organized.  Building upon this version will allow us to turn around new features faster.

### 1.5.1
+ Writers will now be prompted to provide their name, bio and website URL when submitting works to your publication.
+ Better yet - that information is fully imported into WordPress when you accept their submission for publication.

### 1.5.0
+ You now have the ability to request a revision of an already accepted submission - which you will probably want at some time.
+ You now have the ability to reimport previously accepted submissions.  This is cool.  Upgrade just for this functionality.

### 1.4.5
+ Fixes problem where errors are being thrown because heypublisher.com domain can't be found.

### 1.4.2
+ Released : 2011-09-27

+ Adds user creation feature request.

### 1.4.1
+ Released : 2011-06-25

+ Fixes minor typos.

### 1.4.0
+ Released : 2011-06-24

+ Primarily fixes UI components and HTML conversion formatting issues.  Upgrade strongly suggested.

### 1.3.2
+ Released : 2011-03-02

+ Fixes error when publisher name has special characters in it.

### 1.3.1
+ Released : 2011-01-04

+ Fixes error thrown when deactivating plugin - please upgrade.

### 1.3.0
+ Released : 2011-01-03

+ Fixes issues with state transitions when submissions are moved to trash, or scheduled for future publication.  See Changelog for details.

### 1.2.4
+ Released : 2010-11-01

+ Fixes Tagging issue. Please ugrade

### 1.2.3
+ Released : 2010-10-30

+ Fixes Dashboard wrapping issue.  Please upgrade.

### 1.2.2
+ Released : 2010-10-27

### 1.2.1
+ See Changelog for added functionality.  No bug fixes.

+ Released : 2010-10-27

### 1.2.0
+ See Changelog for added functionality.  No bug fixes.

+ Released : 2010-10-23

### 1.1.0
+ Upgrade to fix possible conflicts with javascript you may experience if your Admin Theme also uses javascript.

+ Released : 2010-07-22

### 1.0.1
+ Initial Release : 2010-04-29
