<?php

namespace Recognize\DwhApplication\Util;


/**
 * Class NameHelper
 * @package Recognize\DwhApplication\Util
 * @author Bart Wesselink <b.wesselink@recognize.nl>
 */
class NameHelper
{
    /**
     * @param string $subject
     * @return string
     */
    public static function camelToSnake(string $subject): string {
        return preg_replace_callback('/[A-Z]/', function ($matches) {
            return sprintf('_%s', strtolower($matches[0]));
        }, $subject);
    }

    /**
     * @param string $subject
     * @return string
     */
    public static function dashToCamel(string $subject): string {
        return str_replace('-', '', ucwords($subject, '-'));
    }

    public static function splitPluralName($name): array {
        $pluralName = $name;
        $singularName = $pluralName;

        if (substr($singularName, -1) !== 's') {
            $pluralName .= 'List';
        } else {
            $singularName = substr($singularName, 0, -1);
        }

        return [$pluralName, $singularName];
    }
}
