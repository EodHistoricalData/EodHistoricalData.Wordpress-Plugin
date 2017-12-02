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

        function __construct()
        {
            parent::__construct(
                'EOD_Stock_Prices_Widget',
                __('EOD Stock Prices', 'eod-stock-prices'),
                array('description' => __('Sample widget based on WPBeginner Tutorial', 'eod-stock-prices'),)
            );
        }

        public function widget($args, $instance)
        {
            $title = apply_filters('widget_title', $instance['title']);

            // before and after widget arguments are defined by themes
            echo $args['before_widget'];
            if (!empty($title))
                echo $args['before_title'] . $title . $args['after_title'];

        // This is where you run the code and display the output
            echo __('Hello, World!', 'eod-stock-prices');
            echo $args['after_widget'];
        }

        // Widget Backend
        public function form($instance)
        {
            if (isset($instance['title'])) {
                $title = $instance['title'];
            } else {
                $title = __('New title', 'wpb_widget_domain');
            }
            // Widget admin form
            ?>
            <p>
                <label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title:'); ?></label>
                <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>"
                       name="<?php echo $this->get_field_name('title'); ?>" type="text"
                       value="<?php echo esc_attr($title); ?>"/>
            </p>
            <?php
        }

        // Updating widget replacing old instances with new
        public function update($new_instance, $old_instance)
        {
            $instance = array();
            $instance['title'] = (!empty($new_instance['title'])) ? strip_tags($new_instance['title']) : '';
            return $instance;
        }
    }
}