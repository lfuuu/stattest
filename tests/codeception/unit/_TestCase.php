<?php

namespace tests\codeception\unit;

use yii\codeception\TestCase;
use yii\db\ActiveRecord;


/**
 * Class _TestCase
 *
 * @link http://codeception.com/docs/modules/Asserts
 *
 * @method bool assertNotEmpty($value)
 * @method bool assertEmpty($value)
 * @method bool assertEquals($value1, $value2)
 * @method bool assertTrue($value)
 * @method bool assertFalse($value)
 * @method bool assertNotNull($value)
 * @method bool assertNull($value)
 * @method bool assertInstanceOf($class, $value)
 * @method bool assertArrayHasKey($key, $value)
 * @method bool assertArrayNotHasKey($key, $value)
 * @method bool assertArraySubset($subset, $array)
 * @method bool fail($message)
 */
class _TestCase extends TestCase
{
    public function failOnValidationModel(ActiveRecord $model) {
        $this->fail(implode(' ', $model->getFirstErrors()));
    }
}