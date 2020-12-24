<?php

namespace app\modules\sim\commands;

use app\modules\sim\models\Registry;
use app\modules\sim\classes\RegistryState;
use app\modules\sim\forms\registry\CommandForm;
use app\modules\sim\forms\registry\Form;
use yii\console\Controller;

class RegistryController extends Controller
{
    const LIMIT_PER_PROCESS = 1;

    /**
     * Получаем заливки на обработку
     *
     * @return Registry[]
     */
    protected function getImportsToProcess()
    {
        return Registry::find()
            ->with('regionSettings.region')
            ->with('regionSettings.parent')
            ->where(['=', 'state', RegistryState::STARTED])
            ->orderBy([
                'updated_at' => SORT_ASC,
                'created_at' => SORT_ASC,
                'id' => SORT_ASC,
            ])
            ->limit(self::LIMIT_PER_PROCESS)
            ->all();
    }
    /**
     * @param $message
     * @param bool $lineBreak
     */
    protected function logLine($message, $lineBreak = true)
    {
        echo date("d-m-Y H:i:s") . ": " . $message . ($lineBreak ? PHP_EOL : '');
    }

    /**
     * Заливка
     */
    public function actionProcess()
    {
        foreach (self::getImportsToProcess() as $regionSimHistory) {
            $this->logLine('--------------------------------------------------');
            $this->logLine('Got import #' . $regionSimHistory->id);

            //$result = Form::process($regionSimHistory);
            $form = new CommandForm(['registry' => $regionSimHistory]);
            $result = $form->process();

                $this->logLine($result ? 'Success!' : 'Error!');
        }
    }
}