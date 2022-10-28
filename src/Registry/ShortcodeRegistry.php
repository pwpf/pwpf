<?php

namespace PWPF\Registry;

use Dframe\Loader\Exceptions\LoaderException;
use Dframe\Loader\Loader;
use Exception;
use PWPF\Routing\RouteType;

/**
 * Class Responsible for registering Routes
 *
 * @author Sławomir Kaleta <slaszka@gmail.com>
 */
class ShortcodeRegistry
{
    /**
     * This constant is used to register late frontend routes
     *
     */
    public const REGISTER_LATE_FRONTEND_ROUTES = true;

    /**
     * Holds Model, View & Controllers triad for All routes except 'Model Only' Routes
     *
     * @var array
     */
    protected static $mvcComponents = [];

    protected static Loader $loader;

    /**
     * @var string
     */
    public $routeTypeToRegister;

    /**
     * @var string
     */
    public $currentShortcode;

    /**
     * Constructor
     */
    public function __construct($boostrap = false)
    {
        $this->boostrap = $boostrap;
        $this->registerHookCallbacks();
    }

    /**
     * Register callbacks for actions and filters
     */
    protected function registerHookCallbacks()
    {
        add_action('init', [$this, 'registerGenericRoutes']);
        add_action('wp', [$this, 'registerLateFrontendRoutes']);
    }

    /**
     * @param $app
     *
     * @return $this
     */
    public function setApp($app)
    {
        $this->app = $app;
        return $this;
    }

    /**
     * Register Generic Routes
     *
     * @return void
     */
    public function registerGenericRoutes()
    {
        $this->registerRoutes();
    }

    /**
     * Registers Enqueued Routes
     *
     * @param bool $registerLateFrontendRoutes Whether to register late frontend routes.
     *
     * @return void
     */
    protected function registerRoutes($registerLateFrontendRoutes = false)
    {
        if ($registerLateFrontendRoutes) {
            $routeTypes = $this->lateFrontendRouteTypes();
        } else {
            $routeTypes = $this->genericRouteTypes();
        }
        if (empty($routeTypes)) {
            return;
        }
        foreach ($routeTypes as $routeType) {
            if ($this->isRequest($routeType) && !empty(static::$mvcComponents[$routeType])) {
                foreach (static::$mvcComponents[$routeType] as $mvcComponent) {
                    $this->dispatch($mvcComponent, $routeType);
                }
            }
        }
    }

    /**
     * Returns list of Route types belonging to Frontend but registered late
     *
     * @return array
     */
    public function lateFrontendRouteTypes()
    {
        return apply_filters(
            'pwpf_late_frontend_route_types',
            [RouteType::LATE_FRONTEND, RouteType::LATE_FRONTEND_WITH_POSSIBLE_AJAX]
        );
    }

    /**
     * Returns List of commonly/mostly used Route types
     *
     * @return array
     */
    public function genericRouteTypes()
    {
        return apply_filters(
            'pwpf_route_types',
            [
                RouteType::ANY,
                RouteType::ADMIN,
                RouteType::ADMIN_WITH_POSSIBLE_AJAX,
                RouteType::AJAX,
                RouteType::CRON,
                RouteType::FRONTEND,
                RouteType::FRONTEND_WITH_POSSIBLE_AJAX
            ]
        );
    }

    /**
     * Identifies Request Type
     *
     * @param string $routeType Route Type to identify.
     *
     * @return bool|void
     */
    protected function isRequest($routeType)
    {
        switch ($routeType) {
            case RouteType::ANY:
                return true;
            case RouteType::ADMIN:
            case RouteType::ADMIN_WITH_POSSIBLE_AJAX:
                return is_admin();
            case RouteType::AJAX:
                return defined('DOING_AJAX');
            case RouteType::CRON:
                return defined('DOING_CRON');
            case RouteType::FRONTEND:
            case RouteType::FRONTEND_WITH_POSSIBLE_AJAX:
                return (!is_admin() || defined('DOING_AJAX')) && !defined('DOING_CRON') && !defined('REST_REQUEST');
            case RouteType::LATE_FRONTEND:
            case RouteType::LATE_FRONTEND_WITH_POSSIBLE_AJAX:
                return $this->isRequest('frontend') || current_action() == 'wp' || did_action('wp') === 1;
        }
    }

    /**
     * Dispatches the route of specified $routeType by creating a controller object
     *
     * @param array  $mvcComponent Model-View-Controller triads for all registered routes.
     * @param string $routeType    Route Type.
     *
     * @return void
     */
    protected function dispatch($mvcComponent, $routeType)
    {
        if (!defined('APP_DIR')) {
            define('APP_DIR', '../../../../app/');
        }
        if (!defined('SALT')) {
            define('SALT', 'SALT');
        }
        if (isset($mvcComponent['shortcode']) && false === $mvcComponent['shortcode']) {
            return;
        }
        if (is_callable($mvcComponent['shortcode'])) {
            $mvcComponent['shortcode'] = call_user_func($mvcComponent['shortcode']);
            if (false === $mvcComponent['shortcode']) {
                return;
            }
        }
        try {
            if (!isset(self::$loader)) {
                $boostrap = new $this->boostrap();
                self::$loader = new Loader($boostrap);
            }

            $loader = self::$loader;
        } catch (LoaderException $e) {
            die($e->getMessage());
        } catch (Exception $e) {
            die($e->getMessage());
        }

        @(list($shortcode, $action) = explode('@', $mvcComponent['shortcode']));
        $loadShortcode = $loader->loadController($shortcode, '\\');

        if (method_exists($loadShortcode, 'start')) {
            $loadShortcode->start();
        }
        if (method_exists($loadShortcode, 'init')) {
            $loadShortcode->init();
        }
        if ($action !== null) {
            return $loadShortcode->{$action}();
        }
    }

    /**
     * Register Late Frontend Routes
     *
     * @return void
     */
    public function registerLateFrontendRoutes()
    {
        $this->registerRoutes(self::REGISTER_LATE_FRONTEND_ROUTES);
    }

    /**
     * Type of Route to be registered. Every time a new route needs to be
     * registered, this function should be called first on `$route` object
     *
     * @param string $type Type of route to be registered.
     *
     * @return ShortcodeRegistry Returns `Router` object.
     */
    public function registerRouteOfType($type)
    {
        if (in_array($type, $this->lateFrontendRouteTypes()) && did_action('wp')) {
            trigger_error(
                __(
                    'Late Routes can not be registered after `wp` hook is triggered. Register your route before `wp` hook is triggered.',
                    'PLUGIN'
                ),
                E_USER_ERROR
            );
            // @codingStandardsIgnoreLine.
        }
        if (in_array($type, $this->genericRouteTypes()) && did_action('init')) {
            trigger_error(
                __(
                    'Non-Late Routes can not be registered after `init` hook is triggered. Register your route before `init` hook is triggered.',
                    'PLUGIN'
                ),
                E_USER_ERROR
            );
            // @codingStandardsIgnoreLine.
        }
        $this->routeTypeToRegister = $type;
        return $this;
    }


    /**
     * Enqueues a controller to be associated with the Route
     *
     * @param mixed $shortcode Controller to be associated with the Route. Could be String or callback.
     *
     * @return object Returns Router Object
     */
    public function withShortcode($shortcode)
    {
        if ($shortcode === false) {
            return $this;
        }

        $this->currentShortcode = $this->buildShortcodeUniqueId($shortcode);
        static::$mvcComponents[$this->routeTypeToRegister][$this->currentShortcode] = ['shortcode' => $shortcode];
        return $this;
    }

    /**
     * Generates a Unique id for each controller
     *
     * This unique id is used as an array key inside mvc_components array which
     * is used while enqueueing models and views to associate them with the
     * controller.
     *
     * @param mixed $shortcode Controller to be associated with the Route. Could be String or callback.
     *
     * @return string|void
     */
    public function buildShortcodeUniqueId($shortcode)
    {
        $prefix = mt_rand() . '_';
        if (is_string($shortcode)) {
            return $prefix . $shortcode;
        }
        if (is_object($shortcode)) {
            // Closures are currently implemented as objects.
            $shortcode = [$shortcode, ''];
        } else {
            $shortcode = (array)$shortcode;
        }
        if (is_object($shortcode[0])) {
            // Object Class Calling.
            return $prefix . spl_object_hash($shortcode[0]) . $shortcode[1];
        }
        if (is_string($shortcode[0])) {
            // Static Calling.
            return $prefix . $shortcode[0] . '::' . $shortcode[1];
        }
    }

    /**
     * Returns the Full Qualified Class Name for given class name
     *
     * @param string $class            Class whose FQCN needs to be found out.
     * @param string $mvcComponentType Could be between 'model', 'view' or 'shortcode'.
     * @param string $routeType        Could be 'admin' or 'frontend'.
     *
     * @return string Retuns Full Qualified Class Name.
     * @throws Exception
     */
    protected function getFullyQualifiedClassName($class, $mvcComponentType, $routeType)
    {
        // If route type is admin or frontend.
        if (strpos($routeType, 'admin') !== false || strpos($routeType, 'frontend') !== false) {
            if (isset($this->app) and !empty($this->app)) {
                $fqcn = $this->app . '\\App\\';
            } else {
                throw new Exception('Please setApp in routes.php');
            }
            $fqcn .= ucfirst($mvcComponentType) . 's\\';
            $fqcn .= strpos($routeType, 'admin') !== false ? 'Admin\\' : 'Frontend\\';
            if (class_exists($fqcn . $class)) {
                return $fqcn . $class;
            }
        }
        return $class;
    }
}
