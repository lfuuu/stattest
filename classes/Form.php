<?php
namespace app\classes;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;
use yii\db\Query;

abstract class Form extends Model
{

    const PAGE_SIZE = 200;
    const EVENT_AFTER_SAVE = 'afterSave';

    public function saveModel(ActiveRecord $model, $runValidation = true)
    {
        if (!$model->save($runValidation)) {
            foreach ($model->getErrors() as $attribute => $errors) {
                foreach($errors as $error) {
                    $this->addError($attribute, $error);
                }
            }
            return false;
        }

        return true;
    }

    /**
     * @return ActiveDataProvider
     */
    public function spawnDataProvider()
    {
        $query = $this->spawnQuery();

        if ($this->validate()) {
            $this->applyFilter($query);
        }

        return new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pageSize' => self::PAGE_SIZE,
            ],
        ]);
    }

    /**
     * @return ActiveQuery
     */
    public function spawnFilteredQuery()
    {
        $query = $this->spawnQuery();
        $this->applyFilter($query);
        return $query;
    }

    public function applyFilter(Query $query)
    {

    }

    /**
     * @return ActiveQuery
     */
    public function spawnQuery() {

    }

    /**
     * @return bool
     */
    public function load($data, $formName = null)
    {
        $result = parent::load($data, $formName);
        $this->preProcess();
        return $result;
    }

    protected function preProcess()
    {

    }
}
