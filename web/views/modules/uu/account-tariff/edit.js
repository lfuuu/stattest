$('#trouble-roistat-channel').on('change', function() {
    $('#submit-button').attr('disabled', this.value == 0);
});