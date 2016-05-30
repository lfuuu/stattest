<?php

namespace app\modules\nnp\controllers;

use app\classes\BaseController;
use app\classes\Connection;
use app\modules\nnp\filter\NumberRangeFilter;
use app\modules\nnp\forms\NumberRangeFormEdit;
use app\modules\nnp\models\NumberRangePrefix;
use app\modules\nnp\models\Prefix;
use Yii;
use yii\db\ActiveQuery;

/**
 * Диапазон номеров
 */
class NumberRangeController extends BaseController
{
    /**
     * Список
     *
     * @return string
     */
    public function actionIndex()
    {
        $filterModel = new NumberRangeFilter();

        $get = Yii::$app->request->get();
        if (!isset($get['CountryFilter'])) {
            $get['NumberRangeFilter']['is_active'] = 1; // по-умолчанию только "вкл."
        }
        $filterModel->load($get);

        $this->addOrRemoveFilterModelToPrefix($filterModel);

        return $this->render('index', [
            'filterModel' => $filterModel,
        ]);
    }

    /**
     * Добавить/удалить отфильтрованные записи в префикс
     * @param NumberRangeFilter $filterModel
     * @return bool
     */
    protected function addOrRemoveFilterModelToPrefix(NumberRangeFilter $filterModel)
    {
        $post = Yii::$app->request->post();
        if (!isset($post['Prefix'])) {
            return true;
        }

        if (isset($post['Prefix']['id'])) {
            $prefixId = (int)$post['Prefix']['id'];
        } else {
            $prefixId = 0;
        }

        if (isset($post['Prefix']['name'])) {
            $prefixName = trim($post['Prefix']['name']);
        } else {
            $prefixName = 0;
        }

        if (!$prefixId && !$prefixName) {
            Yii::$app->session->setFlash('error', 'Не указан префикс ни существующий, ни новый');
            return false;
        }

        // построить запрос, выбирающий все отфильтрованные записи
        /** @var ActiveQuery $query */
        $query = clone $filterModel->search()->query;
        // обработать все записи, а не только на этой странице
        $query->offset(0);
        $query->limit(null);
        $query->select('id');
        $sql = $query->createCommand()->rawSql;


        if (isset($post['dropButton'])) {
            // удалить из префикса
            if (!$prefixId) {
                Yii::$app->session->setFlash('error', 'Для удаления отфильтрованных записей из префикса выберите его');
                return false;
            }

            return $this->removeFilterModelFromPrefix($sql, $prefixId);
        }

        // добавить в префикс

        if ($prefixName) {
            // .. в новый
            if ($prefixId) {
                Yii::$app->session->setFlash('error', 'Укажите только один префикс: либо существующий, либо новый');
                return false;
            }

            $prefix = new Prefix();
            $prefix->name = $prefixName;
            if (!$prefix->save()) {
                Yii::$app->session->setFlash('error', 'Ошибка создания нового префикса');
                return false;
            }

            $prefixId = $prefix->id;
        }

        return $this->addFilterModelToPrefix($sql, $prefixId);
    }

    /**
     * Добавить отфильтрованные записи в префикс
     * @param string $sql
     * @param int $prefixId
     * @return bool
     */
    protected function addFilterModelToPrefix($sql, $prefixId)
    {
        /** @var Connection $dbPgNnp */
        $dbPgNnp = Yii::$app->dbPgNnp;
        $transaction = $dbPgNnp->beginTransaction();
        try {

            // "чтобы продать что-нибудь не нужное, надо сначала купить что-нибудь ненужное" (С) Матроскин
            // повторное добавление дает ошибку, "on duplicate key" в postresql нет, поэтому проще удалить дубли заранее
            $this->removeFilterModelFromPrefix($sql, $prefixId);

            $numberRangePrefix = NumberRangePrefix::tableName();
            $userId = Yii::$app->user->getId();
            $sql = <<<SQL
INSERT INTO {$numberRangePrefix}
    (number_range_id, prefix_id, insert_time, insert_user_id)
SELECT
    t.id, {$prefixId}, NOW(), {$userId}
FROM 
    ( {$sql} ) t
SQL;

            $affectedRows = $dbPgNnp->createCommand($sql)->execute();
            Yii::$app->session->setFlash('success', 'В префикс добавлено ' . Yii::t('common', '{n, plural, one{# entry} other{# entries}}', ['n' => $affectedRows]));
            $transaction->commit();
            return true;

        } catch (\Exception $e) {
            $transaction->rollBack();
            Yii::$app->session->setFlash('error', 'Ошибка добавления отфильтрованных записей в префикс');
            Yii::error($e);
            return false;
        }
    }

    /**
     * Удалить отфильтрованные записи из префикса
     * @param string $sql
     * @param int $prefixId
     * @return bool
     */
    protected function removeFilterModelFromPrefix($sql, $prefixId)
    {
        /** @var Connection $dbPgNnp */
        $dbPgNnp = Yii::$app->dbPgNnp;
        $transaction = $dbPgNnp->beginTransaction();
        try {

            $numberRangePrefix = NumberRangePrefix::tableName();
            $sql = <<<SQL
DELETE FROM {$numberRangePrefix}
WHERE
    prefix_id = {$prefixId}
    AND number_range_id IN ( {$sql} )
SQL;

            $affectedRows = $dbPgNnp->createCommand($sql)->execute();
            Yii::$app->session->setFlash('success', 'Из префикса удалено ' . Yii::t('common', '{n, plural, one{# entry} other{# entries}}', ['n' => $affectedRows]));
            $transaction->commit();
            return true;

        } catch (\Exception $e) {
            $transaction->rollBack();
            Yii::$app->session->setFlash('error', 'Ошибка удаления отфильтрованных записей из префикса');
            Yii::error($e);
            return false;
        }
    }

    /**
     * Редактировать
     *
     * @param int $id
     * @return string
     */
    public function actionEdit($id)
    {
        /** @var NumberRangeFormEdit $form */
        $form = new NumberRangeFormEdit([
            'id' => $id
        ]);

        if ($form->isSaved) {
            return $this->redirect(['index']);
        } else {
            return $this->render('edit', [
                'formModel' => $form,
            ]);
        }
    }
}
