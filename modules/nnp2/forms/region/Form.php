<?php

namespace app\modules\nnp2\forms\region;

use app\modules\nnp2\models\Region;
use InvalidArgumentException;
use yii;
use yii\web\NotFoundHttpException;

abstract class Form extends \app\classes\Form
{
    /** @var Region */
    public $region;

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

            if ($this->region->load($post)) {
                $this->region->parent_id = $this->region->parent_id ? : null;

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
