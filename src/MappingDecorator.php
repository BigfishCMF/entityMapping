<?php

namespace Bigfish\Component\Mapping;

/**
 * Class MappingDecorator
 *
 * @package Bigfish
 */
class MappingDecorator
{
    /**
     * Get result
     *
     * @return array
     */
    public static function mapEntities($entities = array(), array $mapping = array(), $defaultValues = array())
    {
        $data = array();

        foreach ($entities as $entity) {
            $map = $mapping;

            self::mapEntity($entity, $entity, $map, $defaultValues);

            $data[] = $map;
        }

        return $data;
    }

    /**
     * Map entity
     *
     * @param $array
     * @param $entities
     */
    public static function mapEntity(array $entity, array $originalEntity, array &$mapping, $defaultValues = array())
    {
        self::flatten($entity);

        foreach ($mapping as $key => &$value) {
            if (is_array($value)) {
                self::mapEntity($entity, $originalEntity, $value);
            } elseif (isset($entity[$value])) {
                $mapping[$key] = $entity[$value];
            } else {
                $data = self::unFlatten($originalEntity, $value);

                if ($data === null && $defaultValues && isset($defaultValues[$key])) {
                    $data = $defaultValues[$key];
                }

                $mapping[$key] = $data;
            }
        }
    }

    /**
     * Flattens an nested array of translations.
     *
     * The scheme used is:
     *   'key' => array('key2' => array('key3' => 'value'))
     * Becomes:
     *   'key.key2.key3' => 'value'
     *
     * This function takes an array by reference and will modify it
     *
     * @param array  &$messages The array that will be flattened
     * @param array  $subnode   Current subnode being parsed, used internally for recursive calls
     * @param string $path      Current path being parsed, used internally for recursive calls
     */
    private static function flatten(array &$messages, array $subnode = null, $path = null)
    {
        if (null === $subnode) {
            $subnode = &$messages;
        }

        foreach ($subnode as $key => $value) {
            if (is_array($value)) {
                $nodePath = $path ? $path.'.'.$key : $key;
                self::flatten($messages, $value, $nodePath);

                if (null === $path) {
                    unset($messages[$key]);
                }
            } elseif (null !== $path) {
                $messages[$path.'.'.$key] = $value;
            }
        }
    }

    /**
     * Get an array value from a string
     *
     * Example:
     *  when the string tv_overview_image.image is given
     *  this function will try to find $originalEntity['tv_overview_image']['image']
     *
     * @param array  $originalEntity
     * @param string $string
     *
     * @return mixed
     */
    private static function unFlatten($originalEntity, $string)
    {
        if (strpos($string, '.') === false) {
            return (isset($originalEntity[$string])) ? $originalEntity[$string] : null;
        }

        $parts = explode('.', $string);

        foreach ($parts as $part) {
            if (isset($originalEntity[$part])) {
                $originalEntity = $originalEntity[$part];
                continue;
            }

            $originalEntity = null;
            break;
        }

        return $originalEntity;
    }
}