<?php

namespace app\modules\nnp\forms\city;

use app\modules\nnp\models\City;
use app\modules\nnp\models\Number;
use app\modules\nnp\models\NumberRange;
use InvalidArgumentException;
use yii;
use yii\web\NotFoundHttpException;

abstract class Form extends \app\classes\Form
{
    /** @var City */
    public $city;

    /**
     * @return City
     */
    abstract public function getCityModel();

    /**
     * Конструктор
     *
     * @throws \yii\web\NotFoundHttpException
     * @throws \yii\db\Exception
     */
    public function init()
    {
        $this->city = $this->getCityModel();

        // Обработать submit (создать, редактировать, удалить)
        $this->loadFromInput();
    }

    /**
     * Обработать submit (создать, редактировать, удалить)
     *
     * @throws NotFoundHttpException
     * @throws yii\db\Exception
     */
    protected function loadFromInput()
    {
        if (!$this->city) {
            throw new NotFoundHttpException('Объект с таким ID не существует');
        }

        // загрузить параметры от юзера
        $db = City::getDb();
        $transaction = $db->beginTransaction();
        try {
            $post = Yii::$app->request->post();

            // название
            if (isset($post['dropButton'])) {

                if (isset($post['newCityId']) && $post['newCityId']) {
                    // перемапить на новый
                    $newCity = City::findOne(['id' => $post['newCityId']]);
                    if (!$newCity) {
                        throw new InvalidArgumentException('Не найден город с ID = ' . $post['newCityId']);
                    }
                    $this->city->newHistoryData = $newCity->toArray();

                    NumberRange::updateAll(['city_id' => $post['newCityId']], ['city_id' => $this->city->id]);
                    Number::updateAll(['city_id' => $post['newCityId']], ['city_id' => $this->city->id]);
                }

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

            if (!count($this->validateErrors)) {
                $this->validateErrors[] = YII_DEBUG ? $e->getMessage() : Yii::t('common', 'Internal error');
            }
        }
    }
}
