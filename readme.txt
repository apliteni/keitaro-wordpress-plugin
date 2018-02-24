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

== Installation ==

1. Upload the `keitaro` folder to the `/wp-content/plugins` directory
2. Activate the plugin through the 'Plugins' menu in WordPress

== Frequently Asked Questions ==

= What is Keitaro? =
Keitaro is a self-hosted tracker for affiliate marketers.

= How to generate offer link? =
Use links with href value %{offer}%.

Full example:
<a href="{offer}">Buy it now!</a>

= How to specify offer in the links? =
Use macro %{offer:ID}%. Examples:
%{offer:4}%
%{offer:9}%,

Full example:
%<a href="{offer}">Buy it now!</a>%


= How to track conversions (send postback)? =
Use shortcode %[send_postback]% on "Thank you" page.

= How to specify conversion revenue? =
Example:
%[send_postback revenue="100" currency="usd"]%


== Changelog ==

= 0.0.1 =
Early alpha version. Implemented:
  * requesting campaigns through Click API v3
  * sending postbacks on [send_postback]
  * generates offer links, includes multi-offer support

== Screenshots ==
Settings page


== Terms of Service ==
[https://keitarotds.com/tos]