<?php
/**
 * Created by IntelliJ IDEA.
 * User: slabre
 * Date: 02/12/2017
 * Time: 14:12
 */

if(!class_exists('EOD_Stock_Prices_Plugin')) {
    class EOD_Stock_Prices_Widget extends WP_Widget
    {
        public static $widget_base_id = 'EOD_Stock_Prices_Widget';

        function __construct()
        {
            parent::__construct(
                self::$widget_base_id,
                __('EOD Stock Prices Ticker', 'eod-stock-prices'),
                array('description' => __('Stock Prices widget displays a ticker', 'eod-stock-prices'))
            );

            EOD_Stock_Prices_Plugin::enqueue_scripts($this->id_base);
        }

        public function widget($args, $instance)
        {
            $title = apply_filters('widget_title', $instance['title']);
            $target = json_decode($instance['target']);
            $targetList = [];
            foreach($target as $targetElement) {
                $targetList[] = array(
                    'target' => $targetElement,
                    'tickerData' => EOD_Stock_Prices_Plugin::get_real_time_ticker($targetElement)
                );
            }

            $widgetContent = EOD_Stock_Prices_Plugin::loadTemplate(
                    "widget/template/ticker_widget.php",
                    array('target' => $target, 'title' => $title, '_this' => $this, 'targetList' => $targetList, 'args' => $args)
            );
            echo $widgetContent;

        }

        // Widget Backend
        public function form($instance)
        {
            if (isset($instance['title'])) {
                $title = $instance['title'];
            }

            if (isset($instance['target'])) {
                $target = $instance['target'];
            }

            $eod_options = get_option('eod_options');
            // Widget admin form
            $widgetFormContent = EOD_Stock_Prices_Plugin::loadTemplate(
                "widget/template/ticker_widget_form.php",
                array('target' => $target,
                    'title' => $title,
                    '_this' => $this,
                    'instance' => $instance,
                    'eod_options' => $eod_options)
            );

            echo $widgetFormContent;
        }

        // Updating widget replacing old instances with new
        public function update($new_instance, $old_instance)
        {
            $instance = array();
            $instance['title'] = (!empty($new_instance['title'])) ? strip_tags($new_instance['title']) : '';
            $instance['target'] = (!empty($new_instance['target'])) ? strip_tags($new_instance['target']) : '';
            return $instance;
        }
    }
}