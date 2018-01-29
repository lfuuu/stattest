<?php

namespace app\modules\nnp\forms\region;

use app\modules\nnp\models\City;
use app\modules\nnp\models\Number;
use app\modules\nnp\models\NumberRange;
use app\modules\nnp\models\Region;
use InvalidArgumentException;
use yii;
use yii\web\NotFoundHttpException;

abstract class Form extends \app\classes\Form
{
    /** @var int ID сохраненный модели */
    public $id;

    /** @var bool */
    public $isSaved = false;

    /** @var Region */
    public $region;

    /** @var string[] */
    public $validateErrors = [];

    /**
     * @return Region
     */
    abstract public function getRegionModel();

    /**
     * Конструктор
     *
     * @throws \yii\web\NotFoundHttpException
     * @throws \yii\db\Exception
     */
    public function init()
    {
        $this->region = $this->getRegionModel();

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
        if (!$this->region) {
            throw new NotFoundHttpException('Объект с таким ID не существует');
        }

        // загрузить параметры от юзера
        $db = Region::getDb();
        $transaction = $db->beginTransaction();
        try {
            $post = Yii::$app->request->post();

            // название
            if (isset($post['dropButton'])) {

                // удалить
                if (isset($post['newRegionId']) && $post['newRegionId']) {
                    // перемапить на новый
                    NumberRange::updateAll(['region_id' => $post['newRegionId']], ['region_id' => $this->region->id]);
                    Number::updateAll(['region_id' => $post['newRegionId']], ['region_id' => $this->region->id]);
                    City::updateAll(['region_id' => $post['newRegionId']], ['region_id' => $this->region->id]);
                }

                $this->region->delete();
                $this->id = null;
                $this->isSaved = true;

            } elseif ($this->region->load($post)) {

                // создать/редактировать
                if ($this->region->validate() && $this->region->save()) {
                    $this->id = $this->region->id;
                    $this->isSaved = true;
                } else {
                    // продолжить выполнение, чтобы показать юзеру массив с недозаполненными данными вместо эталонных
                    $this->validateErrors += $this->region->getFirstErrors();
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
