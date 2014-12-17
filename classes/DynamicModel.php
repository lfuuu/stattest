<?php
namespace app\classes;

use yii\base\InvalidConfigException;
use yii\validators\Validator;

class DynamicModel extends \yii\base\DynamicModel
{
    /**
     * Validates the given data with the specified validation rules.
     * This method will create a DynamicModel instance, populate it with the data to be validated,
     * create the specified validation rules, and then validate the data using these rules.
     * @param array $data the data (name-value pairs) to be validated
     * @param array $rules the validation rules. Please refer to [[Model::rules()]] on the format of this parameter.
     * @return static the model instance that contains the data being validated
     * @throws InvalidConfigException if a validation rule is not specified correctly.
     */
    public static function validateData(array $data, $rules = [])
    {
        /* @var $model DynamicModel */
        $model = new static();

        if (!empty($rules)) {
            $validators = $model->getValidators();
            foreach ($rules as $rule) {
                if ($rule instanceof Validator) {
                    foreach($rule->attributes as $attribute) {
                        $model->defineAttribute($attribute);
                    }
                    $validators->append($rule);
                } elseif (is_array($rule) && isset($rule[0], $rule[1])) { // attributes, validator type
                    $validator = Validator::createValidator($rule[1], $model, (array) $rule[0], array_slice($rule, 2));
                    foreach($validator->attributes as $attribute) {
                        $model->defineAttribute($attribute);
                    }
                    $validators->append($validator);
                } else {
                    throw new InvalidConfigException('Invalid validation rule: a rule must specify both attribute names and validator type.');
                }
            }
        }

        $model->setAttributes($data);

        $model->validate();

        return $model;
    }
}
