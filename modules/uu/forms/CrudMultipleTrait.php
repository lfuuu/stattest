<?php

namespace app\modules\uu\forms;

use app\classes\model\ActiveRecord;
use Yii;
use yii\db\IntegrityException;

trait CrudMultipleTrait
{
    /** @var string[] */
    public $validateErrors = [];

    /**
     * Аналог model::loadMultiple, но
     * 1. не только update, а также insert и delete
     * 2. виджет multiple-input переиндексирует массив. Был id, стал autoincrement
     * 3. возвращает данные, а не параметр по ссылке
     *
     * @param ActiveRecord[] $models
     * @param array $data
     * @param ActiveRecord $originalModel
     * @return ActiveRecord[]
     */
    protected function crudMultiple($models, $data, ActiveRecord $originalModel)
    {
        $returnModels = [];

        $primaryKeyNames = $originalModel::primaryKey();
        $primaryKeyName = reset($primaryKeyNames);

        $formName = $originalModel->formName();
        if (isset($data[$formName])) {
            /** @var string[] $dataParam */
            foreach ($data[$formName] as $dataParam) {

                /** @var ActiveRecord $model */
                $primaryKeyValue = isset($dataParam[$primaryKeyName]) ? (int)$dataParam[$primaryKeyName] : 0;
                if (
                    $primaryKeyValue
                    && isset($models[$primaryKeyValue])
                    && ($model = $models[$primaryKeyValue])
                    && $model->getPrimaryKey() == $primaryKeyValue
                ) {
                    // update
                    unset($models[$primaryKeyValue]);
                } else {
                    // insert
                    $model = clone $originalModel;
                }

                $model->load($dataParam, '');
                if ($model->save()) {
                    $returnModels[$model->getPrimaryKey()] = $model;
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
                $model->getPrimaryKey() && $model->delete();
            } catch (IntegrityException $e) {
                $this->validateErrors[] = Yii::t('common', 'You can not delete the object which used in another place');
            }
        }

        return $returnModels;
    }

    /**
     * Аналог crudMultiple, но для мульти-select2
     *
     * @param ActiveRecord[] $models
     * @param array $data
     * @param ActiveRecord $originalModel
     * @param string $fieldName
     * @param string $formName
     * @return ActiveRecord[]
     */
    protected function crudMultipleSelect2($models, $data, ActiveRecord $originalModel, $fieldName, $formName = null, $isIdInt = true)
    {
        $returnModels = [];

        !$formName && $formName = $originalModel->formName();

        if (isset($data[$formName])) {
            /** @var string[] $dataParam */
            foreach ($data[$formName] as $id) {

                if ($isIdInt) {
                    $id = (int)$id;
                }
                if ($id && isset($models[$id]) && ($model = $models[$id]) && $model->{$fieldName} == $id) {
                    // update
                    unset($models[$id]);
                } else {
                    // insert
                    $model = clone $originalModel;
                }

                /** @var ActiveRecord $model */
                $model->{$fieldName} = $id;
                if ($model->validate() && $model->save()) {
                    $returnModels[$id] = $model;
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
                $model->{$fieldName} && $model->delete();
            } catch (IntegrityException $e) {
                $this->validateErrors[] = Yii::t('common', 'You can not delete the object which used in another place');
            }
        }

        return $returnModels;
    }

    /**
     * Удаляем пустые модели
     *
     * @param $post
     * @param $modelName
     */
    public function clearEmpty(&$post, $modelName)
    {
        if (isset($post[$modelName])) {
            $post[$modelName] = array_filter(
                $post[$modelName],
                function ($v) {
                    foreach ($v as $k => $v) {
                        if ($v) {
                            return true;
                        }
                    }
                    return false;
                });
        }


        if (empty($post[$modelName])) {
            unset($post[$modelName]);
        };
    }
}
