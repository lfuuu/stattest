<?php

namespace tests\codeception\unit\custom;

use SyncCoreHelper;

include_once dirname(__FILE__) . '/../../../../stat/classes/SyncCoreHelper.php';

class NormalizeEmailTest extends \yii\codeception\TestCase
{
    /**
     */
    public function testNormalizeEmail()
    {
        $this->assertEquals(SyncCoreHelper::normalizeEmail('      example@example.com     '), 'example@example.com');
        $this->assertEquals(SyncCoreHelper::normalizeEmail('example@example.com; example2@example.com'), 'example@example.com');
        $this->assertEquals(SyncCoreHelper::normalizeEmail(' http://example.com     '), '');
    }
}