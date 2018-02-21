jQuery(document).ready(function($) {
    console.log("EOD Widget Form JS loaded");

    function getWidgetFromButton(buttonElement){
        var widget = buttonElement.parents('.eod_widget_form').first();
        if(typeof widget.eod_tickers_elements == "undefined" || !widget.eod_tickers_elements){
            var inputValue = [];
            if(widget.find('input#'+widget.attr('target')).val().length > 0){
                try{
                    inputValue = JSON.parse(widget.find('input#'+widget.attr('target')).val());
                }catch(err){
                    inputValue = [widget.find('input#'+widget.attr('target')).val()];
                }
            }

            widget.eod_tickers_elements = inputValue;
        }
        return widget;
    }

    $('body').on('click', '.eod_widget_form .eod_ticker_list .remove_ticker_input', function(){
        event.preventDefault();
        var widget = getWidgetFromButton($(this));
        $(this).parent('.eod_ticker_input_container').remove();
        var toDelete = $(this).parent('.eod_ticker_input_container').find('.eod_ticker_input').text();
        widget.eod_tickers_elements.splice(widget.eod_tickers_elements.indexOf(toDelete),1);
        widget.find('input#'+widget.attr('target')).val(JSON.stringify(widget.eod_tickers_elements));
        return false;
    });

    $('body').on('click', '.eod_widget_form .eod_ticker_list .add_ticker_input', function() {
        event.preventDefault();
        var widget = getWidgetFromButton($(this));
        var targetInput = $(this).siblings('.eod_add_ticker_input');
        var newTarget = targetInput.val();
        if(newTarget.length == 0){
            return false;
        }
        widget.eod_tickers_elements.push(newTarget);
        $('<li class="eod_ticker_input_container"> <span class="widefat eod_ticker_input" type="text">' + newTarget + '</span> <a class="remove_ticker_input" href="#"> - </a> </li>').insertBefore($(this).parent('.eod_add_ticker_input_container'))
        widget.find('input#'+widget.attr('target')).val(JSON.stringify(widget.eod_tickers_elements));
        targetInput.val('');
        return false;
    });
});