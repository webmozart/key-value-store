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

use Exception;
use stdClass;
use Webmozart\KeyValueStore\Api\KeyValueStore;
use Webmozart\KeyValueStore\Api\ReadException;
use Webmozart\KeyValueStore\Api\SerializationFailedException;
use Webmozart\KeyValueStore\Api\WriteException;
use Webmozart\KeyValueStore\Api\UnsupportedValueException;
use Webmozart\KeyValueStore\Assert\Assert;
use Webmozart\KeyValueStore\Util\Serializer;

/**
 * A key-value store backed by a JSON file.
 *
 * @since  1.0
 * @author Bernhard Schussek <bschussek@gmail.com>
 */
class JsonFileStore implements KeyValueStore
{
    /**
     * This seems to be the biggest float supported by json_encode()/json_decode()
     */
    const MAX_FLOAT = 1.0E+14;

    private $path;

    public function __construct($path)
    {
        Assert::string($path, 'The path must be a string. Got: %s');
        Assert::notEmpty($path, 'The path must not be empty.');

        $this->path = $path;
    }

    /**
     * {@inheritdoc}
     */
    public function set($key, $value)
    {
        Assert::key($key);

        if (is_float($value) && $value > self::MAX_FLOAT) {
            throw new UnsupportedValueException('The JSON file store cannot handle floats larger than 1.0E+14.');
        }

        if (!is_scalar($value) || is_string($value)) {
            $value = Serializer::serialize($value);
        }

        $data = $this->load();
        $data->$key = $value;

        $this->save($data);
    }

    /**
     * {@inheritdoc}
     */
    public function get($key, $default = null)
    {
        Assert::key($key);

        $data = $this->load();

        if (!property_exists($data, $key)) {
            return $default;
        }

        $value = $data->$key;

        if (is_string($value)) {
            $value = Serializer::unserialize($value);
        }

        return $value;
    }

    /**
     * {@inheritdoc}
     */
    public function remove($key)
    {
        Assert::key($key);

        $data = $this->load();

        if (!property_exists($data, $key)) {
            return false;
        }

        unset($data->$key);

        $this->save($data);

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function has($key)
    {
        Assert::key($key);

        $data = $this->load();

        return property_exists($data, $key);
    }

    /**
     * {@inheritdoc}
     */
    public function clear()
    {
        $this->save(new stdClass());
    }

    private function load()
    {
        $contents = file_exists($this->path)
            ? trim($this->readFile($this->path))
            : null;

        if (!$contents) {
            return new stdClass();
        }

        $decoded = json_decode($contents);

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
}
