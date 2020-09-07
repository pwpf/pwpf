<?php

namespace PWPF\Registry;

if (!class_exists(__NAMESPACE__ . '\\' . 'Controller')) {
    /**
     * Controller Registry
     *
     * Maintains the list of all controllers objects
     *
     * @author Sławomir Kaleta <slaszka@gmail.com>
     */
    class ControllerRegistry
    {
        use BaseRegistry;

        /**
         * Returns key used to store a particular Controller Object
         *
         * @param string $controller_class_name Controller Class Name.
         * @param string $model_class_name      Model Class Name.
         * @param string $view_class_name       View Class Name.
         *
         * @return string
         */
        public static function getKey(string $controller_class_name, string $model_class_name, string $view_class_name)
        {
            return "{$controller_class_name}__{$model_class_name}__{$view_class_name}";
        }
    }
}
