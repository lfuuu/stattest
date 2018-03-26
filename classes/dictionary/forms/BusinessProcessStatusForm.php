<?php

namespace app\classes\dictionary\forms;

use app\classes\Form;
use app\models\BusinessProcessStatus;
use app\models\ClientContract;
use InvalidArgumentException;
use Yii;

abstract class BusinessProcessStatusForm extends Form
{
    /** @var BusinessProcessStatus */
    public $status;

    /**
     * @return BusinessProcessStatus
     */
    abstract public function getStatusModel();

    /**
     * Конструктор
     */
    public function init()
    {
        $this->status = $this->getStatusModel();

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

                if (ClientContract::find()->where(['business_process_status_id' => $this->id])->exists()) {
                    throw new \LogicException('Удалить статус нельзя. Он используется.');
                }
                // удалить
                $this->status->delete();
                $this->id = null;
                $this->isSaved = true;

            } elseif ($this->status->load($post)) {

                // создать/редактировать
                if ($this->status->validate() && $this->status->save()) {
                    $this->id = $this->status->id;
                    $this->isSaved = true;
                } else {
                    // продолжить выполнение, чтобы показать юзеру массив с недозаполненными данными вместо эталонных
                    $this->validateErrors += $this->status->getFirstErrors();
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
