<?php

use app\classes\Html;

/* @var $this app\classes\BaseView */
/* @var $name string */
/* @var $message string */
/* @var $exception Exception */

$this->title = $name;
?>

<div class="col-sm-12" style="text-align: center; padding: 20px;">
    <div class="well">
        <div class="site-error">

            <h1><?= Html::encode($this->title) ?></h1>
            <?php 
                $start = strpos($exception, ' ');
                $end = strpos($exception, 'Stack trace');
                echo '<p>'. Html::encode(substr($exception, $start, $end - $start)) . '</p>';
            ?>
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
