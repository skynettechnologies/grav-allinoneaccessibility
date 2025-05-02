<?php

namespace Grav\Plugin\Allinoneaccessibility\Classes\AllinoneConsent;

use Grav\Common\Grav;
use Grav\Common\Utils;
use Grav\Common\Data\Data;
use Grav\Common\File\CompiledYamlFile;


/**
 * All in One Accessibility Plugin Allinone Manager Class 
 *
 */
class AllinoneConsent extends Data {

    /**
     * get data object of given type 
     *
     * @return object
     */
    public static function getYamlDataByType($type) {

        //location of yaml files
        $dataStorage = 'user://data';

        return CompiledYamlFile::instance(Grav::instance()['locator']->findResource($dataStorage) . DS . $type . ".yaml")->content();
    }

}