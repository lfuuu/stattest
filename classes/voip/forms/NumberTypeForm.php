<?php

namespace app\classes\voip\forms;

use app\classes\Form;
use app\modules\uu\forms\CrudMultipleTrait;
use app\models\NumberType;
use app\models\NumberTypeCountry;
use InvalidArgumentException;
use yii;

abstract class NumberTypeForm extends Form
{
    use CrudMultipleTrait;

    /** @var int ID сохраненный модели */
    public $id;

    /** @var bool */
    public $isSaved = false;

    /** @var NumberType */
    public $numberType;

    /**
     * @return NumberType
     */
    abstract public function getNumberTypeModel();

    /**
     * Конструктор
     */
    public function init()
    {
        $this->numberType = $this->getNumberTypeModel();

        // Обработать submit (создать, редактировать, удалить)
        $this->loadFromInput();
    }

    /**
     * Обработать submit (создать, редактировать, удалить)
     */
    protected function loadFromInput()
    {
        // загрузить параметры от юзера
        $transaction = \Yii::$app->db->beginTransaction();
        try {
            $post = Yii::$app->request->post();

            // название
            if (isset($post['dropButton'])) {

                // удалить. Страны удалятся каскадно
                $this->numberType->delete();
                $this->id = null;
                $this->isSaved = true;

            } elseif ($this->numberType->load($post)) {

                // создать/редактировать
                if ($this->numberType->validate()) {
                    $this->numberType->save();
                    $this->id = $this->numberType->id;
                    $this->isSaved = true;
                } else {
                    // продолжить выполнение, чтобы показать юзеру массив с недозаполненными данными вместо эталонных
                    $this->validateErrors += $this->numberType->getFirstErrors();
                }

                // страны
                $numberTypeCountry = new NumberTypeCountry();
                $numberTypeCountry->voip_number_type_id = $this->id;
                $this->crudMultipleSelect2($this->numberType->numberTypeCountries, $post, $numberTypeCountry,
                    'country_id');

            }

            if ($this->validateErrors) {
                throw new InvalidArgumentException();
            }

            $transaction->commit();

        } catch (InvalidArgumentException $e) {
            $transaction->rollBack();
            $this->isSaved = false;

        } catch (\Exception $e) {
            $transaction->rollBack();
            Yii::error($e);
            $this->isSaved = false;

            if (!count($this->validateErrors)) {
                $this->validateErrors[] = YII_DEBUG ? $e->getMessage() : Yii::t('common', 'Internal error');
            }
        }
    }
}
