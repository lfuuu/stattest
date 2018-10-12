<?php

namespace app\commands\convert;

use app\exceptions\ModelValidationException;
use app\models\Lead;
use yii\console\Controller;

class LeadController extends Controller
{
    public function actionExtractDidsFromJson()
    {
        $leadQuery = Lead::find()
            ->where(['NOT', ['data_json' => null]]);
        foreach ($leadQuery->each() as $lead) {
            /** @var Lead $lead */
            if (!$data = $lead->getData()) {
                continue;
            }
            try {
                if (isset($data['did'])) {
                    $lead->did = $data['did'];
                }
                if (isset($data['did_mcn'])) {
                    $lead->did_mcn = $data['did_mcn'];
                }
                if (!$lead->save()) {
                    throw new ModelValidationException($lead);
                }
            } catch (\Exception $e) {
                echo $e->getMessage();
            }
        }
    }
}
