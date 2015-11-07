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
use Webmozart\Json\DecodingFailedException;
use Webmozart\Json\EncodingFailedException;
use Webmozart\Json\FileNotFoundException;
use Webmozart\Json\IOException;
use Webmozart\Json\JsonDecoder;
use Webmozart\Json\JsonEncoder;
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

    /**
     * @var JsonEncoder
     */
    private $encoder;

    /**
     * @var JsonDecoder
     */
    private $decoder;

    public function __construct($path, $flags = 0)
    {
        Assert::string($path, 'The path must be a string. Got: %s');
        Assert::notEmpty($path, 'The path must not be empty.');
        Assert::integer($flags, 'The flags must be an integer. Got: %s');

        $this->path = $path;
        $this->flags = $flags;

        $this->encoder = new JsonEncoder();

        $this->decoder = new JsonDecoder();
        $this->decoder->setObjectDecoding(JsonDecoder::ASSOC_ARRAY);
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
        try {
            return $this->decoder->decodeFile($this->path);
        } catch (FileNotFoundException $e) {
            return array();
        } catch (DecodingFailedException $e) {
            throw new ReadException($e->getMessage(), 0, $e);
        } catch (IOException $e) {
            throw new ReadException($e->getMessage(), 0, $e);
        }
    }

    private function save($data)
    {
        try {
            $this->encoder->encodeFile($data, $this->path);
        } catch (EncodingFailedException $e) {
            if (JSON_ERROR_UTF8 === $e->getCode()) {
                throw UnsupportedValueException::forType('binary', $this);
            }

            throw new WriteException($e->getMessage(), 0, $e);
        } catch (IOException $e) {
            throw new WriteException($e->getMessage(), 0, $e);
        }
    }

    private function serializeValue($value)
    {
        // Serialize if we have a string and string serialization is enabled...
        $serializeValue = (is_string($value) && !($this->flags & self::NO_SERIALIZE_STRINGS))
            // or we have an array and array serialization is enabled...
            || (is_array($value) && !($this->flags & self::NO_SERIALIZE_ARRAYS))
            // or we have any other non-scalar, non-null value
            || (null !== $value && !is_scalar($value) && !is_array($value));

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
