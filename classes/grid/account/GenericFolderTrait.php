<?php

namespace app\classes\grid\account;

use app\classes\grid\account\operator\operators\GenericFolder;
use app\models\BusinessProcessStatus;

trait GenericFolderTrait
{
    private static $commonClassChunk = 'app\classes\grid\account';

    /**
     * @param $statuses
     * @return AccountGridFolder[] $folders
     */
    public function getGenericFolders($statuses)
    {
        $folders = [];
        $process = $this->getBusinessProcessId();
        $cycleMapping = null;
        $rejectMapping = [];
        // Получение циклической карты инжектов и реджектов для связанных отчетов
        if (isset(static::$BUSINESS_CYCLE_MAPPING[$process]) && static::$BUSINESS_CYCLE_MAPPING[$process]) {
            $cycleMapping =  static::$BUSINESS_CYCLE_MAPPING[$process];
            if (isset($cycleMapping[static::BUSINESS_MAPPING_REJECT])) {
                $rejectMapping = $cycleMapping[static::BUSINESS_MAPPING_REJECT];
            }
        }
        /** @var BusinessProcessStatus $status */
        foreach ($statuses as $status) {
            // Реджект связанного отчета
            if ($rejectMapping && in_array($status->id, $rejectMapping)) {
                continue;
            }
            // Инжект связанного отчета, если он найден в циклической карте
            if (isset($cycleMapping[static::BUSINESS_MAPPING_INJECT][$status->id])) {
                $className = self::$commonClassChunk . $cycleMapping[static::BUSINESS_MAPPING_INJECT][$status->id];
                $folders[] = $className::create($this);
                continue;
            }
            $folder = GenericFolder::create($this);
            $folder->initialize($status, $this->getColumns());
            $folders[] = $folder;
        }
        // Карта добавочных отчетов, внедряемых независимо от основного цикла карты замен
        if (isset(static::$BUSINESS_EXTRA_MAPPING[$process])) {
            foreach (static::$BUSINESS_EXTRA_MAPPING[$process] as $item) {
                $className = self::$commonClassChunk . $item;
                $folders[] = $className::create($this);
            }
        }
        return $folders;
    }

    /**
     * @return AccountGridFolder[]
     */
    public function getFolders()
    {
        $statuses = BusinessProcessStatus::find()
            ->where(['business_process_id' => $this->getBusinessProcessId()])
            ->all();

        return $this->getGenericFolders($statuses);
    }
}