$('#business_id').change(function() {
    reloadBusinessProcesses($(this).val(), null, true);
});

$('#business_process_id').change(function() {
    reloadBusinessProcessStatuses($(this).val());
});

$(document).ready(function() {
    $businessId = 3;
    $searchParams = new URLSearchParams(window.location.search);
    $b_id = $searchParams.get('BalanceReport[b_id]');
    $bp_id = $searchParams.get('BalanceReport[bp_id]');
    $bps_id = $searchParams.get('BalanceReport[bps_id]');
    if ($b_id) {
        reloadBusinessProcesses($b_id, $bp_id);
    }
    if ($bp_id) {
        reloadBusinessProcessStatuses($bp_id, $bps_id);
    }
    if (!$b_id && !$bp_id && !$bps_id) {
        $operatorOption = $('#business_id option[value=' + $businessId + ']');
        $operatorOption.attr('selected', true);
        $('#select2-business_id-container').text($operatorOption.text());
        reloadBusinessProcesses($businessId);
    }
});

function reloadBusinessProcesses($businessId, $select = null, $trigger = false) {
    $.ajax({
        type: 'get',
        url: 'voipreport/balance-report/get-business-processes',
        data: {id: $businessId},
        success: function(s) {
            $dropdown = $('#business_process_id');
            $dropdown.empty();
            $.each(s, function (id, name) {
                $dropdown.append("<option value='" + id + "'>" + name + "</option>");
            });
            $option = $('#business_process_id ' + (($select ? 'option[value=' + $select + ']' : 'option:contains(----)') + ':first'));
            $option.attr('selected', true);
            $selectedText = $option.text();
            $('#select2-business_process_id-container').text($selectedText);
            if ($trigger) {
                $dropdown.trigger('change');
            }
        }
    });
}

function reloadBusinessProcessStatuses($businessProcessId, $select = null) {
    $.ajax({
        type: 'get',
        url: 'voipreport/balance-report/get-business-process-statuses',
        data: {id: $businessProcessId},
        success: function(s) {
            $dropdown = $('#business_process_status_id');
            $dropdown.empty();
            $.each(s, function (id, name) {
                $dropdown.append("<option value='" + id + "'>" + name + "</option>");
            });
            $option = $('#business_process_status_id ' + (($select ? 'option[value=' + $select + ']' : 'option:contains(----)') + ':first'));
            $option.attr('selected', true);
            $selectedText = $option.text();
            $('#select2-business_process_status_id-container').text($selectedText);
        }
    });
}
