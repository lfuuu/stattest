<?php

/**
 * Создание/редактирование типа
 *
 * @var \app\classes\BaseView $this
 * @var \app\modules\uu\forms\serviceTypeForm $formModel
 */

use app\modules\uu\models\ResourceModel;

$serviceTypeResources = $formModel->serviceTypeResources;

?>
<div class="well">
    <?php
        $viewParams = [
            'formModel' => $formModel,
            'form' => $form,
        ];
    ?>
    <h2>Ресурсы</h2>
    <?php 
        $resources = ResourceModel::getList($formModel->serviceType->id);
        foreach ($resources as $index => $resource) {
            foreach ($serviceTypeResources as $id => $serviceResource) {
                if ($serviceResource['resource_id'] == $index) {
                    echo $form->field($serviceResource, "[{$id}]is_active")
                    ->checkbox(['label' => $resource['name']]);
                }
            }
            
        }
    ?>
</div>
