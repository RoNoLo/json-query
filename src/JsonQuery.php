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
        // This will force the data structure into the correct PHP representation of a JSON.
        // JavaScript objects will be PHP objects, arrays will be numeric arrays.
        $json = json_encode($data);

        return self::fromJson($json);
    }

    public static function fromJson($json)
    {
        $data = json_decode($json);

        if (is_null($data)) {
            $error = json_last_error();
            $message = json_last_error_msg();

            if ($error !== JSON_ERROR_NONE) {
                throw new InvalidJsonException($message, $error);
            }
        }

        return new self($data);
    }

    public static function fromFile($filePath)
    {
        $json = file_get_contents($filePath);

        return self::fromJson($json);
    }

    /**
     * Use this method with caution. You can skip two encode/decode turns, which is
     * a time saver. Use only with PHP data structures which are saved correctly.
     *
     * Example:
     * You could do the following, to save correct PHP data, which will not fail with
     * JsonQuery in the process later.
     *
     * $q = JsonQuery::fromJson('some JSON string');
     * file_put_contents('foo.php', '<?php return ' . var_export($q->getData(), true) . ';');
     *
     * @param $data
     * @return JsonQuery
     */
    public static function fromValidData($data)
    {
        return new self($data);
    }

    protected function __construct($data)
    {
        $this->data = $data;
    }

    public function getData()
    {
        return $this->data;
    }

    public function getNestedProperty($field, &$context = null)
    {
        if (!$context) {
            $context = $this->data;
        }

        if (is_string($field)) {
            if (trim($field) == '') {
                return $context;
            }
            $parts = explode('.', $field);
        } elseif (is_null($field)) {
            $parts = [];
        } else {
            $parts = $field;
        }

        foreach ($parts as $idx => $part) {
            if (trim($part) == '') {
                return new ValueNotFound();
            }

            // Will handle foo.2.bar querys
            if (is_numeric($part) && is_array($context)) {
                return $this->getNestedProperty(array_slice($parts, $idx + 1), $context[intval($part)]);
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
                    if (is_array($res) && !count($res)) {
                        ; //
                    } else {
                        $result[$i] = $res;
                    }
                }
                return $result;
            } else {
                return new ValueNotFound();
            }
        }

        return $context;
    }
}