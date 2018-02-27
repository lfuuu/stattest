<?php
namespace app\classes;

use app\modules\uu\forms\CrudMultipleTrait;
use InvalidArgumentException;
use Yii;
use yii\base\InvalidParamException;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;
use yii\db\Query;

abstract class Form extends Model
{
    use CrudMultipleTrait;

    const PAGE_SIZE = 200;
    const EVENT_AFTER_SAVE = 'afterSave';

    /** @var ActiveRecord */
    protected static $formModel;

    /** @var int ID сохраненный модели */
    public $id;

    /** @var bool */
    public $isSaved = false;

    /**
     * @param ActiveRecord $model
     * @param bool $runValidation
     * @param bool $autoSetAttributes
     * @return bool
     */
    public function saveModel(ActiveRecord $model, $runValidation = true, $autoSetAttributes = false)
    {
        if ($autoSetAttributes) {
            $attributes = $this->getAttributes(null, ['id', 'isSaved', 'validateErrors']);
            foreach ($attributes as $name => $value) {
                $model->$name = $value;
            }
        }

        if (!$model->save($runValidation)) {
            foreach ($model->getErrors() as $attribute => $errors) {
                foreach ($errors as $error) {
                    $this->addError($attribute, $error);
                }
            }

            return false;
        }

        return true;
    }

    /**
     * @throws InvalidArgumentException
     * @throws \yii\db\Exception
     */
    protected function _loadFromInput()
    {
        $connection = $this->getModel()->getDb();
        $transaction = $connection->beginTransaction();

        try {
            $post = Yii::$app->request->post();

            if (isset($post['dropButton'])) {
                // Удаление записи
                $this->getModel()->delete();
                $this->id = null;
                $this->isSaved = true;

            } elseif ($this->getModel()->load($post)) {
                // Редактирование записи
                if ($this->getModel()->validate() && $this->getModel()->save()) {
                    $this->id = $this->getModel()->primaryKey;
                    $this->isSaved = true;
                } else {
                    // Сбор ошибок валидации
                    $this->validateErrors += $this->getModel()->getFirstErrors();
                }
            }

            if ($this->validateErrors) {
                throw new InvalidArgumentException;
            }

            $transaction->commit();

        } catch (InvalidArgumentException $e) {
            $transaction->rollBack();
            $this->isSaved = false;

        } catch (\Exception $e) {
            $transaction->rollBack();
            Yii::error($e);
            $this->isSaved = false;
            $this->validateErrors[] = YII_DEBUG ?
                $e->getMessage() :
                Yii::t('common', 'Internal error');
        }
    }

    /**
     * @return ActiveRecord
     */
    public function getModel()
    {
        return static::$formModel;
    }

    /**
     * @param array $config
     * @return ActiveDataProvider
     * @throws InvalidParamException
     */
    public function spawnDataProvider(array $config = [])
    {
        $query = $this->spawnQuery();

        if ($this->validate()) {
            $this->applyFilter($query);
        }

        $dataProviderConfig = [
            'query' => $query,
        ] + array_merge([
            'pagination' => [
                'pageSize' => self::PAGE_SIZE,
            ],
        ], $config);

        return new ActiveDataProvider($dataProviderConfig);
    }

    /**
     * @return ActiveQuery
     */
    public function spawnFilteredQuery()
    {
        $query = $this->spawnQuery();
        $this->applyFilter($query);
        return $query;
    }

    /**
     * @param Query $query
     */
    public function applyFilter(Query $query)
    {

    }

    /**
     * @return ActiveQuery
     */
    public function spawnQuery()
    {

    }

    /**
     * @param array $data
     * @param string $formName
     * @return bool
     */
    public function load($data, $formName = null)
    {
        $result = parent::load($data, $formName);
        $this->preProcess();
        return $result;
    }

    /**
     * @return void
     */
    public function afterValidate()
    {
        if (count($this->getErrors()) && is_a(Yii::$app, 'yii\web\Application')) {
            Yii::$app->session->setFlash('error', Html::errorSummary($this, [
                'class' => 'alert-danger fade in text-left',
            ]));
        }

        parent::afterValidate();
    }

    /**
     * @inheritdoc
     */
    protected function preProcess()
    {

    }

    /**
     * @return string
     */
    public function getErrorsAsString()
    {
        return implode('<br />' . PHP_EOL, $this->getFirstErrors());
    }

}
