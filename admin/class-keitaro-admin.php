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
        echo '<form id="keitaro-settings-form" method="post" action="options.php">';
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
            'import',
            '',
            array($this, 'import_settings'),
            $this->hook_suffix,
            $section
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

        add_settings_field(
            'use_title_as_keyword',
            _('Use post title as keyword', $this->plugin_name),
            array($this, 'radio_buttons'),
            $this->hook_suffix,
            $section, array(
                'name' => 'keitaro_settings[use_title_as_keyword]',
                'value' => isset($settings['use_title_as_keyword']) ? $settings['use_title_as_keyword'] : 'no',
                'options' => $yesNoOptions,
                'description' => _('Choose \'yes\' in order to use post title as keyword', $this->plugin_name),
            )
        );

        add_settings_field(
            'track_hits',
            _('Track non-unique visits', $this->plugin_name),
            array($this, 'radio_buttons'),
            $this->hook_suffix,
            $section, array(
                'name' => 'keitaro_settings[track_hits]',
                'value' => isset($settings['track_hits']) ? $settings['track_hits'] : 'yes',
                'options' => $yesNoOptions,
                'description' => _('If you want to track only unique visits, set \'yes\'. 
                    In order to send every visits as clicks to Keitaro, choose \'no\'. This option respects cookies ttl that is set in Keitaro campaign.', $this->plugin_name),
            )
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

    public function import_settings()
    {
        echo '<div id="keitaro-import-success" style="display:none" class="updated settings-error notice">' .
            _('Settings successfully imported', $this->plugin_name)
            . '</div>';

        echo '<a href="#" id="keitaro-import-settings" class="button">' .
            _('Import settings', $this->plugin_name)
        . '</a>';
        echo '<textarea id="keitaro-import-box" style="display:none" rows="10"></textarea>';
        echo '<p><a href="#" id="keitaro-import-button" class="button button-primary" style="display:none">' .
            _('Import', $this->plugin_name)
            . '</a></p>';

    }

    public function plugin_links( $links, $plugin_file, $plugin_data ) {
        if ( isset( $plugin_data['PluginURI'] ) && false !== strpos( $plugin_data['PluginURI'], 'keitarotds.com' ) ) {
            $slug = basename( $plugin_data['PluginURI'] );
            $links[] = sprintf( '<a href="%s" class="thickbox" title="%s">%s</a>',
                self_admin_url( 'plugin-install.php?tab=plugin-information&amp;plugin=' . $slug . '&amp;TB_iframe=true&amp;width=600&amp;height=550' ),
                esc_attr( sprintf( __( 'More information about %s' ), $plugin_data['Name'] ) ),
                __( 'View details' )
            );
        }
        return $links;
    }
}
