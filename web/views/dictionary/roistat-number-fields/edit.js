function fillInputs($number, $fields) {
    if ($number) {
        $('#number').val($number);
    }
    if (!$fields) {
        return;
    }
    fillInputValues($fields);
}

function addInputFields() {
    $('.fields-container').first().clone().appendTo('.additional_fields').find('input').val('');
}

function prepareInputNames() {
    $('.fields-container').each(function($index) {
        $key = $(this).find('input').eq(0).val();
        if (!$key.trim()) {
            return;
        }
        $(this).find('input').eq(1).attr('name', 'fields_arr[' + $key + ']');
    });
}

function fillInputValues($fields) {
    $fieldsCount = Object.keys($fields).length;
    for ($i = 1; $i < $fieldsCount; ++$i) {
        addInputFields();
    }
    $counter = 0;
    $containers = $('.fields-container');
    $.each($fields, function($key, $val) {
        $($containers.eq($counter)).find('input').eq(0).val($key);
        $($containers.eq($counter)).find('input').eq(1).val($val);
        ++$counter;
    });
}