=== Keitaro Tracker Integration ===
Contributors: Artur Sabirov
Tags: metrics, analytics, adsbridge, voluum, keitaro
Requires at least: 3.3
Tested up to: 4.9.4
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
<a href="https://keitarotds.com/tos">https://keitarotds.com/tos</a>

== Installation ==

1. Upload the `keitaro` folder to the `/wp-content/plugins` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Open the campaign, switch tab to "Integration", find there "WordPress".
4. Copy settings block, paste
5. Open section "Keitaro" in the WordPress.
6. Import copied settings. Press "Save Changes".

== Need help? ==
Send us a message on support@keitarotds.com or read <a href="https://help.keitarotds.com/wp-plugin">the knowledge base</a>.

== Frequently Asked Questions ==

= What is Keitaro? =
Keitaro is a self-hosted tracker for affiliate marketers.
More information about Keitaro on page <a href="https://keitarotds.com?utm_source=wordpress-plugins">https://keitarotds.com</a>.

= Which Keitaro version is needed? =
Keitaro v9.1 or higher.

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


== Changelog ==

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

