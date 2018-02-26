<?php

class KEITARO_Public {
    private $version;
    private $client;
    public function __construct( $plugin_name, $version ) {
        $this->plugin_name = $plugin_name;
        $this->version = $version;

        require_once plugin_dir_path( __FILE__  ). '../includes/kclick_client.php';

        $this->client = new KClickClient(
            $this->get_option('tracker_url') . '/api.php?',
            $this->get_option('token')
        );
    }
    public function enqueue_scripts() {
        //wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/keitaro-public.js', array( 'jquery' ), $this->version, false );
    }

    public function get_footer()
    {
        if ($this->get_option('enabled') && $this->get_option('debug') === 'yes') {
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

    public function init_tracker() {
        if (!$this->get_option('enabled')) {
            return false;
        }

        if ($this->get_option('debug') && isset($_GET['_reset'])) {
            if (!headers_sent()) {
                session_start();
            }
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

        $this->client->sendAllParams();
        if ($this->get_option('use_title_as_keyword') === 'yes') {
            $this->client->param('default_keyword', get_the_title());
        }
        $this->client->restoreFromQuery();

        if ($this->get_option('track_hits')) {
            $this->client->restoreFromSession();
        }

        $this->client->executeAndBreak();
    }

    public function the_content($content)
    {
        if (preg_match_all('/(http[s]?:\/\/)?\{offer:?([0-9])?\}/si', $content, $result)) {
            foreach ($result[0] as $num => $macro) {
                if ($result[2][$num]) {
                    $offer_id = $result[2][$num];
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
        $options = array();
        if (!empty($offer_id)) {
            $options['offer_id'] = $offer_id;
        }
        return $this->client->getOffer($options);
    }

    public function send_postback($attrs)
    {
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

        $url = $postback_url;
        $attrs['sub_id'] = $this->client->getSubId();

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
        $httpClient = new KHttpClient();
        $response = $httpClient->request($url, array());
        if ($response != 'Success') {
            echo 'Error while sending postback: ' . $response;
        }
    }

    private function find_variable($name)
    {
        foreach ([$_SESSION, $_POST, $_GET] as $source) {
            if (isset($source[$name])) {
                return $source[$name];
            }
        }
    }
}
