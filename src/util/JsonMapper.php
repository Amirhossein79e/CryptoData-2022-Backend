<?php
declare(strict_types=1);

namespace crypto\util;
use DateTime;
use Exception;
use JsonParseException;
use ReflectionClass;
use ReflectionProperty;
use ReflectionException;

class JsonMapper
{
    /**
     * This method helps you convert your json to array of specific object by name or name annotation.
     * @param string $jsonString the json string should be parse. {"id":"1"}
     * @param string $classType the class type which json should be mapped to it. Class::class
     * @return object|array returns an array of specific object or an object by json you provided.
     * @throws JsonParseException when json object is not valid or your object class has problem.
     */
    public static function map(string $jsonString, string $classType) : object|array
    {
        try
        {
            $isObject = !str_starts_with(trim($jsonString), '[');
            $jsonArray = json_decode($jsonString,true);
            $refClass = new ReflectionClass($classType);

            if ($isObject)
            {
                return self::mapItem($jsonArray,$refClass);
            }else
            {
                $resultArray = array();

                foreach ($jsonArray as $json)
                {
                    $resultArray[] = self::mapItem($json,$refClass);
                }

                return $resultArray;
            }

        }catch (Exception $exception)
        {
            throw new JsonParseException($exception->getMessage());
        }
    }


    /**
     * it map provided json to an object.
     * @param array $jsonArray json should be converted to object.
     * @param ReflectionClass $reflectedClass reflection object of class for mapping json to it.
     * @return object an instance of class with json values mapped to fields.
     * @throws ReflectionException if a problem occurs when try to use reflection
     */
    private static function mapItem(array $jsonArray,ReflectionClass $reflectedClass) : object
    {
        $objClass = $reflectedClass->newInstance();

        foreach ($reflectedClass->getProperties() as $item)
        {
            if (array_key_exists($item->getName(), $jsonArray) && $jsonArray[$item->getName()] != null)
            {
                $item->setAccessible(true);
                $item->setValue($objClass, self::getModifiedValue($item, $jsonArray[$item->getName()]));

            }else if (array_key_exists(self::getName($item), $jsonArray) && $jsonArray[self::getName($item)] != null)
            {
                $item->setAccessible(true);
                $item->setValue($objClass, self::getModifiedValue($item, $jsonArray[self::getName($item)]));
            }
        }

        return $objClass;
    }


    /** return serialized name of property annotated with name
     * @param ReflectionProperty $property
     * @return string serialized name
     */
    private static function getName(ReflectionProperty $property) : string
    {
        $name = "";

        if ($property->getDocComment() !== false)
        {
            $comment = str_replace("*","",$property->getDocComment());
            $array = preg_split("/\r\n|\n|\r/",$comment);

            foreach ($array as $item)
            {
                $trimmed = trim($item);

                if (str_starts_with($trimmed, "@name"))
                {
                    $array = explode(" ",$trimmed);
                    $name = $array[array_key_last($array)];
                }
            }
        }

        return $name;
    }


    /**
     * this modifies value if needed for mapping.
     * @param ReflectionProperty $property
     * @param $value
     * @return DateTime|object
     * @throws ReflectionException
     */
    private static function getModifiedValue(ReflectionProperty $property, $value) : mixed
    {
        switch ($property->getType())
        {
            case "DateTime":
                return new DateTime($value);
                break;

            case "boolean":
            case "integer":
            case "float":
            case "string":
            case "array":
            case "object":
            case NULL:
                return $value;
                break;

            default:
                if ($property->getType()->isBuiltin())
                {
                    return $value;
                }else
                {
                    $refClass = new ReflectionClass($property->getType()->getName());
                    return self::mapItem($value,$refClass);
                }
        }
    }

}