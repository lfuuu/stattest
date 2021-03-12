<?php

namespace app\classes\dictionary\forms;

use app\classes\Form;
use app\exceptions\ModelValidationException;
use app\models\PaymentApiChannel;
use app\models\Region;
use InvalidArgumentException;
use Yii;

abstract class PaymentApiChannelForm extends Form
{
    public $isCodeUsed = false;

    /** @var PaymentApiChannel */
    public PaymentApiChannel $model;

    /**
     * @return PaymentApiChannel
     */
    abstract public function getFormModel();

    /**
     * Конструктор
     */
    public function init()
    {
        $this->model = $this->getFormModel();

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

                if ($this->model->isUsedCode()) {
                    throw new \LogicException('Канал не может быть удален. Имеются платежи.');
                }
                // удалить
                $this->model->delete();
                $this->id = null;
                $this->isSaved = true;

            } elseif ($this->model->load($post)) {
                // создать/редактировать
                if ($this->model->validate() && $this->model->save()) {
                    $this->id = $this->model->id;
                    $this->isSaved = true;
                } else {
                    // продолжить выполнение, чтобы показать юзеру массив с недозаполненными данными вместо эталонных
                    $this->validateErrors += $this->model->getFirstErrors();
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
