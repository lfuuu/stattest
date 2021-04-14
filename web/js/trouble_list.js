$previousType = '';
$currentType = '';

function setState($trouble_type) {
    $previousType = $currentType;
    $currentType = $trouble_type;
    $container = $('.trouble-change-container');
    if (getTroubleIds().length > 0) {
        $container.slideDown();
    } else {
        $container.slideUp();
        $('#select2-select-state-container').text('');
        $('button:submit').prop('disabled', true);
    }
}

function disableTrouble() {
    $checkbox = $("input[name='checkAll']:checked");
    disableRest($checkbox);
    $('.select-client-checkbox').each(function () {
        if ($(this).data('trouble_type') == $checkbox.data('trouble_type')) {
            $(this).prop('checked', $checkbox.prop('checked'));
        }
    });
    disableTypes($checkbox.data('trouble_type'));
    setState($checkbox.data('trouble_type'));
    getItemsForSelect();
}

function disableRest($checkbox) {
    $.each($('.select-type'), function () {
        if ($checkbox.prop('checked')) {
            if ($(this) != $checkbox) {
                $(this).prop('disabled', true);
            } else {
                $(this).prop('disabled', false);
            }
            $checkbox.prop('disabled', false);
        } else {
            $(this).prop('disabled', false);
        }
    });
}

function disableTypes($type) {
    $checkedCount = 0;
    $selector = $('.select-client-checkbox').not('*[data-disabled]');
    $selector.each(function () {
        $hasChecked = true;
        if ($(this).data('trouble_type') !== $type) {
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
    $selector.each(function () {
        $(this).prop('disabled', false);
        $('#select-state option').remove();
    });
}

function disableCheckboxes() {
    $checkedCount = 0;
    $selector = $('.select-client-checkbox').not('*[data-disabled]');
    $selector.each(function () {
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
    $selector.each(function () {
        $(this).prop('disabled', false);
        $('#select-state option').remove();
    });
}
function getItemsForSelect() {
    $trouble_ids = getTroubleIds();
    if ($trouble_ids.length > 0) {
        return $.ajax({
            type: 'post',
            url: './?module=tt&action=get_trouble_stages',
            data: {
                trouble_ids: $trouble_ids
            },
            success: function (data) {
                if (data) {
                    insertOptions(data);
                }
            }
        });
    }
}
function insertOptions($selectItems) {
    $options = $('#select-state option');
    if ($options.length) {
        $options.remove();
    }
    $.each($selectItems, function ($index, $val) {
        $('#select-state').append($('<option/>', {
            value: $index,
            text: $val
        }));
    });
}
function getTroubleIds() {
    return $('.select-client-checkbox:checked').map(function () {
        return $(this).val();
    }).get();
}
