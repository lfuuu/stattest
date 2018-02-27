<?php

namespace app\modules\nnp\forms\numberRange;

use app\modules\nnp\models\NumberRange;
use InvalidArgumentException;
use yii;
use yii\web\NotFoundHttpException;

abstract class Form extends \app\classes\Form
{
    /** @var NumberRange */
    public $numberRange;

    /**
     * @return NumberRange
     */
    abstract public function getNumberRangeModel();

    /**
     * Конструктор
     *
     * @throws \yii\web\NotFoundHttpException
     * @throws \yii\db\Exception
     */
    public function init()
    {
        $this->numberRange = $this->getNumberRangeModel();

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
        if (!$this->numberRange) {
            throw new NotFoundHttpException('Объект с таким ID не существует');
        }

        // загрузить параметры от юзера
        $db = NumberRange::getDb();
        $transaction = $db->beginTransaction();
        try {
            $post = Yii::$app->request->post();

            if ($this->numberRange->load($post)) {

                // создать/редактировать
                if ($this->numberRange->validate() && $this->numberRange->save()) {
                    $this->id = $this->numberRange->id;
                    $this->isSaved = true;
                } else {
                    // продолжить выполнение, чтобы показать юзеру массив с недозаполненными данными вместо эталонных
                    $this->validateErrors += $this->numberRange->getFirstErrors();
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
