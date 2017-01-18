<?php
namespace app\classes;

/**
 * Class Singleton
 */
abstract class Singleton
{
    private static $_instances = [];

    /**
     * @param array $args
     * @return self
     * @throws \yii\base\Exception
     */
    public static function me($args = null)
    {
        $class = get_called_class();
        if (!isset(self::$_instances[$class])) {
            // for Singleton::getInstance('class_name', $arg1, ...) calling
            if (2 < func_num_args()) {
                $args = func_get_args();
                array_shift($args);

                // emulat`ion of ReflectionClass->newInstanceWithoutConstructor
                $object = unserialize(
                    sprintf('O:%d:"%s":0:{}', strlen($class), $class)
                );

                call_user_func_array(
                    [$object, '__construct'],
                    $args
                );
            } else {
                $object = $args ? new $class($args) : new $class();
            }

            Assert::isTrue(
                $object instanceof Singleton,
                "Class '{$class}' is something not a Singleton's child"
            );

            self::$_instances[$class] = $object;
        }

        $me = self::$_instances[$class];
        if (method_exists($me, 'init')) {
            $me->init();
        }

        return $me;
    }

    /**
     * Don't create me
     */
    final protected function __construct()
    {
    }

    /**
     * Don't clone me
     */
    final private function __clone()
    {
    }

    /**
     * Don't wake me up
     */
    final private function __wakeup()
    {
    }
}