$previousType = '';
$currentType = '';

function setState($trouble_type) {
    $previousType = $currentType;
    $currentType = $trouble_type;
}
function disableCheckboxes() {
    $checkedCount = 0;
    $selector = $('.select-client-checkbox');
    $selector.each(function() {
        $hasChecked = true;
        if ($(this).data('trouble_type') != $currentType) {
            $(this).prop('checked', false);
            $(this).prop('disabled', true);
        }
        if (this.checked) {
            ++$checkedCount;
        }
    });
    if ($checkedCount != 0) {
        return;
    }
    $selector.each(function() {
        $(this).prop('disabled', false);
        $('#select-state option').remove();
    });
}
function getItemsForSelect() {
    return $.ajax({
        type: 'post',
        url: './?module=tt&action=get_trouble_stages',
        data: {
            trouble_ids: $('.select-client-checkbox:checked').map(function(){
                return $(this).val();
            }).get()
        },
        dataType: 'json',
        success: function(data){
            if (data) {
                insertOptions(data);
            }
        }
    });
}
function insertOptions($selectItems) {
    $options = $('#select-state option');
    if ($options.length) {
        $options.remove();
    }
    $.each($selectItems, function ($index, $val) {
        $('#select-state').append($('<option/>', {
            value: $index,
            text : $val
        }));
    });
}
