<?php

namespace PWPF\View;

use function extract;
use function file_exists;
use function is_array;
use function ob_get_clean;
use function ob_start;

/**
 * Class Responsible for Loading Templates
 *
 * @author Sławomir Kaleta <slaszka@gmail.com>
 */
class View extends \Dframe\View\View
{
    /**
     * @var array
     */
    private $config;

    public function init(array $config = [])
    {
        $this->config = $config;
    }

    /**
     * Render Templates
     *
     * @access public
     *
     * @param mixed  $templateName Template file to render.
     * @param array  $args         Variables to make available inside template file.
     * @param string $templatePath Directory to search for template.
     * @param string $defaultPath  Fallback directory to search for template if not found at $templatePath.
     *
     * @return string
     */
    public function render($templateName, $args = [], $templatePath = '', $defaultPath = '')
    {
        $result = $this->renderTemplate($templateName, $args, $templatePath, $defaultPath);
        return $result;
    }

    /**
     * @param        $templateName
     * @param array  $args
     * @param string $templatePath
     * @param string $defaultPath
     *
     * @return false|string|void
     */
    public function renderTemplate($templateName, $args = [], $templatePath = '', $defaultPath = '')
    {
        if ($args && is_array($args)) {
            extract($args);
            // @codingStandardsIgnoreLine.
        }
        if (!empty($this->config['defaultPath'])) {
            $defaultPath = $this->config['defaultPath'];
        }

        $located = $this->locateTemplate($templateName, $templatePath, $defaultPath);
        if (false == $located) {
            return;
        }
        ob_start();
        do_action($this->config['appName'] . '_before_template_render', $templateName, $templatePath, $located, $args);
        include $located;
        do_action($this->config['appName'] . '_after_template_render', $templateName, $templatePath, $located, $args);
        return ob_get_clean();
        // @codingStandardsIgnoreLine.
    }

    /**
     * Locate a template and return the path for inclusion.
     *
     * This is the load order:
     *
     *      yourtheme       /   $templatePath  /   $templateName
     *      yourtheme       /   $templateName
     *      $defaultPath   /   $templateName
     *
     * @access public
     *
     * @param mixed  $templateName Template file to locate.
     * @param string $templatePath $templatePath Directory to search for template.
     * @param string $defaultPath  Fallback directory to search for template if not found at $templatePath.
     *
     * @return string
     */
    public function locateTemplate($templateName, $templatePath = '', $defaultPath = '')
    {
        if (!$templatePath) {
            $templatePath = 'Templates/';
        }
        if (!$defaultPath) {
            $defaultPath = plugin_dir_path(__FILE__) . '../../../../app/Templates/';
        }
        // Look within passed path within the theme - this is priority.
        $template = locate_template([trailingslashit($templatePath) . $templateName, $templateName]);
        // Get default template.
        if (!$template) {
            $template = $defaultPath . $templateName;
        }
        if (file_exists($template)) {
            // Return what we found.
            return apply_filters(
                $this->config['appName'] . '_locate_template',
                $template,
                $templateName,
                $templatePath
            );
        } else {
            return false;
        }
    }
}
