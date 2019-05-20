<?php

class KEITARO_Public {
    private $version;
    private $client;
    public function __construct( $plugin_name, $version ) {
        require_once plugin_dir_path( __FILE__  ). '../includes/kclick_client.php';

        $this->plugin_name = $plugin_name;
        $this->version = $version;

        $this->client = new KClickClient(
            $this->get_option('tracker_url') . '/api.php?',
            $this->get_option('token')
        );
    }

    public function get_footer()
    {
        if ($this->get_option('enabled') === 'yes' && $this->get_option('debug') === 'yes') {
            echo '<hr>';
            echo 'Keitaro debug output:<br>';
            echo implode('<br>', $this->client->getLog());
            echo '<hr>';
        }
    }

    private function get_option($key) {
        $settings = (array) get_option( $this->plugin_name . '_settings' );
        return isset($settings[$key]) ? $settings[$key] :null;
    }

    private function get_page_options($key) {
        $settings = (array) get_option( $this->plugin_name . '_page_settings' );
        return isset($settings[$key]) ? $settings[$key] :null;
    }

    private function get_page_option_for($id) {
        $pages = $this->get_page_options('pages');
        return (isset($pages[$id])) ? $pages[$id] : null;
    }

    public function init_tracker() {
        // Check if enabled
        if ($this->get_option('enabled') !== 'yes') {
            return false;
        }

        // Do not run on system pages
        if (is_admin() || is_feed() || is_search() || is_date() || is_month() ||
            is_year() || is_attachment() || is_author() || is_trackback() ||
            is_comment_feed() || is_robots() || is_tag() ) {
            return false;
        }

        // Allow webvisor to see original content
        if ( $this->is_webvisor() ) {
            return false;
        }

        // Custom page settings
        if ($this->get_page_options('specify_pages') === 'yes') {
            if (get_post_type() !== 'page') {
                return false;
            }

            $value = $this->get_page_option_for(get_the_ID());

            switch ($value) {
                case '':
                    return;
                    break;
                case 'primary_campaign':
                    break;
                default:
                    $this->client->token($value);
            }
        }

        $this->start_buffer();

        if (!headers_sent()) {
            session_start();
        }

        $this->client->param('page', $_SERVER['REQUEST_URI']);

        if ($this->get_option('debug') === 'yes' && isset($_GET['_reset'])) {
            unset($_SESSION[KClickClient::STATE_SESSION_KEY]);
        }

        if (!$this->get_option('tracker_url')) {
            echo "<!-- No tracker URL defined -->";
            return false;
        }

        if (!$this->get_option('token')) {
            echo "<!-- No campaign token defined -->";
            return false;
        }

        if ($this->get_option('force_redirect_offer') === 'yes') {
            $this->client->forceRedirectOffer();
        }

        $this->client->sendAllParams();
        if ($this->get_option('use_title_as_keyword') === 'yes') {
            $this->client->param('default_keyword', get_the_title());
        }

        $this->client->restoreFromQuery();

        if (isset($_GET['r'])) {
            return;
        }

        if ($this->get_option('track_hits') !== 'yes' && !$this->client->isStateRestored()) {
            $this->client->restoreFromSession();
        }

        $this->client->executeAndBreak();
    }

    public function final_output($content)
    {
        $patterns = array(
            '/http[s]?:\/\/{offer:?([0-9]*)?}/si',
            '/http[s]?:\/\/offer:?([0-9]*)?/si',
            '/{offer:?([0-9]*)?}/si'
        );
        foreach ($patterns as $pattern) {
            $content = $this->replace_with_pattern($pattern, $content);
        }
        return $content;
    }

    private function replace_with_pattern($pattern, $content)
    {
        if (preg_match_all($pattern, $content, $result)) {
            foreach ($result[0] as $num => $macro) {
                if (isset($result[1][$num]) && intval($result[1][$num]) > 0) {
                    $offer_id = intval($result[1][$num]);
                } else {
                    $offer_id = null;
                }
                $content = str_replace($macro, $this->get_offer_url($offer_id), $content);
            }
        }
        return $content;
    }

    public function get_offer_url($offer_id = null)
    {
        if ($this->get_option('enabled') !== 'yes') {
            return '#keitaro_plugin_disabled';
        }

        $options = array();
        if (!empty($offer_id)) {
            $options['offer_id'] = $offer_id;
        }
        return $this->client->getOffer($options, '#');
    }

    private function start_buffer()
    {
        ob_start(array($this, "final_output"));
    }

    public function end_buffer()
    {
        if (ob_get_length()) ob_end_flush();
    }

    public function offer_short_code($attrs)
    {
        $offer_id = (isset($attrs['offer_id'])) ? $attrs['offer_id'] : null;
        return $this->get_offer_url($offer_id);
    }

    public function send_postback($attrs)
    {
        if (empty($attrs)) {
            $attrs = array();
        }
        if ($this->get_option('enabled') !== 'yes') {
            return 'Keitaro integration disabled';
        }
        $postback_url = $this->get_option('postback_url');
        $sub_id = $this->client->getSubId();
        if (!$postback_url) {
            echo 'No \'postback_url\' defined';
            return;
        }

        if (empty($sub_id)) {
            echo 'No \'sub_id\' defined';
            return;
        }

        $attrs = array_merge($attrs, $this->add_wpforms_fields());

        $url = $postback_url;
        $attrs['subid'] = $this->client->getSubId();

        if (strstr($url, '?')) {
            $url .=  '&';
        } else {
            $url .=  '?';
        }

        foreach ($attrs as $key => $value) {
            if (substr($value, '0', 1) === '$') {
                $attrs[$key] = $this->find_variable(substr($value, '1'));
            }
        }

        $url .= http_build_query($attrs);
        $this->client->log('Send postback:' . $url);
        $httpClient = new KHttpClient();
        $response = $httpClient->request($url, array());
        if ($response != 'Success') {
            echo 'Error while sending postback: ' . $response;
        }
    }

    private function find_variable($name)
    {
        foreach (array($_SESSION, $_POST, $_GET) as $source) {
            if (isset($source[$name])) {
                return $source[$name];
            }
        }
    }

    private function add_wpforms_fields()
    {
        $fields = array();
        if (isset($_POST['wpforms']) && isset($_POST['wpforms']['fields'])) {
            foreach($_POST['wpforms']['complete'] as $field) {
                $fields[] = $field['name'] .': '. $field['value'];
            }
        }
        if (!empty($fields)) {
            return array('form' => join(', ', $fields));
        } else {
            return array();
        }
    }

    private function is_webvisor()
    {
        $check = 'mtproxy.yandex.net';
        return strstr($_SERVER['HTTP_HOST'], $check) ||
            strstr($_SERVER['HTTP_X_REAL_HOST'], $check);
    }
}
