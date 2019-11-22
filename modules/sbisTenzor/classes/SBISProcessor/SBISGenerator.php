<?php

namespace app\modules\sbisTenzor\classes\SBISProcessor;

use app\exceptions\ModelValidationException;
use app\modules\sbisTenzor\classes\SBISGeneratedDraftStatus;
use app\modules\sbisTenzor\classes\SBISProcessor;
use app\modules\sbisTenzor\models\SBISDocument;
use app\modules\sbisTenzor\models\SBISGeneratedDraft;
use Yii;

/**
 * Обработчик создания пакетов документов
 */
class SBISGenerator extends SBISProcessor
{
    const LIMIT_PER_PROCESS = 100;

    /**
     * Основа выборки
     *
     * @return \yii\db\ActiveQuery
     */
    protected function getBasePreGeneratedQuery()
    {
        return SBISGeneratedDraft::find()
            ->andWhere(['=', 'state', SBISGeneratedDraftStatus::PROCESSING]);
    }

    /**
     * Получаем количество документов для генерации
     *
     * @return int
     */
    protected function getDraftsToProcessCount()
    {
        return $this->getBasePreGeneratedQuery()
            ->count();
    }

    /**
     * Получаем черновики пакетов документов
     *
     * @return SBISGeneratedDraft[]
     */
    protected function getDraftsToProcess()
    {
        return $this->getBasePreGeneratedQuery()
            ->with('invoice')
            ->all();
    }

    /**
     * Точка входа обработчика
     *
     * @throws ModelValidationException
     * @throws \yii\base\Exception
     * @throws \yii\db\Exception
     * @throws \Exception
     */
    public function run()
    {
        $draftsCount = $this->getDraftsToProcessCount();
        if ($draftsCount == 0) {
            return 0 ;
        }

        $processed = 0;
        $drafts = $this->getDraftsToProcess();
        $processed += count($drafts);

        foreach ($drafts as $draft) {
            $transaction = SBISGeneratedDraft::getDb()->beginTransaction();
            try {

                $draft->generateDocument();

                $transaction->commit();
            } catch (\Exception $e) {
                $transaction->rollBack();

                Yii::error($e);
                $errorText = sprintf(
                    'Ошибка обработчика генерации документов (draft id: %s): %s',
                    $draft->id,
                    $e->getMessage()
                );
                Yii::error($errorText, SBISDocument::LOG_CATEGORY);

                $draft->state = SBISGeneratedDraftStatus::ERROR;
                if (!$draft->save()) {
                    throw new ModelValidationException($draft);
                }
            }
        }

        return $processed;
    }
}