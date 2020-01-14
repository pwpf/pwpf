<?php

namespace PWPF\Registry;

if (!class_exists(__NAMESPACE__ . '\\' . 'Model')) {
    /**
     * Model Registry
     *
     * Maintains the list of all models objects
     *
     * @author Sławomir Kaleta <slaszka@gmail.com>
     */
    class ModelRegistry
    {
        use BaseRegistry;
    }
}
