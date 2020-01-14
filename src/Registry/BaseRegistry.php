<?php

namespace PWPF\Registry;

if (!trait_exists('Base_Registry')) {

    /**
     * Base Registry Trait
     *
     * Controller Registry and Model Registry use this trait to deal with all
     * objects.
     *
     * This trait provides methods to store & retrieve objects in Registry
     *
     * If you have not heard about the term Registry before, think of hashmaps.
     * So creating registry means creating hashmaps to store objects.
     *
     * @author Sławomir Kaleta <slaszka@gmail.com>
     */
    trait BaseRegistry
    {

        /**
         * Variable that holds all objects in registry.
         *
         * @var array
         */
        protected static $storedObjects = [];

        /**
         * Add object to registry
         *
         * @param string $key   Key to be used to map with Object.
         * @param mixed  $value Object to Store.
         *
         * @return void
         */
        public static function set($key, $value)
        {
            if (!is_string($key)) {
                trigger_error(
                    __('Key passed to `set` method must be key', 'PLUGIN_ID'),
                    E_USER_ERROR
                ); // @codingStandardsIgnoreLine.
            }
            static::$storedObjects[$key] = $value;
        }

        /**
         * Get object from registry
         *
         * @param string $key Key of the object to restore.
         *
         * @return mixed
         */
        public static function get($key)
        {
            if (!is_string($key)) {
                trigger_error(
                    __('Key passed to `get` method must be key', 'PLUGIN_ID'),
                    E_USER_ERROR
                ); // @codingStandardsIgnoreLine.
            }

            if (!isset(static::$storedObjects[$key])) {
                return null;
            }

            return static::$storedObjects[$key];
        }

        /**
         * Returns all objects
         *
         * @return array
         */
        public static function getAllObjects()
        {
            return static::$storedObjects;
        }
    }
}
