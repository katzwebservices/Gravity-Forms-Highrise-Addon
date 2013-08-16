# Gravity Forms Highrise Add-on #
**Tags:** gravity forms, forms, gravity, form, crm, gravity form, customer, contact, contacts, address, addresses, address book, highrise, highrise plugin, form, forms, gravity, gravity form, gravity forms, secure form, simplemodal contact form, wp contact form, widget, high-rise, 37signals, 37 signals, basecamp  
**Requires at least:** 2.8  
**Tested up to:** 3.6  
**Stable tag:** trunk  
**Contributors:** katzwebdesign, katzwebservices  
Donate link:https://www.paypal.com/cgi-bin/webscr?cmd=_donations&business=zackkatz%40gmail%2ecom&item_name=Gravity%20Forms%20Highrise&no_shipping=0&no_note=1&tax=0&currency_code=USD&lc=US&bn=PP%2dDonationsBF&charset=UTF%2d8

Integrate the remarkable Gravity Forms plugin with Highrise.

## Description ##

### Integrate Gravity Forms with Highrise

####No more exporting and importing from Gravity Forms into Highrise

Gravity Forms is the best form plugin for WordPress. <a href="http://highrisehq.com/" rel="nofollow">Highrise</a> is the best web-based CRM.

####Gravity Forms + Highrise = Happy Sales Team

This free Highrise Add-On for Gravity Forms adds contacts into Highrise automatically, making customer relationship management simple. The setup process takes less than three minutes, and your contact form will be linked with Highrise.

### This plugin supports great Highrise functionality:
* Multiple phone numbers (work, home, mobile, fax, other)
* Multiple email addresses (work, personal, other)
* Twitter
*** Highrise Custom Fields:** simply label the field the same name as the Custom Field in Highrise, and the information will be added!  

#### Other Gravity Forms Add-ons:

* <a href="http://wordpress.org/extend/plugins/gravity-forms-addons/">Gravity Forms Directory & Addons</a> - Turn Gravity Forms into a WordPress Directory plugin.
* <a href="http://wordpress.org/extend/plugins/gravity-forms-constant-contact/">Gravity Forms + Constant Contact</a> - If you use Constant Contact and Gravity Forms, this plugin is for you.

###Originally developed by <a href="http://www.glidedesign.com/">Glide Design</a>

If you have questions, comments, or issues with this plugin, <strong>please leave your feedback on the <a href="https://github.com/katzwebservices/Gravity-Forms-Highrise-Addon/issues">Plugin Support Forum</a></strong>. <a href="http://wordpress.org/plugins/gravity-forms-highrise-crm/">Or use a different plugin</a>.

## Screenshots ##

###1. The Gravity Forms Highrise Add-on settings page###
<img src="http://svn.wp-plugins.org/gravity-forms-highrise/trunk/screenshot-1.jpg" alt="The Gravity Forms Highrise Add-on settings page" />

**2. It's easy to integrate Gravity Forms with Highrise:** check a box in the "Advanced" tab of a form's Form Settings  
###2. A link to the person in Highrise is added to the entry info box###
<img alt="A link to the person in Highrise is added to the entry info box" src="http://svn.wp-plugins.org/gravity-forms-highrise/trunk/screenshot-2.jpg" />


## Installation ##

1. Upload plugin files to your plugins folder, or install using WordPress' built-in Add New Plugin installer
1. Activate the plugin
1. Go to the plugin settings page (under Forms > Highrise)
1. Enter the information requested by the plugin.
1. Click Save Settings.
1. If the settings are correct, it will say so.
1. Follow on-screen instructions for integrating with Highrise.

## Frequently Asked Questions ##

### Server requirements ###
Currently, the plugin requires `curl` to be enabled on your server. This will be improved in future versions.

### What's the license for this plugin? ###
This plugin is released under a GPL license.

## Changelog ##

### 2.6 ###
* Fixes for Gravity Forms 1.7 and WordPress 1.6
	- Accurately show forms with Highrise enabled
	- Updated installation instructions
* Notes are added to Entries to show whether the entry was added to Highrise successfully
* If an entry was added to Highrise, a link to the person in Highrise is added to the Entry info box

### 2.5.2 ###
* Added support for Gravity Forms 1.7
* Added proper error reporting for admins

### 2.5.1 ###
* Fixed issue with notes not being created for a new contact
* Fixed issue with tags not being created for a new contact
* Fixed <a href="http://wordpress.org/support/topic/plugin-gravity-forms-highrise-add-on-field-mapping-is-still-completely-wrong">a field mapping issue</a>. Thanks, @brentoe!
* Thanks, <a href="http://sip.us">Marc</a>!

### 2.4.5 ###
* Fixed bug where tags are not properly added. Thanks, <a href="http://www.justinparks.com/">Justin</a> and <a href="http://www.webdistortion.com/">Paul</a>!

### 2.4.4 ###
* Fixed issue with latest Gravity Forms preventing Highrise checkbox from showing up

### 2.4.3 ###
* Fixed issue where if there are no custom fields, the information sent to Highrise is malformed. (Fixed unopened `</subject_datas>` tag, if you want to know!)

### 2.4.2 ###
* Added an Highrise icon next to each form with Highrise integration enabled in the Gravity Forms Edit Forms view
* Forced `https://` API path to fix submission errors

### 2.4.1 ###
* Fixes empty array warnings from 2.4 changes

### 2.4 ###
* Reduced the number of requests to the API by a lot; should speed up the submission process
* Added Twitter field
* Added support for Phone field locations (Work, Home, Fax, Mobile, etc.)
* Added support for Email field locations (Work, Home, Other)
* Added support for Highrise Custom Fields. Simply label the field the same name as the Custom Field in Highrise, and the information will be added!
* Fixed issues with notes vs. staff comments (<a href="http://wordpress.org/support/topic/plugin-gravity-forms-highrise-add-on-job-title">as described here</a> - thanks Ted)

### 2.3 ###
*** Improved integration into forms:** now, simply edit Form Settings, click the Advanced tab, and check the box titled "Enable Highrise integration"!  
* Added error notice for logged-in administrators when form does not have required fields.
* Fixed & improved the upload functionality
	- Now works properly for sites in sub-folders
	- Upload links now are correct
	- Supports multiple uploads

### 2.2 ###

* Added to WordPress directory (originally found on <a href="http://www.glidedesign.com/finally-highrise-addon-gravity-forms/">Glide Design</a>
* Made it easier to enable forms to be linked with Highrise
	* Case-insensitive `Highrise` label
	* No longer need to add "yes" as the value in the Advanced tab. If you want, you still can, though :-)
* Fixed many PHP warnings when WordPress development mode is enabled
* Improved layout of settings page and configuration instructions

### 2.1 ###

* Upgraded ability to process multi-field forms and file uploads

### 2.0 ###

* Did more extensive testing with Highrise and added account validation

### 1.1 ###

* Added functionality for hidden form to choose to send info or not and to push tags

### 1.0 ###

* Push functionality for all forms to people and notes

## Upgrade Notice ##

### 2.6 ###
* Fixes for Gravity Forms 1.7 and WordPress 1.6
	- Accurately show forms with Highrise enabled
	- Updated installation instructions
* Notes are added to Entries to show whether the entry was added to Highrise successfully
* If an entry was added to Highrise, a link to the person in Highrise is added to the Entry info box


### 2.5.2 ###
* Added support for Gravity Forms 1.7

### 2.5.1 ###
* Fixed issue with notes not being created for a new contact
* Fixed issue with tags not being created for a new contact
* Thanks, <a href="http://sip.us">Marc</a>!

### 2.4.5 ###
* Fixed bug where tags are not properly added. Thanks, <a href="http://www.justinparks.com/">Justin</a> and <a href="http://www.webdistortion.com/">Paul</a>!

### 2.4.4 ###
* Fixed issue with latest Gravity Forms preventing Highrise checkbox from showing up

### 2.4.3 ###
* Fixed issue where if there are no custom fields, the information sent to Highrise is malformed. (Fixed unopened `</subject_datas>` tag, if you want to know!)

### 2.4.2 ###
* Added an Highrise icon next to each form with Highrise integration enabled in the Gravity Forms Edit Forms view
* Forced `https://` API path to fix submission errors

### 2.4.1 ###
* Fixes empty array warnings from 2.4 changes

### 2.4 ###
* Reduced the number of requests to the API by a lot; should speed up the submission process
* Added Twitter field
* Added support for Phone field locations (Work, Home, Fax, Mobile, etc.)
* Added support for Email field locations (Work, Home, Other)
* Added support for Highrise Custom Fields. Simply label the field the same name as the Custom Field in Highrise, and the information will be added!
* Fixed issues with notes vs. staff comments (<a href="http://wordpress.org/support/topic/plugin-gravity-forms-highrise-add-on-job-title">as described here</a> - thanks Ted)

### 2.3 ###
*** Improved integration into forms:** now, simply edit Form Settings, click the Advanced tab, and check the box titled "Enable Highrise integration"!  
* Added error notice for logged-in administrators when form does not have required fields.
* Fixed & improved the upload functionality
	- Now works properly for sites in sub-folders
	- Upload links now are correct
	- Supports multiple uploads

### 2.2 ###

* Added to WordPress directory (originally found on <a href="http://www.glidedesign.com/finally-highrise-addon-gravity-forms/">Glide Design</a>
* Made it easier to enable forms to be linked with Highrise
	* Case-insensitive `Highrise` label
	* No longer need to add "yes" as the value in the Advanced tab. If you want, you still can, though :-)
* Fixed many PHP warnings when WordPress development mode is enabled
* Improved layout of settings page and configuration instructions


### 2.1 ###

* Upgraded ability to process multi-field forms and file uploads

### 2.0 ###

* Did more extensive testing with Highrise and added account validation

### 1.1 ###

* Added functionality for hidden form to choose to send info or not and to push tags

### 1.0 ###

* Push functionality for all forms to people and notes