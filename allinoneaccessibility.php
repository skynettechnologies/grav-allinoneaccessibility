<?php
/**
 * @package    Grav\Plugin\Allinoneaccessibility
 *
 * @copyright  Copyright (C) 2023 Skynet Technologies USA LLC
 * @license    MIT License; see LICENSE file for details.
 */
namespace Grav\Plugin;

use Grav\Common\Grav;
use Grav\Common\Utils;
use Grav\Common\Plugin;
use Grav\Common\Data\Data;
use Grav\Common\Data\Blueprints;
use Grav\Common\File\CompiledYamlFile;

use RocketTheme\Toolbox\Event\Event;

use Grav\Plugin\Allinoneaccessibility\Classes\AllinoneManager\AllinoneManager;
use Grav\Plugin\Allinoneaccessibility\Classes\AllinoneConsent\AllinoneConsent;

require_once __DIR__ . '/classes/AllinoneManager/AllinoneManager.php';
require_once __DIR__ . '/classes/AllinoneConsent/AllinoneConsent.php';

/**
 * Class AllinoneaccessibilityPlugin
 * @package Grav\Plugin
 */
class AllinoneaccessibilityPlugin extends Plugin {

    protected $routes = [
                'allinone-manager'
               ];

    /**
     * admin controller
     * @type string
     */
    private $adminController;

    /**
     * admin controller
     * @type string
     */
    private $dataStorageDefault = 'user://data';

    /**
     * @return array
     *
     * The getSubscribedEvents() gives the core a list of events
     *     that the plugin wants to listen to. The key of each
     *     array section is the event that the plugin listens to
     *     and the value (in the form of an array) contains the
     *     callable (or function) as well as the priority. The
     *     higher the number the higher the priority.
     */
    public static function getSubscribedEvents() {
        return [
            'onPluginsInitialized'  => ['onPluginsInitialized',  0],
            'onTwigSiteVariables'   => ['onTwigSiteVariables',   0],
            'onAdminControllerInit' => ['onAdminControllerInit', 0],
        ];
    }

    /**
     * Initialize the plugin
     */
    public function onPluginsInitialized() {

        $this->grav['locator']->addPath('blueprints', '', __DIR__ . DS . 'blueprints');

        // proceed if we are in the admin plugin
        if ($this->isAdmin()) {

            // Enable the main event we are interested in
            $this->enable([
                'onAdminTwigTemplatePaths'  => ['onAdminTwigTemplatePaths',     0],
                'onGetPageTemplates'        => ['onGetPageTemplates',           0],
                'onAdminMenu'               => ['onAdminMenu',                  0],
                'onAdminData'               => ['onAdminData',                  0],
            ]);
        }
        // don't proceed if we are in the admin plugin
        else{
            // Enable the main event we are interested in
            $this->enable([
                'onTwigTemplatePaths'  => ['onTwigTemplatePaths',  0],
            ]);
        }
    }

    /**
     * Push plugin templates to twig paths array.
     * FRONTEND
     */
    public function onTwigTemplatePaths() {
        // Push own templates to twig paths
        array_push($this->grav['twig']->twig_paths,__DIR__ . '/templates');
    }

    /**
     * Add plugin CSS and JS files to the grav assets.
     * FRONTEND
     */
    public function onTwigSiteVariables() {

        $twig = $this->grav['twig'];
        $type = AllinoneManager::getCurrentAllinoneManagerPath();

        if ($this->isAdmin()) {

            if(in_array($type, $this->routes)){
                $vars = AllinoneManager::getAllinoneManagerDataTwigVars();
                $twig->twig_vars = array_merge($twig->twig_vars, $vars);
            }
        }
        else {
            $assets = $this->grav['assets'];

            //array for twig variables of allinoneaccessibility/blueprints.yaml//
            /* Get widget setting data from yaml file */
            $aioaWidegtData = AllinoneConsent::getYamlDataByType('allinone-manager');
            
            if(!$aioaWidegtData){
                $license_key = '';
                $color = '#420083';
                $position = 'bottom_right';
                $icon_type = 'aioa-icon-type-1';
                $icon_size = 'aioa-medium-icon';
            }else{
                $license_key = ($aioaWidegtData['aioa_license_key']) ? ($aioaWidegtData['aioa_license_key']) : '';
                $color = ($aioaWidegtData['aioa_color']) ? ($aioaWidegtData['aioa_color']) : '#420083';
                $position = ($aioaWidegtData['aioa_position']) ? ($aioaWidegtData['aioa_position']) : 'bottom_right';
                $icon_type = ($aioaWidegtData['aioa_icon_type']) ? ($aioaWidegtData['aioa_icon_type']) :'aioa-icon-type-1';
                $icon_size = ($aioaWidegtData['aioa_icon_size']) ? ($aioaWidegtData['aioa_icon_size']) : 'aioa-medium-icon';
            }
            /* set that data in ADA script */
            $assets->addJs('https://www.skynettechnologies.com/accessibility/js/all-in-one-accessibility-js-widget-minify.js?colorcode='.$color.'&token='.$license_key.'&position='.$position.'.'.$icon_type.'.'.$icon_size.'', array('loading' => 'async'));
        }
    }

    /**
     * Add Routes to the custom data pages when the admin menu is being loaded
     */
    public function onAdminMenu() {
        if ($this->isAdmin()) {
            $this->grav['twig']->plugins_hooked_nav['PLUGIN_ALLINONEACCESSIBILITY.ALLINONE_ACCESSIBILITY'] = ['route' => $this->routes[0], 'icon' => 'fa-shield'];
        }
    }

    /**
     * Get admin page template
     */
    public function onAdminTwigTemplatePaths(Event $event) {
        $paths = $event['paths'];
        $paths[] = __DIR__ . DS . 'admin/templates';
        $event['paths'] = $paths;
    }

    /**
     * Add blueprint directory.
     */
    public function onGetPageTemplates(Event $event) {
        $types = $event->types;
        $types->scanBlueprints('plugin://' . $this->name . '/blueprints');
    }

    /**
     * Add additional blueprints data
     */
    public function onAdminControllerInit(Event $event) {
        $controller = $event['controller'];
        $this->adminController = $controller;
    }

    public function onAdminData(Event $event) {
        $type = $event['type']; //current route
        
        // Check if current context is a custom page
        if(in_array($type, $this->routes)) {

            $locator    = Grav::instance()['locator'];
            $blueprint  = AllinoneManager::getCurrentAllinoneManagerBlueprint();
            $obj        = new Data(AllinoneManager::getAllinoneManagerData(), $blueprint);
            $post       = $this->adminController->data;

            //location of yaml files
            $dataStorage = $this->dataStorageDefault;
            
            if($post){
                $obj->merge($post);
                $event['data_type'] = $obj;
                $file = CompiledYamlFile::instance($locator->findResource($dataStorage) . DS .$type. ".yaml");
                $obj->file($file);
                $obj->save();
            }
        }
    }
}
