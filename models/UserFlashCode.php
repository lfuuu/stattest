<?php

namespace app\models;

use app\classes\behaviors\BillChangeLog;
use app\classes\behaviors\BillInvoiceReversal;
use app\classes\behaviors\CheckBillPaymentOverdue;
use app\classes\behaviors\SetBillPaymentDate;
use app\classes\behaviors\SetBillPaymentOverdue;
use app\classes\model\ActiveRecord;
use app\classes\Utils;
use app\dao\BillDao;
use app\dao\BillUuCorrectionDao;
use app\exceptions\ModelValidationException;
use app\helpers\DateTimeZoneHelper;
use app\models\media\BillExtFiles;
use app\modules\uu\models\Bill as uuBill;
use app\queries\BillQuery;
use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\Expression;
use yii\helpers\Url;

/**
 * Flash-коды пользователей
 *
 * @property int $user_id
 * @property int $code_md5
 * @property int $created_at
 */
class UserFlashCode extends ActiveRecord
{
    const LIFETIME = 180;

    /**
     * Название таблицы
     *
     * @return string
     */
    public static function tableName()
    {
        return 'user_flash_code';
    }

    public function behaviors()
    {
        return [
            [
                'class' => TimestampBehavior::class,
                'updatedAtAttribute' => null,
                'value' => new Expression('NOW()'),
            ]
        ];
    }

    /**
     * Проверка на правильность кода
     *
     * @param string $code
     * @return bool
     */
    public function isCodeValide($code)
    {
        return $this->code_md5 === md5($code);
    }

    public static function setCode($userId, $code)
    {
        UserFlashCode::clean();

        $flashCode = UserFlashCode::findOne(['user_id' => $userId]);
        if (!$flashCode) {
            $flashCode = new UserFlashCode();
            $flashCode->user_id = $userId;
        }

        $flashCode->code_md5 = md5($code);

        if (!$flashCode->save()) {
            throw new ModelValidationException($flashCode);
        }
    }

    public static function clean()
    {
        self::DeleteAll(['<', 'created_at', new Expression('DATE_ADD(NOW(), INTERVAL -' . self::LIFETIME . ' second)')]);
    }

}
