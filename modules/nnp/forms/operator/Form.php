<?php

namespace app\modules\nnp\forms\operator;

use app\modules\nnp\models\Number;
use app\modules\nnp\models\NumberRange;
use app\modules\nnp\models\Operator;
use InvalidArgumentException;
use yii;
use yii\web\NotFoundHttpException;

abstract class Form extends \app\classes\Form
{
    /** @var Operator */
    public $operator;

    /**
     * @return Operator
     */
    abstract public function getOperatorModel();

    /**
     * Конструктор
     *
     * @throws \yii\web\NotFoundHttpException
     * @throws \yii\db\Exception
     */
    public function init()
    {
        $this->operator = $this->getOperatorModel();

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
        if (!$this->operator) {
            throw new NotFoundHttpException('Объект с таким ID не существует');
        }

        // загрузить параметры от юзера
        $db = Operator::getDb();
        $transaction = $db->beginTransaction();
        try {
            $post = Yii::$app->request->post();

            if (isset($post['Operator']['operator_src_code']['src_code'])) {
                $post['Operator']['operator_src_code'] = implode(',', array_filter($post['Operator']['operator_src_code']['src_code']));
            }

            // название
            if (isset($post['dropButton'])) {

                // удалить
                if (isset($post['newOperatorId']) && $post['newOperatorId']) {
                    // перемапить на новый
                    $newOperator = Operator::findOne(['id' => $post['newOperatorId']]);
                    if (!$newOperator) {
                        throw new InvalidArgumentException('Не найден оператор с ID = ' . $post['newOperatorId']);
                    }
                    $this->operator->newHistoryData = $newOperator->toArray();

                    NumberRange::updateAll(['operator_id' => $post['newOperatorId']], ['operator_id' => $this->operator->id]);
                    Number::updateAll(['operator_id' => $post['newOperatorId']], ['operator_id' => $this->operator->id]);
                }

                $this->operator->delete();
                $this->id = null;
                $this->isSaved = true;

            } elseif ($this->operator->load($post)) {

                // создать/редактировать
                if ($this->operator->validate() && $this->operator->save()) {
                        $this->id = $this->operator->id;
                        $this->isSaved = true;
                } else {
                    // продолжить выполнение, чтобы показать юзеру массив с недозаполненными данными вместо эталонных
                    $this->validateErrors += $this->operator->getFirstErrors();
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
