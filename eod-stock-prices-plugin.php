<?php
/*
Plugin Name: Stock Prices plugin
Plugin URI: https://eodhistoricaldata.com/knowledgebase/plugins
Description: The stock prices plugin allows you to use a widget and a shortcode to display the ticker data you want.
Version: 1.0
Author: Eod Historical Data
Author URI: https://eodhistoricaldata.com
*/
require ('admin/eod-stock-prices-admin.php');
require('widget/eod-stock-prices-widget.php');

if(!class_exists('EOD_Stock_Prices_Plugin'))
{
    class EOD_Stock_Prices_Plugin
    {
        /**
         * Static load template method
         * @param $templatePath
         * @param $vars
         * @return string
         */
        public static function loadTemplate($templatePath, $vars) {
            //Load template
            $template = dirname(__FILE__)."/".$templatePath;
            ob_start();
            extract($vars);
            include $template;
            $content = ob_get_contents();
            ob_end_clean();

            return $content;
        }

        /**
         * Prepare plugin hooks / filters
         */
        public function __construct(){
            /* Runs when plugin is activated */
            register_activation_hook(__FILE__,array(&$this,'activate'));
            /* Runs on plugin deactivation*/
            register_deactivation_hook( __FILE__, array(&$this,'deactivate'));


            $plugin = plugin_basename( __FILE__ );
            add_filter( "plugin_action_links_$plugin", array(&$this, 'add_plugins_list_link') );

            add_action('init', array(&$this, 'shortcodes_init'));
            add_action( 'widgets_init', array(&$this, 'widgets_init'));
            add_action( 'wp_enqueue_scripts',  array(EOD_Stock_Prices_Plugin::class, 'enqueue_scripts'));

            $this->register_ajax_routes();
            $this->admin = new EOD_Stock_Prices_Admin();
        }


        /**
         * Called when the plugin is deactivated
         */
        public function activate(){

        }

        /**
         * Called when the plugin is deactivated
         */
        public function deactivate(){

        }

        /**
         *
         */
        public function widgets_init(){
            register_widget( 'EOD_Stock_Prices_Widget' );
        }

        /**
         *
         */
        protected function register_ajax_routes(){
            $ajaxRoutes = array(
              'eod_stock_prices_refresh'
            );

            foreach($ajaxRoutes as $route){
                add_action('wp_ajax_'.$route, array(&$this,'ajax_'.$route));
                add_action('wp_ajax_nopriv_'.$route, array(&$this,'ajax_'.$route));
            }

        }

        /**
         * @param $links
         * @return mixed
         */
        function add_plugins_list_link( $links ) {
            $settings_link = '<a href="admin.php?page=eod-stock-prices-admin">' . __( 'Settings' ) . '</a>';
            array_push( $links, $settings_link );

            $plugin_page_link = '<a href="https://eodhistoricaldata.com/">' . __( 'EOD Historical Data' ) . '</a>';
            array_push( $links, $plugin_page_link );
            return $links;
        }


        /**
         * Shortcode initialization
         */
        public function shortcodes_init()
        {
            //HTML rendering
            add_shortcode('eod_ticker', array(&$this,'eod_ticker'));
        }


        /**
         * Shortcode JS Scripts to add for shortcode containing pages
         */
        public static function enqueue_scripts($widget_base_id = null) {
            if($widget_base_id === null){
                $widget_base_id = EOD_Stock_Prices_Widget::$widget_base_id;
            }
            global $post;
            if( has_shortcode( $post->post_content, 'eod_ticker')
                || is_active_widget( false, false, $widget_base_id)
            ) {
                wp_enqueue_script( 'eod_stock-prices-plugin', plugins_url( '/js/eod-stock-prices.js', __FILE__ ), array('jquery') );

                wp_enqueue_style('eod_stock-prices-plugin',plugins_url('/css/eod-stock-prices.css',__FILE__));
                $protocol = isset( $_SERVER['HTTPS'] ) ? 'https://' : 'http://';

                // Set the ajaxurl Parameter which will be accessible from javascript
                $params = array(
                    // Get the url to the admin-ajax.php file using admin_url()
                    'ajaxurl' => admin_url( 'admin-ajax.php', $protocol ),
                );
                // Print the script to our page
                wp_localize_script( 'eod_stock-prices-plugin', 'eod_params', $params );

                //Force not to cache page
                //define( 'DONOTCACHEPAGE', true );
            }
        }

        /**
         *
         */
        public function ajax_eod_stock_prices_refresh(){
           $tickerTarget = $_GET['target'];

           $result = self::get_real_time_ticker($tickerTarget);
           echo json_encode($result);
           wp_die();
        }


        /**
         * Shortcode EOD Ticker
         * @param array $atts
         * @param null $content
         * @param string $tag
         * @return string
         */
        public function eod_ticker($atts=[], $content = null, $tag = ''){
            $atts = array_change_key_case((array)$atts, CASE_LOWER);
            // override default attributes with user attributes
            $shortcode_atts = shortcode_atts([
                'target' => 'AAPL.US'
            ], $atts, $tag);

            $tickerData = self::get_real_time_ticker($shortcode_atts['target']);

            return self::loadTemplate("template/ticker.php", array('tickerData' => $tickerData, 'target' => $shortcode_atts['target']));
        }

        /**
         * get Ticker infos and calculate evolution
         * @param $target
         * @return mixed
         */
        public static function get_real_time_ticker($target){

            $tickerData = self::call_eod_real_time_api($target);

            if(!$tickerData){
                return array('error' => 'no result from real time api');
            }

            if(array_key_exists('error', $tickerData)){
                return $tickerData;
            }

            $tickerData['evolution'] =  round($tickerData['open'] - $tickerData['close'],2);
            $tickerData['evolutionClass'] = $tickerData['evolution'] > 0 ? 'plus' : ($tickerData['evolution'] == 0 ? 'equal' : 'minus');
            $tickerData['evolutionSymbol'] = $tickerData['evolution'] > 0 ? '+' : '';

            return $tickerData;
        }

        /**
         * Will cal api asking for wanted ticker then returns the result
         * @param $targets
         * @return mixed
         */
        public static function call_eod_real_time_api ($targets) {
            if(is_array($targets)){
                $target = $targets[0];
                $extraTargets = array_slice($targets,1);
            }else{
                $target = $targets;
                $extraTargets = null;
            }

            $plugin_options = get_option('eod_options');


            //Default token
            $apiKey = 'OeAFFmMliFG5orCUuwAKQ8l4WWFQ67YX';
            if(array_key_exists('api_key', $plugin_options)){
                $apiKey = $plugin_options['api_key'];
            }

            //API Endpoint
            $apiUrl = 'https://eodhistoricaldata.com/api/real-time/'.$target.'?api_token='.$apiKey.'&fmt=json';
            //Extra target management.
            if($extraTargets && count($extraTargets) > 0){
                $apiUrl .= '&s=';
                foreach($extraTargets as $i => $extraTarget){
                    $apiUrl .= $extraTarget;
                    if($i + 1 < count($extraTarget)){
                        $apiUrl .= ',';
                    }
                }
            }

            //Create request and get result
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $apiUrl);
            curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_VERBOSE, true);
            curl_setopt($ch, CURLOPT_HEADER, true);

            $response = curl_exec($ch);

            //Parse response (headers vs body)
            $header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
            $headers = substr($response, 0, $header_size);
            $body = substr($response, $header_size);
            curl_close($ch);

            //Parse json body or return error
            if(!$body || strlen(trim($body)) === 0){
                return array('error' => 'null body', 'headers'  => $headers);
            }
            if($body === "Forbidden"){
                return array('error' => 'Forbidden', 'headers'  => $headers);
            }

            try {
                $result = json_decode($body, true);
            } catch (Exception $err) {
                $result = array('error' => $body, 'exception' => $err->getMessage(), 'headers'  => $headers);
                error_log('Error getting api result : '.print_r($err,true));
            }

            return $result;
        }
    }
}

if(class_exists('EOD_Stock_Prices_Plugin')) {
    $EOD_Stock_Prices_Plugin = new EOD_Stock_Prices_Plugin();
}


