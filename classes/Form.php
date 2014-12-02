<?php
namespace app\classes;

use Yii;
use yii\base\Model;
use yii\db\ActiveRecord;

abstract class Form extends Model
{
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
}
