jQuery(document).ready(function() {

    var $isExternal = $('[name*="is_external"]')
        $contractNo = $('.unchecked-contract-no'),
        $notExternalBlocks = $('[data-not-external]'),
        $isExternalAction = function(element) {
            var checked = $(this).is(':checked');

            if (checked === true) {
                $notExternalBlocks.hide();
                $contractNo.prop('readonly', false);
            }
            else {
                $notExternalBlocks.show();
                $contractNo.prop('readonly', true);
            }
        };

    $isExternal
        .on('input change', $isExternalAction)
        .trigger('input');

});