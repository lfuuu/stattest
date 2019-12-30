$('#trouble-roistat-channel').on('change', function() {

    var isDisabled = this.value == 0;

    // склад должен быть выбран всегда, при выборе мобильного номера
    if (!isDisabled && $('#voipNdcType').val() == 2 && !$('#voipNumbersWarehouseStatus').val()) {
        isDisabled = true;
    }

    $('#submit-button').attr('disabled', isDisabled);
});