<?php echo $args['before_widget']; ?>
<?php if (!empty($title)): ?>
    <?php echo $args['before_title'] . $title . $args['after_title']; ?>
<?php endif; ?>
<ul class="eod_widget_ticker_list">
<?php foreach($targetList as $i => $targetElement):  ?>
    <li>
    <?php echo EOD_Stock_Prices_Plugin::loadTemplate('template/ticker.php', array('tickerData' => $targetElement['tickerData'], 'target' => $targetElement['target'])); ?>
    </li>
<?php endforeach; ?>
</ul>
<?php echo $args['after_widget']; ?>