<?php

namespace app\classes\media;

use app\exceptions\ModelValidationException;
use app\helpers\DateTimeZoneHelper;
use app\models\Bill;
use app\models\media\BillExtFiles;
use Yii;
use DateTime;
use app\classes\model\ActiveRecord;

class BillExtMedia extends MediaManager
{
    /** @var Bill */
    private $bill;

    /**
     * @param Bill $bill
     */
    public function __construct(Bill $bill)
    {
        $this->bill = $bill;
    }

    /**
     * @return string
     */
    public function getFolder()
    {
        return 'files/bill_ext';
    }

    /**
     * @param string $name
     * @param string $comment
     * @return BillExtFiles
     * @throws ModelValidationException
     */
    protected function createFileModel($name, $comment)
    {

        // в живых останется только один
        $existModel = BillExtFiles::find()->where(['bill_no' => $this->bill->bill_no])->one();

        if ($existModel) {
            $this->removeFile($existModel);
        }


        $model = new BillExtFiles();
        $model->bill_no = $this->bill->bill_no;
        $model->ts = (new DateTime())->format(DateTimeZoneHelper::DATETIME_FORMAT);

        $model->comment = $comment;
        $model->name = $name;
        $model->user_id = Yii::$app->user->getId();

        if (!$model->save()) {
            throw new ModelValidationException($model);
        }

        return $model;
    }

    /**
     * @param ActiveRecord $fileModel
     * @throws \Exception
     */
    protected function deleteFileModel(ActiveRecord $fileModel)
    {
        /** @var BillExtFiles $model */
        $model = BillExtFiles::findOne(['bill_no' => $fileModel->bill_no, 'id' => $fileModel->id]);
        if ($model) {
            $model->delete();
        }
    }

    /**
     * @return BillExtFiles[]
     */
    protected function getFileModels()
    {
        return BillExtFiles::findAll(['bill_no' => $this->bill->bill_no]);
    }
}