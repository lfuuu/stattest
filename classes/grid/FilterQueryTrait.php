<?php

namespace app\classes\grid;

use app\classes\model\ActiveRecord;
use app\classes\ReturnFormatted;
use app\exceptions\ModelValidationException;
use app\exceptions\web\BadRequestHttpException;
use app\modules\nnp\models\FilterQuery;
use Yii;

trait FilterQueryTrait
{
    /**
     * @return string
     * @throws \yii\base\ExitException
     * @throws \app\exceptions\web\BadRequestHttpException
     */
    public function run()
    {
        $filterQueryAction = Yii::$app->request->post('filterQueryAction');
        if ($filterQueryAction) {
            Yii::$app->controller->layout = false;

            while (ob_get_level() > 0) {
                ob_end_clean();
            }

            $action = '_actionFilterQuery' . ucfirst($filterQueryAction);

            if (!method_exists($this, $action)) {
                throw new BadRequestHttpException('Action not found');
            }

            $returnArray = $this->{$action}();
            ReturnFormatted::me()->returnFormattedValues($returnArray, ReturnFormatted::FORMAT_JSON);
        }

        parent::run();
    }

    /**
     * Сохранить фильтр
     *
     * @return array
     * @throws \ReflectionException
     * @throws \app\exceptions\web\BadRequestHttpException
     * @throws \app\exceptions\ModelValidationException
     */
    private function _actionFilterQueryAdd()
    {
        $filterQueryName = Yii::$app->request->post('filterQueryName');
        if (!$filterQueryName) {
            throw new BadRequestHttpException('Не указано имя фильтра');
        }

        /** @var ActiveRecord $filterModel */
        $filterModel = $this->filterModel;

        $filterQuery = new FilterQuery();
        $filterQuery->name = $filterQueryName;
        $filterQuery->data = $filterModel->getObjectNotEmptyValues();
        $filterQuery->model_name = $filterModel->getClassName();
        if (!$filterQuery->save()) {
            throw new ModelValidationException($filterQuery);
        }

        Yii::$app->session->setFlash('success', 'Фильтр успешно сохранен');

        return $this->_getFilterQueryUrl($filterQuery);
    }

    /**
     * Сохранить фильтр
     *
     * @return array
     * @throws \ReflectionException
     * @throws \app\exceptions\web\BadRequestHttpException
     * @throws \app\exceptions\ModelValidationException
     */
    private function _actionFilterQueryLoad()
    {
        $filterQuery = $this->_getFilterQuery();
        Yii::$app->session->setFlash('success', 'Фильтр ' . $filterQuery->name . ' успешно загружен');

        return $this->_getFilterQueryUrl($filterQuery);
    }

    /**
     * Вернуть URL фильтра
     *
     * @param FilterQuery $filterQuery
     * @return array
     * @throws \ReflectionException
     * @throws \app\exceptions\web\BadRequestHttpException
     * @throws \app\exceptions\ModelValidationException
     */
    private function _getFilterQueryUrl(FilterQuery $filterQuery)
    {
        /** @var ActiveRecord $filterModel */
        $filterModel = $this->filterModel;

        $url = $_SERVER['REQUEST_URI'];
        if ($strpos = strpos($url, '?')) {
            $url = substr($url, 0, $strpos);
        }

        $queryData = [
            'filterQueryId' => $filterQuery->id,
        ];
        $formName = $filterModel->formName();
        $filters = $filterQuery->data;
        foreach ($filters as $filterKey => $filterValue) {
            $queryData[$formName . '[' . $filterKey . ']'] = $filterValue;
        }

        $url .= '?' . http_build_query($queryData);

        return ['location' => $url];
    }

    /**
     * Заменить фильтр
     *
     * @return array
     * @throws \ReflectionException
     * @throws \app\exceptions\web\BadRequestHttpException
     * @throws \app\exceptions\ModelValidationException
     */
    private function _actionFilterQueryReplace()
    {
        /** @var ActiveRecord $filterModel */
        $filterModel = $this->filterModel;

        $filterQuery = $this->_getFilterQuery();
        $filterQuery->data = $filterModel->getObjectNotEmptyValues();
        if (!$filterQuery->save()) {
            throw new ModelValidationException($filterQuery);
        }

        Yii::$app->session->setFlash('success', 'Фильтр ' . $filterQuery->name . ' успешно заменен');

        return [];
    }

    /**
     * Удалить фильтр
     *
     * @return array
     * @throws \Exception
     * @throws \app\exceptions\web\BadRequestHttpException
     * @throws \app\exceptions\ModelValidationException
     */
    private function _actionFilterQueryDelete()
    {
        $filterQuery = $this->_getFilterQuery();
        if (!$filterQuery->delete()) {
            throw new ModelValidationException($filterQuery);
        }

        Yii::$app->session->setFlash('success', 'Фильтр ' . $filterQuery->name . ' успешно удален');

        return [];
    }

    /**
     * Вернуть запрощенный фильтр
     *
     * @return FilterQuery
     * @throws \app\exceptions\web\BadRequestHttpException
     */
    private function _getFilterQuery()
    {
        $filterQueryId = Yii::$app->request->post('filterQueryId');
        if (!$filterQueryId) {
            throw new BadRequestHttpException('Не указан фильтр');
        }

        /** @var ActiveRecord $filterModel */
        $filterModel = $this->filterModel;

        $filterQuery = FilterQuery::findOne(['id' => $filterQueryId, 'model_name' => $filterModel->getClassName()]);
        if (!$filterQuery) {
            throw new BadRequestHttpException('Неправильный фильтр');
        }

        return $filterQuery;
    }
}
