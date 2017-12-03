<p>
    <label for="<?php echo $_this->get_field_id('title'); ?>"><?php _e('Title:', 'eod_stock_prices'); ?></label>
    <input class="widefat" id="<?php echo $_this->get_field_id('title'); ?>"
           name="<?php echo $_this->get_field_name('title'); ?>" type="text"
           value="<?php echo esc_attr($title); ?>"/>

    <label for="<?php echo $_this->get_field_id('target'); ?>"><?php _e('Target:', 'eod_stock_prices'); ?></label>
    <input class="widefat" id="<?php echo $_this->get_field_id('target'); ?>"
           name="<?php echo $_this->get_field_name('target'); ?>" type="text"
           value="<?php echo esc_attr($target); ?>" placeholder="<?php _e('Ex: AAPL.US', 'eod_stock_prices'); ?>"/>

    <?php if(!$eod_options || !$eod_options['api_key'] || $eod_options['api_key'] == 'OeAFFmMliFG5orCUuwAKQ8l4WWFQ67YX'): ?>
    <span class="error eod_error widget_error eod_api_key_error" ><?php _e("You don't have configured a valid API key, you can only ask for AAPL.US ticker",'eod_stock_prices'); ?></span>
    <?php endif; ?>

</p>