<?php

namespace PWPF\Controller;

use PWPF\Model\Model;
use PWPF\Registry\ControllerRegistry;
use PWPF\View\View;
/**
 * Abstract class to define/implement base methods for all controller classes
 *
 * @author Sławomir Kaleta <slaszka@gmail.com>
 */
abstract class Controller extends \Dframe\Loader
{

    /**
     * Get model.
     *
     * In most of the cases, the model will be set as per routes in defined in routes.php.
     * So if you are not sure which model class is currently being used, search for the
     * current controller class name in the routes.php
     *
     * @return object
     */
    protected function get_model()
    {
        return $this->model;
    }
    /**
     * Sets the model to be used
     *
     * @param Model $model Model object to be associated with the current controller object.
     *
     * @return void
     */
    protected function set_model(Model $model)
    {
        $this->model = $model;
    }
    /**
     * Get view
     *
     * In most of the cases, the view will be set as per routes in defined in routes.php.
     * So if you are not sure which view class is currently being used, search for the
     * current controller class name in the routes.php
     *
     * @return object
     */
    protected function get_view()
    {
        return $this->view;
    }
    /**
     * Sets the view to be used
     *
     * @param View $view View object to be associated with the current controller object.
     *
     * @return void
     */
    protected function set_view(View $view)
    {
        $this->view = $view;
    }
}
