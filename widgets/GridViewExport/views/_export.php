<?php

use app\classes\Html;
use yii\bootstrap\ButtonDropdown;

/** @var string $uid */
/** @var [] $columns */
/** @var [] $drivers */
?>

<div class="btn-group" data-export-menu="<?= $uid ?>">
    <div class="btn-group">
        <button
            type="button"
            title="Выберите поля для экспорта"
            class="btn btn-default dropdown-toggle"
            data-toggle="dropdown"
            aria-haspopup="true"
            aria-expanded="false"
            >
            <i class="glyphicon glyphicon-list"></i>
            <span class="caret"></span>
        </button>
        <ul class="dropdown-menu dropdown-menu-right export-checkbox-list">
            <li>
                <div class="checkbox">
                    <label>
                        <?= Html::checkbox('export_gridview_columns_toggle', true) ?>
                        <?= Html::tag('span', 'Выбрать все', ['class' => 'export-toggle-all']) ?>
                    </label>
                </div>
            </li>
            <li class="divider"></li>

            <?php foreach ($columns as $column): ?>
                <li>
                    <div class="checkbox">
                        <label>
                            <?= $column ?>
                        </label>
                    </div>
                </li>
            <?php endforeach; ?>
        </ul>
    </div>

    <?= ButtonDropdown::widget([
        'label' => Html::tag('i', '', ['class' => 'glyphicon glyphicon-export']),
        'dropdown' => [
            'items' => $drivers,
            'encodeLabels' => false,
            'options' => [
                'class' => 'dropdown-menu dropdown-menu-right',
            ],
        ],
        'options' => [
            'class' => 'btn btn-default grid-export-file-format',
            'title' => 'Выберите формат файла для экспорта',
        ],
        'encodeLabel' => false,
    ]);
    ?>
</div>

<div id="<?= $uid ?>-export-dialog" class="modal fade export-dialog" role="dialog">
    <div class="modal-dialog">

        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h2 class="modal-title">Экспорт таблицы</h2>
            </div>
            <div class="modal-body">
                <div class="dialog-step" data-step="init">Загружается базовая информация</div>
                <div class="dialog-step" data-step="process">
                    <b>Подготовлено <span class="dialog-export-total">0 / 0</span></b>
                    <div class="dialog-export-progress-bar" style="width: 100%; height: 20px; border: 1px solid #F0F0F0;">
                        <div style="width: 0; height: 100%; background-color: #0F6AB4;"></div>
                    </div>
                </div>
                <div class="dialog-step" data-step="complete">
                    Файл сформирован, дождитесь начала скачивания файла браузером
                </div>
                <div class="dialog-step" data-step="error">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal" style="display: none;">Скачать</button>
            </div>
        </div>

    </div>
</div>