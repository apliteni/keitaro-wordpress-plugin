<?php

class Keitaro_Admin {
    private $plugin_name;
	private $version;
	private $hook_suffix;

	public function __construct( $plugin_name, $version ) {
		$this->plugin_name = $plugin_name;
		$this->version = $version;
	}

    public function add_settings_page() {
        add_options_page('Keitaro Options', 'Keitaro Options', 'manage_options', 'keitaro-admin', array($this, 'create_settings_page'));
    }

	public function enqueue_styles() {
		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/keitaro-admin.css', array(), $this->version, 'all' );
	}

	public function enqueue_scripts() {
		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/keitaro-admin.js', array( 'jquery' ), $this->version, false );
	}

	public function admin_menu() {
        $this->hook_suffix = add_menu_page(
            $this->settings_page_name(),
            'Keitaro',
            'manage_options',
            $this->plugin_name,
            array($this, 'show_settings_page'),
            'none'
        );
    }

	public function show_settings_page() {
        #$this->_prepare_settings();
        //do_action( 'add_' . $this->plugin_name . '_section' );
        echo '<div class="wrap">';
        echo '<header><h1>' . esc_html( $this->settings_page_name() ) . '</h1></header>';
        settings_errors( 'general' );
        echo '<form method="post" action="' . esc_url( admin_url( 'options.php' ), array( 'https', 'http' ) ) . '">';
        settings_fields( $this->hook_suffix );
        do_settings_sections( $this->hook_suffix );
        submit_button();
        echo '</form>';
        echo '</div>';
    }

    public function admin_init() {
        register_setting('keitaro_settings_group', 'keitaro_settings');
        $settings = (array) get_option('keitaro_settings');
        $section = 'keitaro_settings';
        
        add_settings_section(
            $section,
            null,
            null,
            $this->hook_suffix
        );

        add_settings_field(
            'token',
            _('Campaign token', $this->plugin_name),
            array($this, 'campaign_token'),
            $this->hook_suffix,
            $section, array(
                'name' => 'keitaro_settings[token]',
                'value' => $settings['token'],
            )
        );
    }

    public function settings_page_name() {
        return __( 'Keitaro Settings', $this->plugin_name);
    }

    function campaign_token($args) {
        $name = esc_attr($args['name']);
        $value = esc_attr($args['value']);
        echo "<input type='text' name='$name' size='40' value='$value' />";
        echo '<p class="description">';
        echo esc_html( __('Enter campaign token from the campaign settings', $this->plugin_name) );
        echo '</p>';
    }
}
