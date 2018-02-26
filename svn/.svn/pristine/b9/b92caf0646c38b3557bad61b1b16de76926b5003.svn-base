<?php
/**
 * @wordpress-plugin
 * Plugin Name:       Keitaro Tracker Integration
 * Plugin URI:        https://github.com/apliteni/keitaro-wordpress-plugin
 * Description:       This plugin integrates WP with Keitaro tracker.
 * Version:           0.0.1
 * Author:            Artur Sabirov
 * Author URI:        https://github.com/asabirov
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       keitaro
 */

if ( ! defined( 'WPINC' ) ) {
	die;
}

define( 'KEITARO_VERSION', '0.0.1' );

function activate_plugin_name() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-keitaro-activator.php';
	KEITARO_Activator::activate();
}

function deactivate_plugin_name() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-keitaro-deactivator.php';
	KEITARO_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_plugin_name' );
register_deactivation_hook( __FILE__, 'deactivate_plugin_name' );

require plugin_dir_path( __FILE__ ) . 'includes/class-keitaro.php';

function run_plugin_name() {
	$plugin = new Plugin_Name();
	$plugin->run();
}
run_plugin_name();
