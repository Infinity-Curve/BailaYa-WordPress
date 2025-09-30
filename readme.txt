=== BailaYa ===
Contributors: bailaya
Tags: dance, studio, schedule, classes, instructors, profiles
Requires at least: 6.0
Tested up to: 6.6
Requires PHP: 8.1
Stable tag: 0.1.0
License: ISC
License URI: https://opensource.org/licenses/ISC

Embed BailaYa schedules, instructors, and profiles in WordPress using shortcodes and Gutenberg blocks.

== Description ==

BailaYa for WordPress lets you easily integrate your studio’s public profile, instructors, class schedules, and more into your WordPress site.

The plugin provides:

* Gutenberg blocks for embedding schedules, instructors, and profiles
* Shortcodes for use in classic editor or widgets
* API integration with the official [BailaYa public API](https://bailaya.com)
* Automatic date parsing and JSON fallbacks
* Optional studio ID configuration in plugin settings

This plugin is built on top of the **BailaYa Core API client** and mirrors the functionality available in the React library (`@bailaya/react`).

== Features ==

* **Blocks**
  * Class Schedule
  * Class Schedule by Type
  * Instructor List
  * Studio Profile Card
  * User Profile Card

* **Shortcodes**
  * `[bailaya_class_schedule]`
  * `[bailaya_class_schedule_by_type type="salsa"]`
  * `[bailaya_instructor_list]`
  * `[bailaya_studio_profile]`
  * `[bailaya_user_profile id="user-123"]`

* **Options**
  * Configure default studio ID in the admin settings page
  * Override studio ID per block/shortcode
  * Localize labels and descriptions
  * Customize styling classes

== Installation ==

1. Upload the plugin files to the `/wp-content/plugins/bailaya` directory, or install via the WordPress admin.
2. Activate the plugin through the 'Plugins' screen.
3. Go to **Settings → BailaYa** to configure your default Studio ID.
4. Use the provided blocks in the block editor or shortcodes in classic editor.

== Frequently Asked Questions ==

= Do I need a BailaYa account? =
Yes. You need a studio ID or user ID from the BailaYa platform to display your data.

= Can I override the studio ID per block or shortcode? =
Yes. Most blocks and shortcodes support an `override_id` attribute.

= Does this work with both Gutenberg and Classic Editor? =
Yes. You can use Gutenberg blocks or the provided shortcodes.
