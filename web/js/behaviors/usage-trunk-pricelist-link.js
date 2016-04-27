jQuery(document).ready(function () {

    $('select.pricelist_with_link').on('change', function(e) {

        var $pricelistElem = $(e.target);
        var $link = $('#link_for_pricelist' + $pricelistElem.data('setting-id'));

        var pricelistId = $pricelistElem.val();
        if (pricelistId) {
            $link
                .attr('href', '/index.php?module=voipnew&action=defs&pricelist=' + pricelistId)
                .show();
        } else {
            $link.hide();
        }
    }).trigger('change');

});
