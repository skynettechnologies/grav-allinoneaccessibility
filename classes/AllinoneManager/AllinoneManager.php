<?php

namespace Grav\Plugin\Allinoneaccessibility\Classes\AllinoneManager;

use Grav\Common\Grav;
use Grav\Common\Utils;
use Grav\Common\Data\Data;
use Grav\Common\Data\Blueprints;
use Grav\Common\File\CompiledYamlFile;

/**
 * All in One Accessibility Plugin All in One Class
 *
 */
class AllinoneManager extends Data {

    /**
     * Get the All in One data list from user/data/ yaml files
     *
     * @return array
     */
    public static function getAllinoneManagerData() {
       
        $aioWidgetData = self::getYamlDataObjType(self::getCurrentAllinoneManagerPath());

        return $aioWidgetData;
    }

    /**
     * Get the allinone manager twig vars
     *
     * @return array
     */
    public static function getAllinoneManagerDataTwigVars() {

        $vars = [];

        $blueprints = self::getCurrentAllinoneManagerBlueprint();
        $content = self::getAllinoneManagerData();

        $aioWidgetData  = new Data($content, $blueprints);

        $vars['aioWidgetData'] = $aioWidgetData;

        return $vars;
    }

    /**
     * get current allinone manager blueprint
     *
     * @return string
     */
    public static function getCurrentAllinoneManagerBlueprint() {

        $blueprints = new Blueprints;
        $currentAllinoneManagerBlueprint = $blueprints->get(self::getCurrentAllinoneManagerPath());

        return $currentAllinoneManagerBlueprint;
    }

    /**
     * get current path of allinone manager for config info
     *
     * @return string
     */
    public static function getCurrentAllinoneManagerPath() {

        $uri = Grav::instance()['uri'];
        $currentAllinoneManagerPath = 'allinone-manager';

        if(isset($uri->paths()[1])){
            $currentAllinoneManagerPath = $uri->paths()[1];
        }

        return $currentAllinoneManagerPath;
    }

    /**
     * get data object of given type
     *
     * @return object
     */
    public static function getYamlDataObjType($type) {

        //location of yaml files
        $dataStorage = 'user://data';

        return CompiledYamlFile::instance(Grav::instance()['locator']->findResource($dataStorage) . DS . $type . ".yaml")->content();
    }

    

}
