<?php

/*
 * This file is part of the webmozart/key-value-store package.
 *
 * (c) Bernhard Schussek <bschussek@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Webmozart\KeyValueStore;

use stdClass;
use Webmozart\Assert\Assert;
use Webmozart\KeyValueStore\Api\CountableStore;
use Webmozart\KeyValueStore\Api\NoSuchKeyException;
use Webmozart\KeyValueStore\Api\ReadException;
use Webmozart\KeyValueStore\Api\SortableStore;
use Webmozart\KeyValueStore\Api\UnsupportedValueException;
use Webmozart\KeyValueStore\Api\WriteException;
use Webmozart\KeyValueStore\Util\KeyUtil;
use Webmozart\KeyValueStore\Util\Serializer;

/**
 * A key-value store backed by a JSON file.
 *
 * @since  1.0
 *
 * @author Bernhard Schussek <bschussek@gmail.com>
 */
class JsonFileStore implements SortableStore, CountableStore
{
    /**
     * Flag: Disable serialization of strings
     */
    const NO_SERIALIZE_STRINGS = 1;

    /**
     * Flag: Disable serialization of arrays
     */
    const NO_SERIALIZE_ARRAYS = 2;

    /**
     * This seems to be the biggest float supported by json_encode()/json_decode().
     */
    const MAX_FLOAT = 1.0E+14;

    /**
     * @var string
     */
    private $path;

    /**
     * @var int
     */
    private $flags;

    public function __construct($path, $flags = 0)
    {
        Assert::string($path, 'The path must be a string. Got: %s');
        Assert::notEmpty($path, 'The path must not be empty.');
        Assert::integer($flags, 'The flags must be an integer. Got: %s');

        $this->path = $path;
        $this->flags = $flags;
    }

    /**
     * {@inheritdoc}
     */
    public function set($key, $value)
    {
        KeyUtil::validate($key);

        if (is_float($value) && $value > self::MAX_FLOAT) {
            throw new UnsupportedValueException('The JSON file store cannot handle floats larger than 1.0E+14.');
        }

        $data = $this->load();
        $data[$key] = $this->serializeValue($value);

        $this->save($data);
    }

    /**
     * {@inheritdoc}
     */
    public function get($key, $default = null)
    {
        KeyUtil::validate($key);

        $data = $this->load();

        if (!array_key_exists($key, $data)) {
            return $default;
        }

        return $this->unserializeValue($data[$key]);
    }

    /**
     * {@inheritdoc}
     */
    public function getOrFail($key)
    {
        KeyUtil::validate($key);

        $data = $this->load();

        if (!array_key_exists($key, $data)) {
            throw NoSuchKeyException::forKey($key);
        }

        return $this->unserializeValue($data[$key]);
    }

    /**
     * {@inheritdoc}
     */
    public function getMultiple(array $keys, $default = null)
    {
        $values = array();
        $data = $this->load();

        foreach ($keys as $key) {
            KeyUtil::validate($key);

            if (array_key_exists($key, $data)) {
                $value = $this->unserializeValue($data[$key]);
            } else {
                $value = $default;
            }

            $values[$key] = $value;
        }

        return $values;
    }

    /**
     * {@inheritdoc}
     */
    public function getMultipleOrFail(array $keys)
    {
        $values = array();
        $data = $this->load();

        foreach ($keys as $key) {
            KeyUtil::validate($key);

            if (!array_key_exists($key, $data)) {
                throw NoSuchKeyException::forKey($key);
            }

            $values[$key] = $this->unserializeValue($data[$key]);
        }

        return $values;
    }

    /**
     * {@inheritdoc}
     */
    public function remove($key)
    {
        KeyUtil::validate($key);

        $data = $this->load();

        if (!array_key_exists($key, $data)) {
            return false;
        }

        unset($data[$key]);

        $this->save($data);

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function exists($key)
    {
        KeyUtil::validate($key);

        $data = $this->load();

        return array_key_exists($key, $data);
    }

    /**
     * {@inheritdoc}
     */
    public function clear()
    {
        $this->save(new stdClass());
    }

    /**
     * {@inheritdoc}
     */
    public function keys()
    {
        return array_keys($this->load());
    }

    /**
     * {@inheritdoc}
     */
    public function sort($flags = SORT_REGULAR)
    {
        $data = $this->load();

        ksort($data, $flags);

        $this->save($data);
    }

    /**
     * {@inheritdoc}
     */
    public function count()
    {
        $data = $this->load();

        return count($data);
    }

    private function load()
    {
        $contents = file_exists($this->path)
            ? trim($this->readFile($this->path))
            : null;

        if (false === (bool) $contents) {
            return array();
        }

        $decoded = json_decode($contents, true);

        if (JSON_ERROR_NONE !== ($error = json_last_error())) {
            throw new ReadException(sprintf(
                'Could not decode JSON data: %s',
                self::getErrorMessage($error)
            ));
        }

        return $decoded;
    }

    private function save($data)
    {
        if (!file_exists($dir = dirname($this->path))) {
            mkdir($dir, 0777, true);
        }

        $encoded = json_encode($data);

        if (JSON_ERROR_NONE !== ($error = json_last_error())) {
            if (JSON_ERROR_UTF8 === $error) {
                throw UnsupportedValueException::forType('binary', $this);
            }

            throw new WriteException(sprintf(
                'Could not encode data as JSON: %s',
                self::getErrorMessage($error)
            ));
        }

        $this->writeFile($this->path, $encoded);
    }

    /**
     * Returns the error message of a JSON error code.
     *
     * Needed for PHP < 5.5, where `json_last_error_msg()` is not available.
     *
     * @param int $error The error code.
     *
     * @return string The error message.
     */
    private function getErrorMessage($error)
    {
        switch ($error) {
            case JSON_ERROR_NONE:
                return 'JSON_ERROR_NONE';
            case JSON_ERROR_DEPTH:
                return 'JSON_ERROR_DEPTH';
            case JSON_ERROR_STATE_MISMATCH:
                return 'JSON_ERROR_STATE_MISMATCH';
            case JSON_ERROR_CTRL_CHAR:
                return 'JSON_ERROR_CTRL_CHAR';
            case JSON_ERROR_SYNTAX:
                return 'JSON_ERROR_SYNTAX';
            case JSON_ERROR_UTF8:
                return 'JSON_ERROR_UTF8';
        }

        if (version_compare(PHP_VERSION, '5.5.0', '>=')) {
            switch ($error) {
                case JSON_ERROR_RECURSION:
                    return 'JSON_ERROR_RECURSION';
                case JSON_ERROR_INF_OR_NAN:
                    return 'JSON_ERROR_INF_OR_NAN';
                case JSON_ERROR_UNSUPPORTED_TYPE:
                    return 'JSON_ERROR_UNSUPPORTED_TYPE';
            }
        }

        return 'JSON_ERROR_UNKNOWN';
    }

    private function writeFile($path, $data)
    {
        $errorMessage = null;
        $errorCode = 0;

        set_error_handler(function ($errno, $errstr) use (&$errorMessage, &$errorCode) {
            $errorMessage = $errstr;
            $errorCode = $errno;
        });

        file_put_contents($path, $data);

        restore_error_handler();

        if (null !== $errorMessage) {
            if (false !== $pos = strpos($errorMessage, '): ')) {
                // cut "file_put_contents(%path%):" to make message more readable
                $errorMessage = substr($errorMessage, $pos + 3);
            }

            throw new WriteException(sprintf(
                'Could not write %s: %s (%s)',
                $path,
                $errorMessage,
                $errorCode
            ), $errorCode);
        }
    }

    private function readFile($path)
    {
        $errorMessage = null;
        $errorCode = 0;

        set_error_handler(function ($errno, $errstr) use (&$errorMessage, &$errorCode) {
            $errorMessage = $errstr;
            $errorCode = $errno;
        });

        $data = file_get_contents($path);

        restore_error_handler();

        if (null !== $errorMessage) {
            if (false !== $pos = strpos($errorMessage, '): ')) {
                // cut "file_get_contents(%path%):" to make message more readable
                $errorMessage = substr($errorMessage, $pos + 3);
            }

            throw new ReadException(sprintf(
                'Could not read %s: %s (%s)',
                $path,
                $errorMessage,
                $errorCode
            ), $errorCode);
        }

        return $data;
    }

    private function serializeValue($value)
    {
        // Serialize if we have a string and string serialization is enabled...
        $serializeValue = (is_string($value) && !($this->flags & self::NO_SERIALIZE_STRINGS))
            // or we have an array and array serialization is enabled...
            || (is_array($value) && !($this->flags & self::NO_SERIALIZE_ARRAYS))
            // or we have any other non-scalar value
            || (!is_scalar($value) && !is_array($value));

        if ($serializeValue) {
            return Serializer::serialize($value);
        }

        // If we have an array and array serialization is disabled, serialize
        // its entries if necessary
        if (is_array($value)) {
            return array_map(array($this, 'serializeValue'), $value);
        }

        return $value;
    }

    private function unserializeValue($value)
    {
        // Unserialize value if it is a string...
        $unserializeValue = is_string($value) && (
            // and string serialization is enabled
            !($this->flags & self::NO_SERIALIZE_STRINGS)
            // or the string contains a serialized object
            || 'O:' === ($prefix = substr($value, 0, 2))
            // or the string contains a serialized array when array
            // serialization is enabled
            || ('a:' === $prefix && !($this->flags & self::NO_SERIALIZE_ARRAYS))
        );

        if ($unserializeValue) {
            return Serializer::unserialize($value);
        }

        if (is_array($value)) {
            return array_map(array($this, 'unserializeValue'), $value);
        }

        return $value;
    }
}
