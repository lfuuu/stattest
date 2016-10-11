<?php

namespace app\modules\nnp\forms\ndcType;

use app\modules\nnp\models\NdcType;

class FormNew extends Form
{
    /**
     * @return NdcType
     */
    public function getNdcTypeModel()
    {
        return new NdcType();
    }
}