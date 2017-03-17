<?php

use yii\helpers\Url;

/** @var int $troublesCount */
/** @var User $user */
?>

<div class="text-center block-user">
    <div class="menupanel text-center">
        <a href="#" class="logged-user name"><?= $user->name ?></a>

        <ul class="menu collapse">
            <li>
                <a href="#" class="logged-user name">
                    <?= $user->name ?>
                </a>
            </li>
            <li>
                <a href="/user/profile/">
                    Изменить профайл
                </a>
            </li>
            <li>
                <a href="/user/profile/change-password" class="logged-user password-change" data-width="400" data-height="450">
                    Изменить пароль
                </a>
            </li>
            <li>
                <a href="/site/logout">
                    Выход
                </a>
            </li>
        </ul>

        <?php if ($troublesCount > 0) : ?>
            <br />
            <br />
            <a href="<?= Url::toRoute(['/', 'module' => 'tt', 'action' => 'list2', 'mode' => 2]) ?>" class="troubles">
                <?= Yii::t('common', 'Tasks {count, plural, one{# entry} other{# entries}}', [
                    'count' => 2,
                ]) ?>
            </a>
        <?php endif; ?>

    </div>
</div>