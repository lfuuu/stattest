<?php

namespace app\modules\nnp\forms\city;

use app\modules\nnp\models\City;
use InvalidArgumentException;
use yii;

abstract class Form extends \app\classes\Form
{
    /** @var int ID сохраненный модели */
    public $id;

    /** @var bool */
    public $isSaved = false;

    /** @var City */
    public $city;

    /** @var string[] */
    public $validateErrors = [];

    /**
     * @return City
     */
    abstract public function getCityModel();

    /**
     * Конструктор
     */
    public function init()
    {
        $this->city = $this->getCityModel();

        // Обработать submit (создать, редактировать, удалить)
        $this->loadFromInput();
    }

    /**
     * Обработать submit (создать, редактировать, удалить)
     */
    protected function loadFromInput()
    {
        // загрузить параметры от юзера
        $db = City::getDb();
        $transaction = $db->beginTransaction();
        try {
            $post = Yii::$app->request->post();

            // название
            if (isset($post['dropButton'])) {

                // удалить
                $this->city->delete();
                $this->id = null;
                $this->isSaved = true;

            } elseif ($this->city->load($post)) {

                // создать/редактировать
                if ($this->city->validate() && $this->city->save()) {
                    $this->id = $this->city->id;
                    $this->isSaved = true;
                } else {
                    // продолжить выполнение, чтобы показать юзеру массив с недозаполненными данными вместо эталонных
                    $this->validateErrors += $this->city->getFirstErrors();
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
