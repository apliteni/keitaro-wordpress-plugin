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
        #add_options_page('Keitaro Options', 'Keitaro Options', 'manage_options', 'keitaro-admin', array($this, 'create_settings_page'));
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
        echo '<form method="post" action="options.php">';
        settings_fields( $this->hook_suffix );
        do_settings_sections( $this->hook_suffix );
        submit_button();
        echo '</form>';
        echo '</div>';
    }

    public function admin_init() {
        register_setting($this->hook_suffix, 'keitaro_settings');

        $settings = (array) get_option('keitaro_settings');
        $section = 'keitaro_main_section';
        $yesNoOptions = [
            ['name' => _('Yes', $this->plugin_name), 'value' => 'yes'],
            ['name' => _('No', $this->plugin_name), 'value' => 'no'],
        ];

        add_settings_section(
            $section,
            _('Main', $this->plugin_name),
            null,
            $this->hook_suffix
        );
        add_settings_field(
            'enabled',
            _('Enabled', $this->plugin_name),
            array($this, 'radio_buttons'),
            $this->hook_suffix,
            $section, array(
                'name' => 'keitaro_settings[enabled]',
                'value' => isset($settings['enabled']) ? $settings['enabled'] : 'no',
                'options' => $yesNoOptions,
                'description' => _('Choose "no" to disable Keitaro', $this->plugin_name),
            )
        );

        add_settings_field(
            'tracker_url',
            _('Tracker URL', $this->plugin_name),
            array($this, 'text_input'),
            $this->hook_suffix,
            $section, array(
                'name' => 'keitaro_settings[tracker_url]',
                'value' => $settings['tracker_url'],
                'size' => 100,
                'placeholder' => 'http://your-tracker.com/',
                'description' => _('Where Keitaro installed', $this->plugin_name),
            )
        );

        add_settings_field(
            'postback_url',
            _('Postback URL', $this->plugin_name),
            array($this, 'text_input'),
            $this->hook_suffix,
            $section, array(
                'name' => 'keitaro_settings[postback_url]',
                'value' => $settings['postback_url'],
                'size' => 100,
                'placeholder' => 'http://your-tracker.com/123/postback',
                'description' => _('Where to send postbacks', $this->plugin_name),
            )
        );

        add_settings_field(
            'token',
            _('Campaign token', $this->plugin_name),
            array($this, 'text_input'),
            $this->hook_suffix,
            $section, array(
                'name' => 'keitaro_settings[token]',
                'value' => $settings['token'],
                'size' => 35,
                'description' => __('Enter campaign token from the campaign settings', $this->plugin_name)
            )
        );

        $section = 'keitaro_advanced_section';
        add_settings_section(
            $section,
            _('Advanced', $this->plugin_name),
            null,
            $this->hook_suffix
        );

        add_settings_field(
            'debug',
            _('Debug enabled', $this->plugin_name),
            array($this, 'radio_buttons'),
            $this->hook_suffix,
            $section, array(
                'name' => 'keitaro_settings[debug]',
                'value' => isset($settings['debug']) ? $settings['debug'] : 'no',
                'options' => $yesNoOptions,
                'description' => _('You\'ll see request and response to Click API on all pages', $this->plugin_name),

            )
        );

    }

    private function settings_page_name() {
        return __( 'Keitaro Settings', $this->plugin_name);
    }

    function text_input($args) {
        $name = esc_attr($args['name']);
        $value = esc_attr($args['value']);
        $size = esc_attr($args['size']);
        $placeholder = esc_attr($args['placeholder']);
        $description = esc_attr($args['description']);

        echo "<input type='text' name='$name' size='$size' value='$value' placeholder='$placeholder' />";
        if (!empty($description)) {
            echo '<p class="description">';
            echo esc_html($description);
            echo '</p>';
        }
    }

    function radio_buttons($args) {
        $name = esc_attr($args['name']);
        $value = esc_attr($args['value']);
        $options = $args['options'];
        $description = esc_attr($args['description']);

        foreach ($options as $option) {
            if ($option['value'] == $value) {
                $checked = 'checked';
            } else {
                $checked = '';
            }
            echo "<label for='$name'>
            <input type='radio' name='$name' id='$name' value='{$option['value']}' $checked>
                {$option['name']}
            </label>&nbsp;&nbsp;";
        }
        if (!empty($description)) {
            echo '<p class="description">';
            echo esc_html($description);
            echo '</p>';
        }
    }
}
