<?php

namespace PWPF\Model;


use PWPF\Registry\ModelRegistry;

/**
 * Abstract class to define/implement base methods for model classes
 *
 * @author Sławomir Kaleta <slaszka@gmail.com>
 */
class Model
{

    /**
     * Provides access to a single instance of a module using the singleton pattern
     *
     * @return object
     */
    public static function get_instance()
    {
        $classname = get_called_class();
        $instance = ModelRegistry::get($classname);

        if (null === $instance) {
            $instance = new $classname();
            ModelRegistry::set($classname, $instance);
        }

        return $instance;
    }

}
