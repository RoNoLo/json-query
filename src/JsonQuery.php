<?php

declare(strict_types=1);

namespace RoNoLo\JsonQuery;

use JsonException;

class JsonQuery
{
    /**
     * Creates a JsonQuery object from a PHP structure.
     *
     * @throws InvalidJsonException
     */
    public static function fromData(mixed $data): self
    {
        // This will force the data structure into the correct PHP representation of a JSON.
        // JavaScript objects will be PHP objects, arrays will be numeric arrays.
        try {
            $json = json_encode($data, flags: JSON_THROW_ON_ERROR);
        } catch (JsonException $e) {
            throw new InvalidJsonException($e->getMessage(), $e->getCode(), $e);
        }

        return self::fromJson($json);
    }

    /**
     * Creates a JsonQuery object from a JSON string.
     *
     * @throws InvalidJsonException
     */
    public static function fromJson(string $json): self
    {
        try {
            $data = json_decode($json, flags: JSON_THROW_ON_ERROR);
        } catch (JsonException $e) {
            throw new InvalidJsonException($e->getMessage(), $e->getCode(), $e);
        }

        return new self($data);
    }

    /**
     * Creates a JsonQuery from a text file, which needs to contain valid JSON.
     *
     * @throws InvalidJsonException
     */
    public static function fromFile(string $filePath): self
    {
        $json = @file_get_contents($filePath);

        if ($json === false) {
            throw new InvalidJsonException(sprintf('File "%s" could not be read.', $filePath));
        }

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
     */
    public static function fromValidData(mixed $data): self
    {
        return new self($data);
    }

    protected function __construct(
        protected mixed $data
    ) {
    }

    public function getData(): mixed
    {
        return $this->data;
    }

    /**
     * Executes the query against the JSON PHP data structure.
     *
     * The return values can be very different. Any valid PHP/JSON structure
     * is possible including null. If a field is not found a ValueNotFound object
     * is returned.
     */
    public function query(mixed $field): mixed
    {
        return $this->queryInternal($field, $this->data);
    }

    /**
     * Internal recursive query method.
     */
    private function queryInternal(mixed $field, mixed $context): mixed
    {
        // Field to Array, if needed.
        if (is_int($field) || is_float($field)) {
            $parts = [(string) $field];
        } elseif (is_string($field)) {
            if (trim($field) === '') {
                return $context;
            }
            $parts = explode('.', $field);
        } elseif ($field === null) {
            $parts = [];
        } elseif (is_array($field)) {
            $parts = $field;
        } else {
            return new ValueNotFound();
        }

        foreach ($parts as $i => $part) {
            if (is_array($context) && !is_numeric($part)) {
                $result = [];
                $count = count($context);
                for ($j = 0; $j < $count; $j++) {
                    $res = $this->queryInternal(array_slice($parts, $i), $context[$j]);
                    if ($res instanceof ValueNotFound) {
                        continue;
                    }
                    if (is_array($res) && count($res) === 0) {
                        // ignore empty array result
                    } else {
                        $result[$j] = $res;
                    }
                }

                return $result;
            }

            if (is_array($context) && is_numeric($part)) {
                $part = (int) $part;
                if (!array_key_exists($part, $context)) {
                    return new ValueNotFound();
                }

                $context = $context[$part];
            } elseif (is_object($context)) {
                if (!property_exists($context, (string) $part)) {
                    return new ValueNotFound();
                }

                $context = $context->{(string) $part};
            } else {
                return new ValueNotFound();
            }
        }

        return $context;
    }
}
