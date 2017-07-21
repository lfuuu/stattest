<?php

use \app\modules\uu\models_light\InvoiceLight;

$help = InvoiceLight::getHelp();
?>

<br />
<table class="table table-bordered ">
    <thead>
    <tr class="info">
        <th>Переменная</th>
        <th>Значение</th>
    </tr>
    </thead>
    <tbody>
        <?php
        /**
         * @var \app\modules\uu\models_light\InvoiceLightInterface $object
         */
        foreach($help as $object): ?>
            <tr>
                <td colspan="2"><b>$<?= $object::getKey() ?>: <?= $object::getTitle() ?></b></td>
            </tr>
            <?php foreach($object::attributeLabels() as $attribute => $attributeLabel): ?>
                <?php if(is_array($attributeLabel)): ?>
                    <?php foreach($attributeLabel as $attributeKeyLabel => $attributeLabelValue): ?>
                        <tr>
                            <td>{$<?= $object::getKey() . '.' . $attribute . '.' . $attributeKeyLabel ?>}</td>
                            <td><?= $attributeLabelValue ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td>{$<?= $object::getKey() . '.' . $attribute ?>}</td>
                        <td><?= $attributeLabel ?></td>
                    </tr>
                <?php endif; ?>
            <?php endforeach; ?>
        <?php endforeach; ?>
    </tbody>
</table>