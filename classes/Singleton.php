<?php
namespace app\classes;

abstract class Singleton
{

    private static $_instances = [];

    /**
     * @param array $args
     * @return self
     * @throws \yii\base\Exception
     */
    public static function me(array $args = null)
    {
        $class = get_called_class();
        if (!isset(self::$_instances[$class])) {
            // for Singleton::getInstance('class_name', $arg1, ...) calling
            if (2 < func_num_args()) {
                $args = func_get_args();
                array_shift($args);

                // emulat`ion of ReflectionClass->newInstanceWithoutConstructor
                $object = unserialize(sprintf('O:%d:"%s":0:{}', strlen($class), $class));

                call_user_func_array([$object, '__construct'], $args);
            } else {
                $object = ($args ? new $class($args) : new $class);
            }

            Assert::isTrue(
                $object instanceof Singleton,
                "Class '{$class}' is something not a Singleton's child"
            );

            self::$_instances[$class] = $object;
        }

        return self::$_instances[$class];
    }

    /**
     * Do not create more than one
     *
     * @inheritdoc
     */
    protected function __construct()
    {
    }

    /**
     * Do not duplicate
     *
     * @inheritdoc
     */
    final private function __clone()
    {
    }

    /**
     * Do not awaken
     *
     * @inheritdoc
     */
    final private function __wakeup()
    {
    }

}
