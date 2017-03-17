+function ($) {
    'use strict';

    $(function () {
        var $paymentRate = $('#payment_rate'),
            $paymentSummary = $('#payment_sum'),
            $paymentType = $('#payment_type'),
            $paymentCurrency = $('#payment_original_currency'),
            $paymentOriginalSummary = $('#payment_original_sum');

        $paymentCurrency.on('change', function(){
            var originalCurrency = $paymentCurrency.val(),
                currency = $('#payment_currency').val();

            $.get('/currency/get-rate', {from: originalCurrency, to: currency}, function (data) {
                $paymentRate.val(data).trigger('change');
            })
        }).trigger('change');

        $paymentOriginalSummary.on('change', function(){
            var originalSum = $(this).val(),
                paymentRate = $paymentRate.val(),
                sum = originalSum / paymentRate;

            $paymentSummary.val(sum.toFixed(2));
        });

        $paymentSummary.on('change', function(){
            var originalSum = $paymentOriginalSummary.val(),
                sum = $(this).val(),
                paymentRate = originalSum / sum;

            $paymentRate.val(paymentRate.toFixed(4));
        });

        $paymentRate.on('change', function(){
            var originalSum = $paymentOriginalSummary.val(),
                paymentRate = $(this).val(),
                sum = originalSum / paymentRate;

            $paymentSummary.val(sum.toFixed(2));
        });

        $paymentType.on('change', function(){
            var type = $(this).val(),
                $paymentBank = $('#payment_bank'),
                $paymentECash = $('#payment_ecash');

            if (type == 'bank') {
                $paymentBank.removeAttr('disabled');
            } else {
                $paymentBank.attr('disabled','disabled');
            }

            if (type == 'ecash') {
                $paymentECash.removeAttr('disabled');
            } else {
                $paymentECash.attr('disabled','disabled');
            }
        }).trigger('change');
    })

}(jQuery);