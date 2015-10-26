<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $name string */
/* @var $message string */
/* @var $exception Exception */

$this->title = $name;
?>

<div class="col-xs-12" style="text-align: center; padding: 20px;">
    <div class="well">
        <div class="site-error">

            <h1><?= Html::encode($this->title) ?></h1>

            <div class="alert alert-danger">
                <?= nl2br(Html::encode($message)) ?>
            </div>

            <p>
                Ошибка возникла в результате обработки вашего запроса
            </p>
            <p>
                Если Вы уверены, что это ошибка произошла по нашей вине, напишите разработчикам
            </p>

        </div>
    </div>
</div>
