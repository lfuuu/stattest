<?php if (Yii::$app->session->hasFlash('error')): ?>
    <?php foreach(Yii::$app->session->getFlash('error', [], true) as $message): ?>
        <div style="color:#ff0000; font-weight: bold"><?= $message ?></div>
    <?php endforeach; ?>
<?php endif; ?>
<?php if (Yii::$app->session->hasFlash('success')): ?>
    <?php foreach(Yii::$app->session->getFlash('success', [], true) as $message): ?>
        <div style="color:#008000; font-weight: bold"><?= $message ?></div>
    <?php endforeach; ?>
<?php endif; ?>
