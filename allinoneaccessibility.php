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
            // Load settings from YAML
            $aioaWidegtData = AllinoneConsent::getYamlDataByType('allinone-manager');
            $license_key = $aioaWidegtData['aioa_license_key'] ?? '';
            $color       = $aioaWidegtData['aioa_color'] ?? '#420083';
            $position    = $aioaWidegtData['aioa_position'] ?? 'bottom_right';
            $icon_type   = $aioaWidegtData['aioa_icon_type'] ?? 'aioa-icon-type-1';
            $icon_size   = $aioaWidegtData['aioa_icon_size'] ?? 'aioa-medium-icon';
            // --------------------------------------
            // DOMAIN
            // --------------------------------------
            $domain = $_SERVER['HTTP_HOST'] ?? '';
            $domain_base64 = base64_encode($domain);
            // --------------------------------------
            // CALL add-user-domain API
            // --------------------------------------
            $apiUrl = 'https://ada.skynettechnologies.us/api/add-user-domain';
            $postData = http_build_query([
                'website' => $domain_base64
            ]);

            $ch = curl_init($apiUrl);
            curl_setopt_array($ch, [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_POST           => true,
                CURLOPT_POSTFIELDS     => $postData,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_SSL_VERIFYPEER => false,
                CURLOPT_SSL_VERIFYHOST => false,
                CURLOPT_TIMEOUT        => 10
            ]);

            $response = curl_exec($ch);
            curl_close($ch);

            $apiResponse = json_decode($response, true);
            $no_required_eu = (int) ($apiResponse['website_data']['no_required_eu'] ?? 1);

            $assets->addInlineJs("
                console.log('ADA API Response:', " . json_encode($apiResponse) . ");
                console.log('ADA no_required_eu:', '{$no_required_eu}');
            ");
            // --------------------------------------
            // SCRIPT LOADING LOGIC
            // --------------------------------------
            if ($no_required_eu == 0) {
                // EU SCRIPT
                $assets->addInlineJs("
                    setTimeout(function () {
                        var aioa = document.createElement('script');
                        aioa.src = 'https://eu.skynettechnologies.com/accessibility/js/all-in-one-accessibility-js-widget-minify.js?colorcode={$color}&token={$license_key}&position={$position}';
                        aioa.id = 'aioa-adawidget';
                        aioa.defer = true;
                        document.body.appendChild(aioa);
                    }, 3000);
                ");
            } else {
                // NORMAL SCRIPT
                $assets->addJs(
                    "https://www.skynettechnologies.com/accessibility/js/all-in-one-accessibility-js-widget-minify.js"
                    . "?colorcode={$color}"
                    . "&token={$license_key}"
                    . "&position={$position}.{$icon_type}.{$icon_size}",
                    [
                        'loading' => 'async',
                        'id' => 'aioa-adawidget'
                    ]
                );
            }
        }
    }

    /**
     * Add Routes to the custom data pages when the admin menu is being loaded
     */
    public function onAdminMenu() {
        if ($this->isAdmin()) {
            $this->grav['twig']->plugins_hooked_nav['PLUGIN_ALLINONEACCESSIBILITY.ALLINONE_ACCESSIBILITY'] = ['route' => $this->routes[0], 'icon' => 'fa-universal-access'];
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

}
