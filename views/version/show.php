<?php
/** @var $versions array */

$links = [
    'ClientAccount' => '/account/edit?id=',
    'ClientContragent' => '/contragent/edit?id=',
    'ClientContragentPerson' => '/contragent/edit?id=',
    'ClientContract' => '/contract/edit?id=',
];
?>
<?php if (!$versions) : ?>
    Версий не найдено
<?php else : ?>

    <div class="row" style="background: #dcdcdc;">
        <div class="col-sm-2">
            Дата
        </div>
        <div class="col-sm-10">
                <div class="row">
                    <div class="col-sm-4">
                        Название аттрибута
                    </div>
                    <div class="col-sm-4">
                        Старое значение
                    </div>
                    <div class="col-sm-4">
                        Новое значение
                    </div>
                </div>
        </div>
    </div>

    <?php $last = count($versions) - 1; ?>
    <?php foreach($versions as $k => $version) : ?>
    <div class="row" style="background: <?= $k%2==0 ? '#f7f7f7' : 'white' ?>; border-top: 1px solid rgb(33, 51, 237);">
        <div class="col-sm-2">
            <a href="<?= $links[$version->model].$version->model_id.($last === $k ? '' : '&date=' . $version->date) ?>"><?= $version->date ?></a>
            <?php if($last !== $k || !$version['diffs']) : ?>
                <i class="uncheck btn-delete-version" style="cursor: pointer;" data-model="<?= $version->model ?>"
                   data-model-id="<?= $version->model_id ?>" data-date="<?= $version->date ?>"></i>
            <?php endif; ?>
        </div>
        <div class="col-sm-10">
        <?php foreach($version['diffs'] as $filed => $values) : ?>
            <div class="row" style="background: <?= $i%2==0?'rgb(104, 199, 244)': 'rgb(65, 181, 237)'?>;">
                <div class="col-sm-4">
                    <?= $models[$version->model]->getAttributeLabel($filed) ?>
                </div>
                <div class="col-sm-4">
                    <?= $values[0] ?>
                </div>
                <div class="col-sm-4">
                    <?= $values[1] ?>
                </div>
            </div>
            <?php $i++; ?>
        <?php endforeach; ?>
        </div>
    </div>
    <?php endforeach; ?>

<?php endif; ?>

<script>
    $('#history-dialog').off('click', '.btn-delete-version');
    $('#history-dialog').on('click', '.btn-delete-version', function () {
        if(confirm('Удалить версию?')){
            var t = $(this);
            var params = {
                model: t.data('model'),
                modelId: t.data('model-id'),
                date: t.data('date'),
            };
            $.getJSON('/version/delete', params, function(data){
                if(data['status'] == 'ok')
                    t.closest('.row').remove();
            })
        }
    })
</script>
