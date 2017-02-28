<?php

namespace app\classes\uu\forms;

use app\classes\Form;
use app\classes\uu\model\ServiceType;
use app\exceptions\ModelValidationException;
use InvalidArgumentException;

abstract class ServiceTypeForm extends Form
{
    /** @var int ID сохраненный модели */
    public $id;

    /** @var bool */
    public $isSaved = false;

    /** @var ServiceType */
    public $serviceType = false;

    /** @var string[] */
    public $validateErrors = [];

    /**
     * @return ServiceType
     */
    abstract public function getServiceTypeModel();

    /**
     * Конструктор
     */
    public function init()
    {
        $this->serviceType = $this->getServiceTypeModel();

        // Обработать submit (создать, редактировать, удалить)
        $this->loadFromInput();
    }

    /**
     * Обработать submit (создать, редактировать, удалить)
     */
    protected function loadFromInput()
    {
        $post = \Yii::$app->request->post();

        // загрузить параметры от юзера
        $transaction = \Yii::$app->db->beginTransaction();
        try {
            if (isset($post['dropButton'])) {

                // удалить
                $this->serviceType->delete();
                $this->id = null;
                $this->isSaved = true;

            } elseif ($this->serviceType->load($post)) {

                if (!$this->serviceType->save()) {
                    $this->validateErrors += $this->serviceType->getFirstErrors();
                    throw new ModelValidationException($this->serviceType);
                }

                $this->id = $this->serviceType->id;
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
