<?php

namespace app\modules\nnp\forms\ndcType;

use app\modules\nnp\models\NdcType;
use InvalidArgumentException;
use yii;

abstract class Form extends \app\classes\Form
{
    /** @var int ID сохраненный модели */
    public $id;

    /** @var bool */
    public $isSaved = false;

    /** @var NdcType */
    public $ndcType;

    /** @var string[] */
    public $validateErrors = [];

    /**
     * @return NdcType
     */
    abstract public function getNdcTypeModel();

    /**
     * конструктор
     */
    public function init()
    {
        $this->ndcType = $this->getNdcTypeModel();

        // Обработать submit (создать, редактировать, удалить)
        $this->loadFromInput();
    }

    /**
     * Обработать submit (создать, редактировать, удалить)
     */
    protected function loadFromInput()
    {
        // загрузить параметры от юзера
        $db = NdcType::getDb();
        $transaction = $db->beginTransaction();
        try {
            $post = Yii::$app->request->post();

            // название
            if (isset($post['dropButton'])) {

                // удалить
                $this->ndcType->delete();
                $this->id = null;
                $this->isSaved = true;

            } elseif ($this->ndcType->load($post)) {

                // создать/редактировать
                if ($this->ndcType->validate() && $this->ndcType->save()) {
                    $this->id = $this->ndcType->id;
                    $this->isSaved = true;
                } else {
                    // продолжить выполнение, чтобы показать юзеру массив с недозаполненными данными вместо эталонных
                    $this->validateErrors += $this->ndcType->getFirstErrors();
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
