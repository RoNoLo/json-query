<?php

namespace RoNoLo\JsonQuery;

/**
 * JsonQuery
 *
 * @package RoNoLo\JsonQuery
 */
class JsonQuery
{
    /** @var mixed */
    protected $data;

    public static function fromData($data)
    {
        // This will force Exceptions, when objects inside the data structure do not implement \JsonSerialize and
        // will also force that array maps will be converted to objects, which is an internal requirement to work
        // correctly with Json objects.
        $json = json_encode($data);

        return self::fromJson($json);
    }

    public static function fromJson($json)
    {
        $data = json_decode($json);

        return new self($data);
    }

    public static function fromFile($filePath)
    {
        $json = file_get_contents($filePath);

        return self::fromJson($json);
    }

    protected function __construct($data)
    {
        $this->data = $data;
    }

    public function getNestedProperty($field, &$context = null)
    {
        if (!$context) {
            $context = $this->data;
        }

        if (is_string($field)) {
            $parts = array_filter(explode('.', $field));
        } elseif (is_null($field)) {
            $parts = [];
        } else {
            $parts = $field;
        }

        foreach ($parts as $idx => $part) {
            // Will handle foo.2.bar querys
            if (is_numeric($part) && is_array($context)) {
                return $this->getNestedProperty(array_slice($parts, $idx + 1), $context[intval($part)]);
            }

            if (trim($part) == '') {
                return new ValueNotFound();
            }

            if (is_object($context)) {
                if (!property_exists($context, $part)) {
                    return new ValueNotFound();
                }

                $context = $context->{$part};
            } elseif (is_array($context)) {
                $length = count($context);
                $result = [];
                for ($i = 0; $i < $length; $i++) {
                    $res = $this->getNestedProperty(array_slice($parts, $idx), $context[$i]);
                    if ($res instanceof ValueNotFound) {
                        continue;
                    }
                    $result[$i] = $res;
                }
                return $result;
            } else {
                return new ValueNotFound();
            }
        }

        return $context;
    }
}