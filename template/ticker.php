<span class="eod_ticker <?php echo $tickerData['error'] ? 'error' : $tickerData['evolutionClass'] ?>" role="eod_ticker" target="<?php echo $target ?>">
    <span role="name"><?php echo $target ?></span><span role="close"><?php echo $tickerData['error'] ? $tickerData['error'] : $tickerData['close'] ?></span>
    <?php if(!isset($tickerData['error'])) : ?>
    <span role="evolution">(<span role="value"><?php echo $tickerData['evolutionSymbol'].$tickerData['evolution'] ?></span>)</span>
    <?php endif; ?>
</span>