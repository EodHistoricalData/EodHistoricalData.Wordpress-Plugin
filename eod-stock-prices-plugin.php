<?php
/*
Plugin Name: Stock Prices plugin
Plugin URI: https://eodhistoricaldata.com/knowledgebase/plugins
Description: The stock prices plugin allows you to use a wisget and a shortcode to display the ticker data you want.
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
         * Prepare plugin hooks / filters
         */
        public function __construct(){
            /* Runs when plugin is activated */
            register_activation_hook(__FILE__,array(&$this,'activate'));
            /* Runs on plugin deactivation*/
            register_deactivation_hook( __FILE__, array(&$this,'deactivate'));

            add_action('init', array(&$this,'shortcodes_init'));
            add_action( 'widgets_init', array(&$this,'widgets_init'));
            add_action( 'wp_enqueue_scripts',  array(&$this,'enqueue_scripts'));

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
        protected function widgets_init(){
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
        public function enqueue_scripts() {
            global $post;
            if( has_shortcode( $post->post_content, 'eod_ticker')) {
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

           $result = $this->call_eod_real_time_api($tickerTarget);
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

            $tickerData = $this->call_eod_real_time_api($shortcode_atts['target']);

            $tickerData['evolution'] =  round($tickerData['open'] - $tickerData['close'],2);
            $tickerData['evolutionClass'] = $tickerData['evolution'] > 0 ? 'plus' : ($tickerData['evolution'] == 0 ? 'equal' : 'minus');
            $tickerData['evolutionSymbol'] = $tickerData['evolution'] > 0 ? '+' : '';

            return '<span class="eod_ticker '.$tickerData['evolutionClass'].'" role="eod_ticker" target="'.$shortcode_atts['target'].'"><span role="name">'.$shortcode_atts['target'].'</span><span role="close">'.$tickerData['close'].'</span>(<span role="evolution">'.$tickerData['evolutionSymbol'].$tickerData['evolution'].'</span>)</span>';
        }


        /**
         * Will cal api asking for wanted ticker then returns the result
         * @param $targets
         * @return mixed
         */
        public function call_eod_real_time_api ($targets) {
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
            $result = curl_exec($ch);
            curl_close($ch);

            //Parse result and return
            try {
                $result = json_decode($result, true);
            } catch (Exception $err) {
                error_log('Error getting api result : '.print_r($err,true));
            }

            return $result;
        }
    }
}

if(class_exists('EOD_Stock_Prices_Plugin')) {
    $EOD_Stock_Prices_Plugin = new EOD_Stock_Prices_Plugin();
}


