<?php

namespace app\modules\nnp\forms\prefix;

use app\modules\nnp\models\Prefix;
use InvalidArgumentException;
use yii;

abstract class Form extends \app\classes\Form
{
    /** @var int ID сохраненный модели */
    public $id;

    /** @var bool */
    public $isSaved = false;

    /** @var Prefix */
    public $prefix;

    /** @var string[] */
    public $validateErrors = [];

    /**
     * @return Prefix
     */
    abstract public function getPrefixModel();

    /**
     * конструктор
     */
    public function init()
    {
        $this->prefix = $this->getPrefixModel();

        // Обработать submit (создать, редактировать, удалить)
        $this->loadFromInput();
    }

    /**
     * Обработать submit (создать, редактировать, удалить)
     */
    protected function loadFromInput()
    {
        // загрузить параметры от юзера
        $db = Prefix::getDb();
        $transaction = $db->beginTransaction();
        try {
            $post = Yii::$app->request->post();

            // название
            if (isset($post['dropButton'])) {

                // удалить
                $this->prefix->delete();
                $this->id = null;
                $this->isSaved = true;

            } elseif ($this->prefix->load($post)) {

                // создать/редактировать
                if ($this->prefix->validate() && $this->prefix->save()) {
                    $this->id = $this->prefix->id;
                    $this->isSaved = true;
                } else {
                    // продолжить выполнение, чтобы показать юзеру массив с недозаполненными данными вместо эталонных
                    $this->validateErrors += $this->prefix->getFirstErrors();
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
