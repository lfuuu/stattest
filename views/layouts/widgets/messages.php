<?php if (Yii::$app->session->hasFlash('error')): ?>
    <?php
    $errorMessages = Yii::$app->session->getFlash('error', [], true);
    ?>
    <div style="text-align: center;" class="alert alert-danger fade in">
        <div style="font-weight: bold;">
            <?php if (is_array($errorMessages)): ?>
                <?= implode('<br />', $errorMessages); ?>
            <?php else: ?>
                <?= $errorMessages; ?>
            <?php endif; ?>
        </div>
    </div>
<?php endif; ?>

<?php if (Yii::$app->session->hasFlash('success')): ?>
    <?php
    $successMessages = Yii::$app->session->getFlash('success', [], true);
    ?>
    <div style="text-align: center;" class="alert alert-success fade in">
        <div style="font-weight: bold;">
            <?php if (is_array($successMessages)): ?>
                <?= implode('<br />', $successMessages); ?>
            <?php else: ?>
                <?= $successMessages; ?>
            <?php endif; ?>
        </div>
    </div>
<?php endif; ?>