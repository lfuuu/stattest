<?php

namespace tests\codeception\unit\usage\sync;

use app\classes\api\ApiVpbx;

class _ApiVpbx extends ApiVpbx
{
    use _ApiTrait;

    public function isAvailable()
    {
        return true;
    }
}