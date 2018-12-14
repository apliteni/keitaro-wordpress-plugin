=== Keitaro Tracker Integration ===
Contributors: Keitaro Team
Tags: metrics, analytics, keitaro
Requires at least: 3.3
Tested up to: 5.0.1
License: GPLv2
License URI: http://www.gnu.org/licenses/gpl-2.0.html
Requires PHP: 5.3

This plugin integrates WP website with Keitaro tracker.

Features:
  - Create offer links in the posts
  - Perform cloaking in it's set in the campaign
  - Cloaking
  - Sending postbacks

== Terms of Service ==
<a href="https://keitaro.io/en/tos">https://keitaro.io/en/tos</a>

== Installation ==

1. Upload the `keitaro` folder to the `/wp-content/plugins` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Open the campaign, switch tab to "Integration", find there "WordPress".
4. Copy settings block, paste
5. Open section "Keitaro" in the WordPress.
6. Import copied settings. Press "Save Changes".

== Need help? ==
Send us a message on support@keitaro.io or read <a href="https://help.keitaro.io/en/wp-plugin">the knowledge base</a>.

== Frequently Asked Questions ==

= What is Keitaro? =
Keitaro is a self-hosted tracker for affiliate marketers.
More information about Keitaro on page <a href="https://keitaro.io?utm_source=wordpress-plugins">https://keitaro.io</a>.

= Which Keitaro version is needed? =
Keitaro v9.1 or higher.

= Which Keitaro license is needed? =
Professional or Business.

= How to generate offer link? =
Use links with href value `{offer}`.

Full example:

	<a href="{offer}">Buy it now!</a>

= How to specify offer in the links? =
Use macro `{offer:ID}`. Examples:

	<a href="{offer:4}">Offer 1</a>
	<a href="{offer:9}">Offer 2</a>

Full example:

	<a href="{offer}">Buy it now!</a>

In order to get offer link in templates, use `do_shortcode()`:

	<?= do_shortcode('[offer]')?>
	<?= do_shortcode('[offer offer_id="10"]')?>

= How to track conversions (send postback)? =
Use shortcode `[send_postback]` on "Thank you" page.

= How to specify conversion revenue? =
Example:

	[send_postback revenue="100" currency="usd"]

= How to send form data? =
Example:

	[send_postback firstname="$firstname" lastname="$lastname" phone="$phone"]

= How to reset saved state? =
Add parameter `?_reset=1` to page URL.

= How to see page reports in Keitaro? =
Add parameter 'page' to the campaign parameters.

= How to ignore on specific pages? =
  - Add parameter 'page' to the campaign parameters.
  - Use filter 'Page' in the streams. For example, to ignore page 'landing-123', filter should be "Page IS NOT '/landing-page'".


== Changelog ==

= 0.7.2 =
Updated kclick_client.php to v3.5
WordPress 5 Compatibility

= 0.7.1 =
Updated kclick_client.php to v3.4

= 0.7.0 =
Added some new settings to specify different campaign on different pages

= 0.6.3 =
Fix storing token

= 0.6.2 =
Fix storing param _subid in session

= 0.6.1 =
Use _subid over session value

= 0.6.0 =
Ignore prefetch requests

= 0.5.0 =
Do not perform redirects if session is restored

= 0.4.5 =
Added param 'r' to exclude pages from executing actions

= 0.4.4 =
Fixed a bug

= 0.4.3 =
Fixed "Track non-unique visits"

= 0.4.2 =
Fixed issue on php 5.3

= 0.4.1 =
Fixed 'Warning: call_user_func_array() expects parameter 1 to be a valid callback'

= 0.4.0 =
Added shortcode [offer].

= 0.3.3 =
Allow using {offer} in templates

= 0.3.2 =
Fixed an error in RU translation

= 0.3.1 =
Fixed: various incompatibility issues

= 0.3.0 =
Return '#' instead of http://no_offer

= 0.2.2 =
Fixed: error with 'no_offer'

= 0.2.1 =
Fixed error 'substr() expects parameter'

= 0.2.0 =
  * Send param 'page' that contains current page URI to the tracker
  * Fixed: incompatibility with Yandex Webvisor
  * Fixed: tracker runs on system pages, like feed, search, etc.

= 0.1.0 =
Added option 'Force redirect to offer'

= 0.0.9 =
Updated KClickClient

= 0.0.8 =
  * Fixed: an error on php 5.4
  * Fixed: issues with radio buttons

= 0.0.7 =
Fixed: import settings isn't working in FireFox

= 0.0.6 =
  * Better compatibility with landing page builders
  * Added compatibility with WPForms

= 0.0.5 =
Fixed: Option 'Enabled' isn't working

= 0.0.4 =
Fixed: tracker runs on admin dashboard

= 0.0.3 =
Fix RU translation

= 0.0.2 =
Better internalization

= 0.0.1 =
Early alpha version. Implemented:
  * requesting campaigns through Click API v3
  * sending postbacks on [send_postback]
  * generates offer links, includes multi-offer support

== Screenshots ==
1. Settings page

