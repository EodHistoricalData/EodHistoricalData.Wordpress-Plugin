jQuery(document).ready(function($){
    console.log('EodStockPrices JS Loaded');
    function refreshTickers(){
        $('[role="eod_ticker"]').each(function(){
            var spanEl = $(this);
            getTickerInfo($(this).attr('target')).done(function(result){
                var data = JSON.parse(result);
                var evolution = Math.round((data.open - data.close)*100)/100;
                var evolutionSymbol = evolution > 0 ? '+' : '';
                var evolutionClass = evolution > 0 ? 'plus' : (evolution == 0 ? 'equal' : 'minus');

                spanEl.removeClass('plus').removeClass('minus').removeClass('equal');

                spanEl.find('[role="close"]').text(data.close);
                spanEl.find('[role="evolution"]').text(evolutionSymbol+evolution);

                spanEl.addClass(evolutionClass);
            });
        });
    }

    function getTickerInfo(target) {
        var data = {
            'action': "eod_stock_prices_refresh",
            'target': target,
        }

        return $.get(eod_params.ajaxurl, data);
    }

    // Refresh ticker every minute
    setInterval(function(){
        refreshTickers();
    }, 60000);
});