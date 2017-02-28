<?php

namespace app\modules\nnp\forms\operator;

use app\modules\nnp\models\Operator;
use InvalidArgumentException;
use yii;

abstract class Form extends \app\classes\Form
{
    /** @var int ID сохраненный модели */
    public $id;

    /** @var bool */
    public $isSaved = false;

    /** @var Operator */
    public $operator;

    /** @var string[] */
    public $validateErrors = [];

    /**
     * @return Operator
     */
    abstract public function getOperatorModel();

    /**
     * Конструктор
     */
    public function init()
    {
        $this->operator = $this->getOperatorModel();

        // Обработать submit (создать, редактировать, удалить)
        $this->loadFromInput();
    }

    /**
     * Обработать submit (создать, редактировать, удалить)
     */
    protected function loadFromInput()
    {
        // загрузить параметры от юзера
        $db = Operator::getDb();
        $transaction = $db->beginTransaction();
        try {
            $post = Yii::$app->request->post();

            // название
            if (isset($post['dropButton'])) {

                // удалить
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
            $this->validateErrors[] = YII_DEBUG ? $e->getMessage() : Yii::t('common', 'Internal error');
        }
    }
}
