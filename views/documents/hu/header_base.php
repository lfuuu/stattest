<?php
/** @var $document app\classes\documents\DocumentReport */
?>

<p>
    <?php foreach ($document->getCompany() as $key => $value): ?>
        <?php if ($key == 'logo' || $key == 'site') continue; ?>
        <?= $value; ?><br />
    <?php endforeach; ?>
</p>