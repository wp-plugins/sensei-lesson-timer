=== Plugin Name ===
Contributors: skinnycat
Donate link: http://www.skinnycatsoftware.com
Tags: sensei
Requires at least: 3.9.2
Tested up to: 4.3
Stable tag: 1.1.1
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

This plugin adds a countdown timer to the Sensei lesson disabling the complete button until the timer reaches zero.

== Description ==

Plugin Sensei Lesson Timer allows you to manage time spent in a lesson for student learners. A visual countdown timer is added next to the "Complete Lesson" button, disabling the button until the countdown has hit zero. This Plugin has been tested as best as possible with WooTheme-Sensei and WooThemes Sensei-module. Supports latest versions of Chrome, IE, Safari,Firefox, and Opera.

== Installation ==

1. Unzip sensei_lesson_timer.zip.
1. Upload the sensei_lesson_timer folder (the folder itself, NOT it's contents) to the /wp-content/plugins directory.
1. Activate the plugin through the Admin Controls under Plugins > Installed Plugins.

== Frequently Asked Questions ==

= How do I set the time for the countdown timer within a lesson? =

When you edit a lesson, set the lesson time in Lesson Information.

= Can I set a value of less then one minute?  =

No. Values are set in 1 minute incraments.

== Screenshots ==

1. Screenshot of timer embedded in lesson page. Timer sits to the right of the Complete Lesson button.
1. Screenshot of message popup within a lesson page when a student learner tries to leave the lesson page.
1. Screenshot of Admin Timer message. Under Sensei->Settings, at the center of the page, you will see a Warning message field. If left blank, lessons with lesson time will allow student learners to leave the page with no warning. If it contains a message, it will display the message when a student learner tries to click away from the lesson page prior to timer reaching zero. Other features added are the ability to add lesson timer to a quiz, auto complete lesson when timer reaches zero, Pause lesson timer when browser is not being viewed, timer placement, & disable lesson timer by role.

== Changelog ==

= 1.1.1 =
* Restructured plugin code to be more in line with WordPress plugin coding standards. Moved inline JavaScript and CSS to external files loaded properly through wp_enqueue_script and wp_enqueue_style.
* Added support for i18n translations. see /languages directory content.
* Moved plugin settings to be within Sensei > Sensei Settings. Look for new tab 'Lesson Timer'.
* Correct timer displays to include leading zero digits.
* Added settings option to auto-submit Lesson form when timer reaches zero.
* Added settings option to control where the timer digits are displayed in relation to lesson complete button. Options are Outside Right (default), Outside Left, Hide Button, Add timer to button text, replace button text wth timer digits. 
* Added WPML Translation configuration file wpml-config.xml to support translation of text and values via WPML

= 1.1 =
* Added setting (Settings > Reading) to show warning message to user if they attempt to leave page with an active time. (paul@codehooligans.com)

= 1.0 =
* First release version.

== Upgrade Notice ==
=
== Arbitrary section ==

== A brief Markdown Example ==

