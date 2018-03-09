<?php

class Plugin_Keitaro {
    protected $loader;
    protected $plugin_name;
    protected $version;
    public function __construct() {
        if ( defined( 'KEITARO_VERSION' ) ) {
            $this->version = KEITARO_VERSION;
        }
        $this->plugin_name = 'keitaro';

        $this->load_dependencies();
        $this->set_locale();
        $this->define_admin_hooks();
        $this->define_public_hooks();
    }

    private function load_dependencies() {
        require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-keitaro-loader.php';
        require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-keitaro-i18n.php';
        require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-keitaro-admin.php';
        require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/class-keitaro-public.php';
        $this->loader = new KEITARO_Loader();
    }

    private function set_locale() {
        $plugin_i18n = new KEITARO_i18n();
        $this->loader->add_action( 'plugins_loaded', $plugin_i18n, 'load_plugin_textdomain' );
    }

    private function define_admin_hooks() {
        $plugin_admin = new KEITARO_Admin( $this->get_plugin_name(), $this->get_version() );
        $this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_styles' );
        $this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts' );
        $this->loader->add_action( 'admin_menu', $plugin_admin, 'admin_menu' );
        $this->loader->add_action( 'admin_init', $plugin_admin, 'admin_init' );
        $this->loader->add_filter( 'plugin_row_meta', $plugin_admin, 'plugin_links', 10, 4 );
    }

    private function define_public_hooks() {
        $plugin_public = new KEITARO_Public( $this->get_plugin_name(), $this->get_version() );
        $this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_scripts' );
        $this->loader->add_action( 'wp', $plugin_public, 'init_tracker' );
        $this->loader->add_action( 'get_footer', $plugin_public, 'get_footer' );
        #$this->loader->add_filter( 'final_output', $plugin_public, 'final_output');
        $this->loader->add_action( 'shutdown', array($plugin_public, 'end_buffer'), 999);
        $this->loader->add_shortcode( 'send_postback', $plugin_public, 'send_postback' );

    }

    public function run() {
        $this->loader->run();
    }

    public function get_plugin_name() {
        return $this->plugin_name;
    }

    public function get_loader() {
        return $this->loader;
    }

    public function get_version() {
        return $this->version;
    }
}
