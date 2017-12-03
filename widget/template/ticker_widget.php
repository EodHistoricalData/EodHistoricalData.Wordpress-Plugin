<?php echo $args['before_widget']; ?>
<?php if (!empty($title)) echo $args['before_title'] . $title . $args['after_title']; ?>
<?php echo EOD_Stock_Prices_Plugin::loadTemplate('template/ticker.php', array('tickerData' => $tickerData, 'target' => $target)); ?>
<?php echo $args['after_widget']; ?>