<?php

namespace app\classes\dictionary\forms;

use app\classes\Form;
use app\classes\uu\forms\CrudMultipleTrait;
use app\models\Country;
use app\models\CountryCountry;
use InvalidArgumentException;
use yii;

abstract class CountryForm extends Form
{
    use CrudMultipleTrait;

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

            // название
            if (isset($post['dropButton'])) {

                // удалить. Страны удалятся каскадно
                $this->country->delete();
                $this->id = null;
                $this->isSaved = true;

            } elseif ($this->country->load($post)) {

                // создать/редактировать
                if ($this->country->validate()) {
                    $this->country->save();
                    $this->id = $this->country->code;
                    $this->isSaved = true;
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
}
