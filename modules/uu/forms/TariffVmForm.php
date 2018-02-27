<?php

namespace app\modules\uu\forms;

use app\classes\Form;
use app\exceptions\ModelValidationException;
use app\modules\uu\models\TariffVm;
use InvalidArgumentException;

abstract class TariffVmForm extends Form
{
    /** @var TariffVm */
    public $tariffVm = false;

    /**
     * @return TariffVm
     */
    abstract public function getTariffVmModel();

    /**
     * Конструктор
     *
     * @throws \yii\db\Exception
     */
    public function init()
    {
        $this->tariffVm = $this->getTariffVmModel();

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
                $this->tariffVm->delete();
                $this->id = null;
                $this->isSaved = true;

            } elseif ($this->tariffVm->load($post)) {

                if (!$this->tariffVm->save()) {
                    $this->validateErrors += $this->tariffVm->getFirstErrors();
                    throw new ModelValidationException($this->tariffVm);
                }

                $this->id = $this->tariffVm->id;
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
