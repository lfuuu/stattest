<?php
/**
 * Печать и просмотр закрывающих документов.
 * (для клиентов, которые оплачивают доставку).
 */

use yii\helpers\Html;
use yii\widgets\Breadcrumbs;

echo app\classes\Html::formLabel($this->title);
echo Breadcrumbs::widget([
    'links' => [
        'Бухгалтерия',
        ['label' => 'Печать закрывающих документов', 'url' => '/report/closing-statements-print/?organization_id=' . $organizationId],
    ],
]);

$baseView = $this;
?>

<form action="?" id="cspForm" method="GET">
    <strong>Организация</strong><br />

    <?= Html::dropDownList(
        'organization_id',
        $organizationId,
        array_column($organizations, 'name', 'organization_id'),
        ['prompt' => 'Выберите организацию']
    ); ?>

    <br /><br />

    <input type="submit" value="Просмотр" class="button" id="reviewButton" />
    <br /><br />
    <input type="button" value="Печать" class="button" id="printButton" />
</form>

<script>
$(document).ready(function(){

    var baseUrl = '/report/closing-statements-print';

    // просмотр
    $('#reviewButton').click(function() {
        var form = $('#cspForm');

        var organizationId = form.find('[name="organization_id"]').val();
        if (organizationId === '' || organizationId === undefined) {
            return false;
        }

        form.attr('action', baseUrl + '/review');
        form.attr('target', '_self');
        form.submit();
    });

    // печать
    $('#printButton').click(function() {
        var form = $('#cspForm');

        var organizationId = form.find('[name="organization_id"]').val();
        if (organizationId === '' || organizationId === undefined) {
            return false;
        }

        var data = form.serialize();

        // акты, счета
        $('<form>', {
            'action': baseUrl + '/print',
            'method': 'get',
            'target': '_blank'
        }).append($.map(data.split('&'), function(val) {
            var [name, value] = val.split('=');
            return $('<input>', {
                'type': 'hidden',
                'name': name,
                'value': decodeURIComponent(value)
            });
        })).appendTo('body').submit().remove();

        // счета-фактуры
        $('<form>', {
            'action': baseUrl + '/print',
            'method': 'get',
            'target': '_blank'
        }).append($.map(data.split('&'), function(val) {
            var [name, value] = val.split('=');
            return $('<input>', {
                'type': 'hidden',
                'name': name,
                'value': decodeURIComponent(value)
            });
        })).append($('<input>', {
            'type': 'hidden',
            'name': 'is_landscape',
            'value': '1'
        })).appendTo('body').submit().remove();
    });

});

</script>
