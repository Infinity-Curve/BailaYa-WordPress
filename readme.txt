=== BailaYa ===
Contributors: infinitycurve
Tags: dance, schedule, classes, booking, studio
Requires at least: 6.0
Tested up to: 7.0
Requires PHP: 8.1
Stable tag: 1.8.4
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Embed your BailaYa class schedules, events, instructors, packages and locations in WordPress with shortcodes and blocks.

== Description ==

**This plugin requires a BailaYa account.** BailaYa (https://www.bailaya.com) is a
third-party dance studio management service, operated by Infinity Curve LLC. The plugin
displays and manages data held in your BailaYa studio, so it does nothing on its own —
you need a BailaYa studio and its Studio ID for any of it to work. Signing up is done at
https://www.bailaya.com, not through this plugin.

See the **External services** section below for exactly what data is sent to BailaYa and
when.

BailaYa for WordPress puts your dance studio's live data on your own website. Class
schedules, events, instructors, class packages and studio locations are pulled from
your BailaYa account and rendered on your pages — no copy-pasting, no going stale.

Everything on display uses BailaYa's **public API** and needs nothing more than your
Studio ID.

Studios that add an API key also get a **BailaYa** admin menu for managing classes,
events, students, instructors, team members, packages, rooms and locations without
leaving WordPress. Optionally, visitors can sign in to your site with their BailaYa
account (OpenID Connect).

= Blocks and shortcodes =

* Class Schedule — `[bailaya_class_schedule]`
* Class Schedule by Type — `[bailaya_class_schedule_by_type type="salsa"]`
* Event Schedule — `[bailaya_events]`
* Instructor List — `[bailaya_instructors]`
* Studio Profile Card — `[bailaya_studio_profile]`
* User Profile Card — `[bailaya_user_profile id="user-123"]`
* Private Lesson Instructors — `[bailaya_private_lesson_instructors]`
* Class Packages — `[bailaya_packages]`
* Locations — `[bailaya_locations]`

Every block and shortcode accepts an `override_id` so a single site can show more than
one studio, and exposes its CSS class names so you can style it to match your theme.

= Languages =

The plugin ships translations for the same six languages as BailaYa itself: English,
Spanish, French, German, Russian and Georgian. It follows your site's language
automatically.

= Locations =

Studios can have several locations. `[bailaya_locations]` lists them all, primary
first, each with its address and a link to open it in Google Maps.

The Studio Profile Card shows the **primary** location, falling back to the studio's
plain address for studios that have no locations configured. Pass
`show_all_locations="true"` (or tick **Show all locations** on the block) to list every
one instead.

= Managing your studio from WordPress =

Add an API key under **Settings → BailaYa** to unlock the **BailaYa** admin menu:

* **Classes** and **Events** — create (including weekly recurrence), edit and delete
* **Students**, **Instructors** and **Team** — create, edit and delete
* **Packages** — create, edit, activate and deactivate
* **Rooms** and **Locations** — create, edit and delete

Managing this data requires the `manage_options` capability. Deleting a package that
still has active subscribers is refused — deactivate it instead, which stops new sales
while leaving existing subscriptions intact.

= Sign in with BailaYa =

Turn on **Sign in with BailaYa** to add a sign-in button to the WordPress login screen.
Visitors authenticate against BailaYa's OpenID Connect provider (authorization code with
PKCE) and are matched to a WordPress account — first by a previously linked BailaYa
account, then by email address. Sign-in is refused if BailaYa reports the address as
unverified. With auto-create off, only people who already have a WordPress account can
sign in.

Because these accounts are created for people who are not logged in, the role given to
them is restricted: only roles that cannot administer the site, manage other users or
publish unfiltered HTML can be chosen, so Administrator and Editor are not on offer. The
role is re-checked against its capabilities at the moment the account is created.

The `bailaya_oauth_login` action fires after a successful sign-in with the `WP_User`,
the OIDC claims and the tokens.

== External services ==

This plugin connects to the **BailaYa API** to fetch and manage your studio's data. It
is required for the plugin to do anything: without it there is no schedule, no
instructor list and no way to manage your studio.

**What is sent, and when**

* On any page that renders a BailaYa block or shortcode, the plugin requests your
  studio's public data from `https://www.bailaya.com/api/public/...`. The request
  carries your **Studio ID** (and, where relevant, the user ID or dance type named in
  the shortcode). Responses are cached in WordPress transients for the cache lifetime
  you configure.
* If you enter an **API key** or **access token**, the plugin sends it as a bearer token
  to `https://www.bailaya.com/api/v1/...` when you use the BailaYa admin screens, along
  with whatever data you enter there (class, student, package details, and so on).
* If you enable **Sign in with BailaYa**, the person signing in is redirected to
  `https://www.bailaya.com/api/oidc/...`. BailaYa returns their profile claims (name and
  email address) so a WordPress account can be matched or created.

No data is sent to BailaYa for visitors who merely browse your site, beyond the request
your server makes for the studio's public data.

The API base URL is configurable, so self-hosted BailaYa installations can point the
plugin elsewhere.

BailaYa (https://www.bailaya.com) is a service operated by Infinity Curve LLC. By using
this plugin you are sending data to, and receiving data from, that service:

* Terms of Service: https://www.bailaya.com/terms
* Privacy Policy: https://www.bailaya.com/privacy

== Installation ==

This plugin requires a BailaYa account. If you do not have one, create your studio at
https://www.bailaya.com first — the plugin has nothing to display without it.

1. Upload the plugin files to `/wp-content/plugins/bailaya`, or install it through the
   **Plugins → Add New** screen.
2. Activate the plugin through the **Plugins** screen.
3. Go to **Settings → BailaYa** and enter the **Studio ID** from your BailaYa account.
4. Add a BailaYa block in the block editor, or a shortcode in the classic editor.

Optionally, add an API key on the same settings screen to manage your studio's data from
the **BailaYa** admin menu, and enable **Sign in with BailaYa** to let visitors sign in
with their BailaYa account.

== Frequently Asked Questions ==

= Do I need a BailaYa account? =

Yes. This plugin is a front end for the BailaYa service (https://www.bailaya.com), a
third-party dance studio management platform operated by Infinity Curve LLC. Without a
BailaYa studio and its Studio ID the plugin has nothing to show. You sign up at
bailaya.com; the plugin does not create accounts.

= Is BailaYa free? =

The plugin is free and open source (GPLv2 or later). BailaYa itself is a separate
service with its own terms and pricing — see https://www.bailaya.com.

= Do I need an API key? =

Not for displaying data — the blocks and shortcodes use BailaYa's public API and need
only your Studio ID. An API key is required only for the admin screens that create and
edit studio data.

= Can I show more than one studio on the same site? =

Yes. Every block and shortcode accepts an `override_id` attribute that overrides the
default Studio ID for that instance.

= Does this work with both the block editor and the classic editor? =

Yes. Each feature ships as a block and as a shortcode.

= Can visitors sign in with their BailaYa account? =

Yes. Enable **Sign in with BailaYa** in the settings and register the redirect URI shown
there with your BailaYa OAuth client.

= Why can't I delete a package? =

A package that someone has an active subscription to cannot be deleted, because deleting
it would erase what they paid for. Deactivate it instead to stop new sales while leaving
existing subscriptions intact.

= How do I stop the data being cached? =

Set the cache lifetime to 0 on the settings screen, or pass `cache_ttl="0"` to an
individual shortcode.

== Changelog ==

= 1.8.4 =
* "Sign in with BailaYa" can no longer auto-create privileged accounts: only roles
  that cannot administer the site, manage users or post unfiltered HTML are offered,
  and the role is re-checked before the account is created.
* Sign-in is refused when BailaYa reports the account's email address as unverified.
* Admin notices on the management screens are no longer passed through the URL.
* Updated bundled libraries.

= 1.8.3 =
* Initial public release. The version number matches the BailaYa API clients the plugin
  is built on.
* Blocks and shortcodes for class schedules, schedules by dance type, events,
  instructors, private lesson instructors, class packages, locations, and studio and
  user profile cards.
* Studio Profile Card shows the studio's primary location, with an option to list every
  location.
* Optional BailaYa admin menu for managing classes, events, students, instructors, team
  members, packages, rooms and locations, unlocked with an API key.
* Optional "Sign in with BailaYa" (OpenID Connect) for WordPress users.
* Configurable API base URL, default Studio ID, and response cache lifetime.
* Translations for English, Spanish, French, German, Russian and Georgian.
