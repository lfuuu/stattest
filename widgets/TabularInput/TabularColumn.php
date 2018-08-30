<?php

namespace app\widgets\TabularInput;


use yii\db\ActiveRecord;

class TabularColumn extends \unclead\multipleinput\TabularColumn
{
    /**
     * @inheritdoc
     */
    public function init()
    {
        $this->enableError = true;
        parent::init();
    }

    /**
     * @param ActiveRecord $model
     */
    public function setModel($model)
    {
        $currentModel = $this->getModel();

        // If model is null and current model is not empty it means that widget renders a template
        // In this case we have to unset all model attributes
        if ($model === null && $currentModel !== null) {

            // Создать новую модель, а не клонировать старую.
            // Ибо чистка атрибутов не помогает избавиться от прочего мусора.
            $className = $currentModel::className();
            /** @var ActiveRecord $model */
            $model = new $className;

            // Проинциализировать дефолтные значения из rules.
            // Наверняка, будет ошибка валидации (ибо какие-то поля не заполнены), но это не важно
            $model->validate();
        }

        parent::setModel($model);
    }

}