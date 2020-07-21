<?php
/**
 * Выбрать ранее загруженные csv
 *
 * @var app\classes\BaseView $this
 * @var Country $country
 */

use app\models\User;
use app\modules\nnp\models\Country;
use yii\helpers\Url;
use app\classes\Html;

$files = $country->getMediaManager()->getFiles();

if ($files) :
    ?>

    <h2>Выбрать из ранее загруженных файлов по стране <u><?=$country->name_rus?></u> <?=
        Html::tag(
            'div', '',
            [
                'title' => $country->name,
                'class' => 'flag flag-' . $country->getFlagCode(),
                'style' => 'outline: 1px solid #e3e3e3',
            ]
        )
        ?></h2>
    <div class="well">
        <table class="table">
            <?php
            foreach ($files as $file) :
                $author = User::findOne(['id' => $file['author']]);
                ?>

                <tr>
                    <td><?= $file['name'] ?></td>
                    <td>
                        <?= $this->render('//layouts/_actionDrop', [
                            'url' => Url::to(['/nnp/import/unlink', 'countryCode' => $country->code, 'fileId' => $file['id']]),
                        ]) ?>

                        <?= $this->render('//layouts/_buttonLink', [
                            'url' => Url::to(['/nnp/import/download', 'countryCode' => $country->code, 'fileId' => $file['id']]),
                            'text' => '',
                            'title' => 'Скачать',
                            'glyphicon' => 'glyphicon-download',
                            'class' => 'btn-default btn-xs',
                        ]) ?>

                        <?= $this->render('//layouts/_buttonLink', [
                            'url' => Url::to(['/nnp/import/step3', 'countryCode' => $country->code, 'fileId' => $file['id']]),
                            'text' => '',
                            'title' => 'Предпросмотр',
                            'glyphicon' => 'glyphicon-step-forward',
                            'class' => 'btn-success btn-xs',
                        ]) ?>

                    </td>
                    <td><?= (int)($file['size'] / 1024) ?> Кб</td>
                    <td><?= $author ? $author->getLink() : '' ?></td>
                    <td><?= $file['created'] ?></td>
                </tr>

            <?php endforeach ?>
        </table>
    </div>
    <?php

endif;