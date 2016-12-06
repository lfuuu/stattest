<?php

namespace app\classes\dictionary\forms;

use app\classes\Form;
use app\models\Country;
use InvalidArgumentException;
use yii;

abstract class CountryForm extends Form
{
    /** @var int ID сохраненный модели */
    public $id;

    /** @var bool */
    public $isSaved = false;

    /** @var Country */
    public $country;

    /** @var string[] */
    public $validateErrors = [];

    /**
     * @return Country
     */
    abstract public function getCountryModel();

    /**
     * конструктор
     */
    public function init()
    {
        $this->country = $this->getCountryModel();

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

            $prevInUse = $this->country->in_use;

            if ($this->country->load($post)) {

                // создать/редактировать
                if ($this->country->validate() && $this->country->save()) {
                    $this->id = $this->country->code;
                    $this->isSaved = true;

                    $this->country->refresh();

                    if (!$prevInUse && $this->country->in_use) { // страну "включили"
                        $this->setModelOrder();
                    }
                } else {
                    // продолжить выполнение, чтобы показать юзеру массив с недозаполненными данными вместо эталонных
                    $this->validateErrors += $this->country->getFirstErrors();
                }
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
            $this->validateErrors[] = YII_DEBUG ? $e->getMessage() : Yii::t('common', 'Internal error');
        }
    }

    /**
     * @return Country[]
     */
    public function getOrderedList()
    {
        return
            Country::find()
                ->orderBy([
                    'in_use' => SORT_DESC,
                    'order' => SORT_ASC,
                ])
                ->all();
    }

    /**
     * Устанавливаем порядок сортировки. Последний внизу.
     */
    protected function setModelOrder()
    {
        $max = Country::find()->where(['in_use' => 1])->max('`order`');
        $this->country->order = $max+1;
        $this->country->save();
    }

}
