<?php

namespace app\classes\uu\forms;

use Yii;
use yii\db\ActiveRecord;
use yii\db\IntegrityException;

/**
 * @property string[] $validateErrors должет быть определен в родительском классе
 */
trait CrudMultipleTrait
{
    /**
     * аналог model::loadMultiple, но
     * 1. не только update, а также insert и delete
     * 2. виджет multiple-input переиндексирует массив. Был id, стал autoincrement
     * 3. возвращает данные, а не параметр по ссылке
     *
     * @param ActiveRecord[] $models
     * @param [] $data
     * @param ActiveRecord $originalModel
     */
    protected function crudMultiple($models, $data, ActiveRecord $originalModel)
    {
        $returnModels = [];

        $formName = $originalModel->formName();
        if (isset($data[$formName])) {
            /** @var string[] $dataParam */
            foreach ($data[$formName] as $i => $dataParam) {

                $id = isset($dataParam['id']) ? (int)$dataParam['id'] : 0;
                if ($id && isset($models[$id])) {
                    // update
                    $model = $models[$id];
                    unset($models[$id]);
                } else {
                    // insert
                    $model = clone $originalModel;
                }

                /** @var ActiveRecord $model */
                $model->load($dataParam, '');
                if ($model->validate()) {
                    $model->save();
                    $returnModels[$model->id] = $model;
                } else {
                    // продолжить выполнение, чтобы показать юзеру массив с недозаполненными данными вместо эталонных
                    $this->validateErrors += $model->getFirstErrors();
                    $returnModels[] = $model;
                }
            }
        }

        // delete
        foreach ($models as $model) {
            try {
                $model->delete();
            } catch (IntegrityException $e) {
                $this->validateErrors[] = Yii::t('common', 'You can not delete the object which used in another place');
            }
        }

        return $returnModels;
    }

    /**
     * аналог crudMultiple, но для мульти-select2
     *
     * @param ActiveRecord[] $models
     * @param [] $data
     * @param ActiveRecord $originalModel
     * @param string $fieldName
     */
    protected function crudMultipleSelect2($models, $data, ActiveRecord $originalModel, $fieldName)
    {
        $returnModels = [];

        $formName = $originalModel->formName();
        if (isset($data[$formName])) {
            /** @var string[] $dataParam */
            foreach ($data[$formName] as $id) {

                $id = (int) $id;
                if ($id && isset($models[$id])) {
                    // update
                    $model = $models[$id];
                    unset($models[$id]);
                } else {
                    // insert
                    $model = clone $originalModel;
                }

                /** @var ActiveRecord $model */
                $model->{$fieldName} = $id;
                if ($model->validate()) {
                    $model->save();
                    $returnModels[$model->{$fieldName}] = $model;
                } else {
                    // продолжить выполнение, чтобы показать юзеру массив с недозаполненными данными вместо эталонных
                    $this->validateErrors += $model->getFirstErrors();
                    $returnModels[] = $model;
                }
            }
        }

        // delete
        foreach ($models as $model) {
            try {
                $model->delete();
            } catch (IntegrityException $e) {
                $this->validateErrors[] = Yii::t('common', 'You can not delete the object which used in another place');
            }
        }

        return $returnModels;
    }
}
