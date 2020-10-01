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

    /**
     * Creates an JsonQuery object from a PHP structure.
     *
     * @param mixed $data
     *
     * @return JsonQuery
     *
     * @throws InvalidJsonException
     */
    public static function fromData($data)
    {
        // This will force the data structure into the correct PHP representation of a JSON.
        // JavaScript objects will be PHP objects, arrays will be numeric arrays.
        $json = json_encode($data);

        return self::fromJson($json);
    }

    /**
     * Creates a JsonQuery object from a JSON string.
     *
     * @param string $json
     *
     * @return JsonQuery
     *
     * @throws InvalidJsonException
     */
    public static function fromJson(string $json)
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

    /**
     * Creates a JsonQuery from a text file, which needs to contain valid JSON.
     *
     * @param string $filePath
     *
     * @return JsonQuery
     *
     * @throws InvalidJsonException
     */
    public static function fromFile(string $filePath)
    {
        $json = file_get_contents($filePath);

        return self::fromJson($json);
    }

    /**
     * Creates a JsonQuery from a *valid* PHP structure.
     *
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
     * @param mixed $data
     *
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

    /**
     * Executes the query against the JSON PHP data structure.
     *
     * The return values can be very different. Any valid PHP/JSON structure
     * is possible including null. If a field is not found a ValueNotFound object
     * is returned.
     *
     * @param string $field
     * @param null|mixed $context
     *
     * @return mixed
     */
    public function query(string $field, &$context = null)
    {
        // Set Default Context.
        while (true) {
            if (!$context) {
                $context = $this->data;
            }

            // Field to Array, if needed.
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

            foreach ($parts as $i => $part) {
                if (is_array($context) && !is_numeric($part)) {
                    $orgContext =& $context;
                    $result = [];
                    for ($j = 0; $j < count($orgContext); $j++) {
                        $res = $this->query(array_slice($parts, $i), $orgContext[$j]);
                        if ($res instanceof ValueNotFound) {
                            continue;
                        }
                        if (is_array($res) && !count($res)) {
                            ; //
                        } else {
                            $result[$j] = $res;
                        }
                    }

                    return $result;
                } elseif (is_array($context) && is_numeric($part)) {
                    $part = intval($part);
                    if (!isset($context[$part])) {
                        return new ValueNotFound();
                    }

                    $context =& $context[$part];
                } elseif (is_object($context)) {
                    if (!property_exists($context, $part)) {
                        return new ValueNotFound();
                    }

                    $context =& $context->{$part};
                } else {
                    return new ValueNotFound();
                }
            }

            return $context;
        }
    }
}