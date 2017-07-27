<?php

namespace app\modules\uu\forms;

use app\classes\Form;
use app\exceptions\ModelValidationException;
use app\modules\uu\models\TariffStatus;
use InvalidArgumentException;

abstract class TariffStatusForm extends Form
{
    /** @var int ID сохраненный модели */
    public $id;

    /** @var bool */
    public $isSaved = false;

    /** @var TariffStatus */
    public $tariffStatus = false;

    /** @var string[] */
    public $validateErrors = [];

    /**
     * @return TariffStatus
     */
    abstract public function getTariffStatusModel();

    /**
     * Конструктор
     *
     * @throws \yii\db\Exception
     */
    public function init()
    {
        $this->tariffStatus = $this->getTariffStatusModel();

        // Обработать submit (создать, редактировать, удалить)
        $this->loadFromInput();
    }

    /**
     * Обработать submit (создать, редактировать, удалить)
     *
     * @throws \yii\db\Exception
     */
    protected function loadFromInput()
    {
        $post = \Yii::$app->request->post();

        // загрузить параметры от юзера
        $transaction = \Yii::$app->db->beginTransaction();
        try {
            if (isset($post['dropButton'])) {

                // удалить
                $this->tariffStatus->delete();
                $this->id = null;
                $this->isSaved = true;

            } elseif ($this->tariffStatus->load($post)) {

                if (!$this->tariffStatus->save()) {
                    $this->validateErrors += $this->tariffStatus->getFirstErrors();
                    throw new ModelValidationException($this->tariffStatus);
                }

                $this->id = $this->tariffStatus->id;
                $this->isSaved = true;

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
            \Yii::error($e);
            $this->isSaved = false;
            $this->validateErrors[] = YII_DEBUG ? $e->getMessage() : \Yii::t('common', 'Internal error');
        }
    }
}
