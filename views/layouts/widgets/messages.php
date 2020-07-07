<?php if (Yii::$app->session->hasFlash('error')): ?>
    <?php
    $errorMessages = Yii::$app->session->getFlash('error', [], true);
    ?>
    <div style="font-weight: bold;" class="alert alert-danger fade in text-center">
        <?php if (is_array($errorMessages)): ?>
            <?= implode('<br />', $errorMessages); ?>
        <?php else: ?>
            <?= $errorMessages; ?>
        <?php endif; ?>
    </div>
<?php endif; ?>

<?php if (Yii::$app->session->hasFlash('warning')): ?>
    <?php
    $errorMessages = Yii::$app->session->getFlash('warning', [], true);
    ?>
    <div style="font-weight: bold;" class="alert alert-warning fade in text-center">
        <?php if (is_array($errorMessages)): ?>
            <?= implode('<br />', $errorMessages); ?>
        <?php else: ?>
            <?= $errorMessages; ?>
        <?php endif; ?>
    </div>
<?php endif; ?>

<?php if (Yii::$app->session->hasFlash('success')): ?>
    <?php
    $successMessages = Yii::$app->session->getFlash('success', [], true);
    ?>
    <div style="font-weight: bold;" class="alert alert-success fade in text-center">
        <?php if (is_array($successMessages)): ?>
            <?= implode('<br />', $successMessages); ?>
        <?php else: ?>
            <?= $successMessages; ?>
        <?php endif; ?>
    </div>
<?php endif; ?>