<div id="eod_widget_<?php echo $_this->get_field_id('target') ?>" class="eod_widget_form" target="<?php echo $_this->get_field_id('target') ?>">
    <label for="<?php echo $_this->get_field_id('title'); ?>"><?php _e('Title:', 'eod_stock_prices'); ?></label>
    <input class="widefat" id="<?php echo $_this->get_field_id('title'); ?>"
           name="<?php echo $_this->get_field_name('title'); ?>" type="text"
           value="<?php echo esc_attr($title); ?>"/>

    <label for="<?php echo $_this->get_field_id('target'); ?>"><?php _e('Target(s):', 'eod_stock_prices'); ?></label>

    <ul class="eod_ticker_list">
        <?php $targetList = json_decode($target); ?>
        <?php if(count($targetList)) : ?>
            <?php foreach($targetList as $i => $targetElement) : ?>
                <li class="eod_ticker_input_container">
                    <span class="widefat eod_ticker_input" type="text"><?php echo $targetElement; ?></span>
                    <a class="remove_ticker_input" href="#"> - </a>
                </li>
            <?php endforeach; ?>
        <?php endif; ?>
        <li class="eod_add_ticker_input_container">
            <input class="eod_add_ticker_input" type="text"
                  placeholder="<?php _e('Ex: AAPL.US', 'eod_stock_prices'); ?>"/>
            <a class="add_ticker_input" href="#"> + </a>
        </li>
    </ul>

    <input type="hidden" id="<?php echo $_this->get_field_id('target'); ?>"
           name="<?php echo $_this->get_field_name('target'); ?>"
           value="<?php echo esc_attr($target); ?>" />


    <?php if(!$eod_options || !$eod_options['api_key'] || $eod_options['api_key'] == 'OeAFFmMliFG5orCUuwAKQ8l4WWFQ67YX'): ?>
    <span class="error eod_error widget_error eod_api_key_error" ><?php _e("You don't have configured a valid API key, you can only ask for AAPL.US ticker",'eod_stock_prices'); ?></span>
    <?php endif; ?>
</div>