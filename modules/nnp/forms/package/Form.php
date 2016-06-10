<?php

namespace app\modules\nnp\forms\package;

use app\classes\uu\forms\CrudMultipleTrait;
use app\modules\nnp\models\Package;
use InvalidArgumentException;
use yii;

abstract class Form extends \app\classes\Form
{
    use CrudMultipleTrait;

    /** @var int ID сохраненный модели */
    public $id;

    /** @var bool */
    public $isSaved = false;

    /** @var Package */
    public $package;

    /** @var string[] */
    public $validateErrors = [];

    /**
     * @return Package
     */
    abstract public function getPackageModel();

    /**
     * конструктор
     */
    public function init()
    {
        $this->package = $this->getPackageModel();

        // Обработать submit (создать, редактировать, удалить)
        $this->loadFromInput();
    }

    /**
     * Обработать submit (создать, редактировать, удалить)
     */
    protected function loadFromInput()
    {
        // загрузить параметры от юзера
        $db = Package::getDb();
        $transaction = $db->beginTransaction();
        try {
            $post = Yii::$app->request->post();

            // название
            if (isset($post['dropButton'])) {

                // удалить
                $this->package->delete();
                $this->id = null;
                $this->isSaved = true;

            } elseif ($this->package->load($post)) {

                // создать/редактировать
                if ($this->package->validate() && $this->package->save()) {
                    $this->id = $this->package->id;
                    $this->isSaved = true;
                } else {
                    // продолжить выполнение, чтобы показать юзеру массив с недозаполненными данными вместо эталонных
                    $this->validateErrors += $this->package->getFirstErrors();
                }
            }

            if ($this->validateErrors) {
                throw new InvalidArgumentException();
            }

            $transaction->commit();

        } catch (InvalidArgumentException $e) {
            $transaction->rollBack();
            $this->isSaved = false;
            $this->validateErrors[] = $e->getMessage();

        } catch (\Exception $e) {
            $transaction->rollBack();
            Yii::error($e);
            $this->isSaved = false;
            $this->validateErrors[] = YII_DEBUG ? $e->getMessage() : Yii::t('common', 'Internal error');
        }
    }
}
