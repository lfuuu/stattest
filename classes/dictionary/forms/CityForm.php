<?php

namespace app\classes\dictionary\forms;

use app\classes\Form;
use app\models\City;
use InvalidArgumentException;
use Yii;

abstract class CityForm extends Form
{
    /** @var City */
    public $city;

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
        $transaction = Yii::$app->db->beginTransaction();
        try {
            $post = Yii::$app->request->post();

            // название
            if (isset($post['dropButton'])) {

                // удалить
                $this->city->delete();
                $this->id = null;
                $this->isSaved = true;

            } elseif ($this->city->load($post)) {

                $this->city->name_translit = \app\modules\nnp\models\City::find()->where(['id' => $this->city->id])->select('name_translit')->scalar();

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

            if (!count($this->validateErrors)) {
                $this->validateErrors[] = YII_DEBUG ? $e->getMessage() : Yii::t('common', 'Internal error');
            }
        }
    }
}
