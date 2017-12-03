<div class="wrap">
    <h1><?php _e('Stock Prices Configuration', 'eod_stock_prices'); ?></h1>
    <form method="post" action="options.php">
        <?php settings_fields( 'eod_options' ); ?>
        <?php do_settings_sections( 'eod_options_section' ); ?>
        <?php submit_button(); ?>
    </form>
</div>