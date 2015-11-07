<?php

/*
 * This file is part of the webmozart/key-value-store package.
 *
 * (c) Bernhard Schussek <bschussek@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Webmozart\KeyValueStore\Tests;

use stdClass;
use Symfony\Component\Filesystem\Filesystem;
use Webmozart\KeyValueStore\JsonFileStore;

/**
 * @since  1.0
 *
 * @author Bernhard Schussek <bschussek@gmail.com>
 */
class JsonFileStoreTest extends AbstractSortableCountableStoreTest
{
    private $tempDir;

    protected function setUp()
    {
        while (false === @mkdir($this->tempDir = sys_get_temp_dir().'/webmozart-JsonFileStoreTest'.rand(10000, 99999), 0777, true)) {
        }

        parent::setUp();
    }

    protected function tearDown()
    {
        parent::tearDown();

        $filesystem = new Filesystem();

        // Ensure all files in the directory are writable before removing
        $filesystem->chmod($this->tempDir, 0755, 0000, true);
        $filesystem->remove($this->tempDir);
    }

    protected function createStore()
    {
        return new JsonFileStore($this->tempDir.'/data.json');
    }

    public function provideScalarValues()
    {
        $values = parent::provideScalarValues();
        $values[] = array(JsonFileStore::MAX_FLOAT);

        return $values;
    }

    public function testCreateMissingDirectoriesOnDemand()
    {
        $store = new JsonFileStore($this->tempDir.'/new/data.json');
        $store->set('foo', 'bar');

        $this->assertFileExists($this->tempDir.'/new/data.json');
    }

    /**
     * @dataProvider provideScalarValues
     */
    public function testSetSupportsScalarValues($value)
    {
        if (is_float($value) && $value > JsonFileStore::MAX_FLOAT) {
            $this->setExpectedException('\Webmozart\KeyValueStore\Api\UnsupportedValueException');
        }

        parent::testSetSupportsScalarValues($value);
    }

    /**
     * @dataProvider provideBinaryValues
     */
    public function testSetSupportsBinaryValues($value)
    {
        // JSON cannot handle binary data
        $this->setExpectedException('\Webmozart\KeyValueStore\Api\UnsupportedValueException');

        parent::testSetSupportsBinaryValues($value);
    }

    /**
     * @expectedException \Webmozart\KeyValueStore\Api\WriteException
     * @expectedExceptionMessage Permission denied
     */
    public function testSetThrowsWriteExceptionIfWriteFails()
    {
        file_put_contents($readOnlyFile = $this->tempDir.'/read-only.json', '{}');
        $store = new JsonFileStore($readOnlyFile);

        chmod($readOnlyFile, 0400);
        $store->set('foo', 'bar');
    }

    public function testSetDoesNotSerializeStringsIfDisabled()
    {
        $store = new JsonFileStore($this->tempDir.'/data.json', JsonFileStore::NO_SERIALIZE_STRINGS);

        $store->set('foo', 'bar');
        $store->set('baz', $array = array(1, 2, new stdClass()));
        $store->set('bam', $object = (object) array('hi' => 'ho'));

        $this->assertSame('{"foo":"bar","baz":"a:3:{i:0;i:1;i:1;i:2;i:2;O:8:\"stdClass\":0:{}}","bam":"O:8:\"stdClass\":1:{s:2:\"hi\";s:2:\"ho\";}"}', file_get_contents($this->tempDir.'/data.json'));
        $this->assertSame('bar', $store->get('foo'));
        $this->assertSame('bar', $store->getOrFail('foo'));
        $this->assertEquals($array, $store->get('baz'));
        $this->assertEquals($array, $store->getOrFail('baz'));
        $this->assertEquals($object, $store->get('bam'));
        $this->assertEquals($object, $store->getOrFail('bam'));
        $this->assertEquals(array('foo' => 'bar', 'bam' => $object), $store->getMultiple(array('foo', 'bam')));
        $this->assertEquals(array('foo' => 'bar', 'bam' => $object), $store->getMultipleOrFail(array('foo', 'bam')));
    }

    public function testSetDoesNotSerializeArrayssIfDisabled()
    {
        $store = new JsonFileStore($this->tempDir.'/data.json', JsonFileStore::NO_SERIALIZE_ARRAYS);

        $store->set('foo', 'bar');
        $store->set('baz', $array = array(1, 2, array(3, new stdClass())));
        $store->set('bam', $object = (object) array('hi' => 'ho'));

        $this->assertSame('{"foo":"s:3:\"bar\";","baz":[1,2,[3,"O:8:\"stdClass\":0:{}"]],"bam":"O:8:\"stdClass\":1:{s:2:\"hi\";s:2:\"ho\";}"}', file_get_contents($this->tempDir.'/data.json'));
        $this->assertSame('bar', $store->get('foo'));
        $this->assertSame('bar', $store->getOrFail('foo'));
        $this->assertEquals($array, $store->get('baz'));
        $this->assertEquals($array, $store->getOrFail('baz'));
        $this->assertEquals($object, $store->get('bam'));
        $this->assertEquals($object, $store->getOrFail('bam'));
        $this->assertEquals(array('foo' => 'bar', 'bam' => $object), $store->getMultiple(array('foo', 'bam')));
        $this->assertEquals(array('foo' => 'bar', 'bam' => $object), $store->getMultipleOrFail(array('foo', 'bam')));
    }

    public function testSetDoesNotSerializeNull()
    {
        $store = new JsonFileStore($this->tempDir.'/data.json', JsonFileStore::NO_SERIALIZE_ARRAYS);

        $store->set('foo', null);

        $this->assertSame('{"foo":null}', file_get_contents($this->tempDir.'/data.json'));
        $this->assertNull($store->get('foo'));
    }

    public function testEscapeGtLt()
    {
        $store = new JsonFileStore($this->tempDir.'/data.json', JsonFileStore::ESCAPE_GT_LT | JsonFileStore::NO_SERIALIZE_STRINGS);

        $store->set('foo<>', 'bar<>');

        $this->assertSame('{"foo\u003C\u003E":"bar\u003C\u003E"}', file_get_contents($this->tempDir.'/data.json'));
        $this->assertSame('bar<>', $store->get('foo<>'));
    }

    public function testNoEscapeGtLt()
    {
        $store = new JsonFileStore($this->tempDir.'/data.json', JsonFileStore::NO_SERIALIZE_STRINGS);

        $store->set('foo<>', 'bar<>');

        $this->assertSame('{"foo<>":"bar<>"}', file_get_contents($this->tempDir.'/data.json'));
        $this->assertSame('bar<>', $store->get('foo<>'));
    }

    public function testEscapeAmpersand()
    {
        $store = new JsonFileStore($this->tempDir.'/data.json', JsonFileStore::ESCAPE_AMPERSAND | JsonFileStore::NO_SERIALIZE_STRINGS);

        $store->set('foo&', 'bar&');

        $this->assertSame('{"foo\u0026":"bar\u0026"}', file_get_contents($this->tempDir.'/data.json'));
        $this->assertSame('bar&', $store->get('foo&'));
    }

    public function testNoEscapeAmpersand()
    {
        $store = new JsonFileStore($this->tempDir.'/data.json', JsonFileStore::NO_SERIALIZE_STRINGS);

        $store->set('foo&', 'bar&');

        $this->assertSame('{"foo&":"bar&"}', file_get_contents($this->tempDir.'/data.json'));
        $this->assertSame('bar&', $store->get('foo&'));
    }

    public function testEscapeSingleQuote()
    {
        $store = new JsonFileStore($this->tempDir.'/data.json', JsonFileStore::ESCAPE_SINGLE_QUOTE | JsonFileStore::NO_SERIALIZE_STRINGS);

        $store->set('foo\'', 'bar\'');

        $this->assertSame('{"foo\u0027":"bar\u0027"}', file_get_contents($this->tempDir.'/data.json'));
        $this->assertSame('bar\'', $store->get('foo\''));
    }

    public function testNoEscapeSingleQuote()
    {
        $store = new JsonFileStore($this->tempDir.'/data.json', JsonFileStore::NO_SERIALIZE_STRINGS);

        $store->set('foo\'', 'bar\'');

        $this->assertSame('{"foo\'":"bar\'"}', file_get_contents($this->tempDir.'/data.json'));
        $this->assertSame('bar\'', $store->get('foo\''));
    }

    public function testEscapeDoubleQuote()
    {
        $store = new JsonFileStore($this->tempDir.'/data.json', JsonFileStore::ESCAPE_DOUBLE_QUOTE | JsonFileStore::NO_SERIALIZE_STRINGS);

        $store->set('foo"', 'bar"');

        $this->assertSame('{"foo\u0022":"bar\u0022"}', file_get_contents($this->tempDir.'/data.json'));
        $this->assertSame('bar"', $store->get('foo"'));
    }

    public function testNoEscapeDoubleQuote()
    {
        $store = new JsonFileStore($this->tempDir.'/data.json', JsonFileStore::NO_SERIALIZE_STRINGS);

        $store->set('foo"', 'bar"');

        $this->assertSame('{"foo\\"":"bar\\""}', file_get_contents($this->tempDir.'/data.json'));
        $this->assertSame('bar"', $store->get('foo"'));
    }

    public function testEscapeSlash()
    {
        $store = new JsonFileStore($this->tempDir.'/data.json', JsonFileStore::NO_SERIALIZE_STRINGS);

        $store->set('foo/', 'bar/');

        $this->assertSame('{"foo\\/":"bar\\/"}', file_get_contents($this->tempDir.'/data.json'));
        $this->assertSame('bar/', $store->get('foo/'));
    }

    public function testNoEscapeSlash()
    {
        $store = new JsonFileStore($this->tempDir.'/data.json', JsonFileStore::NO_ESCAPE_SLASH | JsonFileStore::NO_SERIALIZE_STRINGS);

        $store->set('foo/', 'bar/');

        $this->assertSame('{"foo/":"bar/"}', file_get_contents($this->tempDir.'/data.json'));
        $this->assertSame('bar/', $store->get('foo/'));
    }

    public function testEscapeUnicode()
    {
        $store = new JsonFileStore($this->tempDir.'/data.json', JsonFileStore::NO_SERIALIZE_STRINGS);

        $store->set('fooäöü', 'baräöü');

        $this->assertSame('{"foo\u00e4\u00f6\u00fc":"bar\u00e4\u00f6\u00fc"}', file_get_contents($this->tempDir.'/data.json'));
        $this->assertSame('baräöü', $store->get('fooäöü'));
    }

    public function testNoEscapeUnicode()
    {
        $store = new JsonFileStore($this->tempDir.'/data.json', JsonFileStore::NO_ESCAPE_UNICODE | JsonFileStore::NO_SERIALIZE_STRINGS);

        $store->set('fooäöü', 'baräöü');

        $this->assertSame('{"fooäöü":"baräöü"}', file_get_contents($this->tempDir.'/data.json'));
        $this->assertSame('baräöü', $store->get('fooäöü'));
    }

    public function testPrettyPrint()
    {
        if (PHP_VERSION_ID < 50400) {
            $this->markTestSkipped('Pretty printing is not supported before PHP 5.4.');
        }

        $store = new JsonFileStore($this->tempDir.'/data.json', JsonFileStore::PRETTY_PRINT | JsonFileStore::NO_SERIALIZE_STRINGS);

        $store->set('foo', 'bar');

        $this->assertSame("{\n    \"foo\": \"bar\"\n}", file_get_contents($this->tempDir.'/data.json'));
        $this->assertSame('bar', $store->get('foo'));
    }

    public function testTerminateWithLineFeed()
    {
        $store = new JsonFileStore($this->tempDir.'/data.json', JsonFileStore::TERMINATE_WITH_LINE_FEED | JsonFileStore::NO_SERIALIZE_STRINGS);

        $store->set('foo', 'bar');

        $this->assertSame("{\"foo\":\"bar\"}\n", file_get_contents($this->tempDir.'/data.json'));
        $this->assertSame('bar', $store->get('foo'));
    }

    /**
     * @expectedException \Webmozart\KeyValueStore\Api\WriteException
     * @expectedExceptionMessage Permission denied
     */
    public function testRemoveThrowsWriteExceptionIfWriteFails()
    {
        file_put_contents($readOnlyFile = $this->tempDir.'/read-only.json', '{}');
        $store = new JsonFileStore($readOnlyFile);
        $store->set('foo', 'bar');

        chmod($readOnlyFile, 0400);
        $store->remove('foo');
    }

    /**
     * @expectedException \Webmozart\KeyValueStore\Api\WriteException
     * @expectedExceptionMessage Permission denied
     */
    public function testClearThrowsWriteExceptionIfWriteFails()
    {
        file_put_contents($readOnlyFile = $this->tempDir.'/read-only.json', '{}');
        $store = new JsonFileStore($readOnlyFile);

        chmod($readOnlyFile, 0400);
        $store->clear();
    }

    /**
     * @expectedException \Webmozart\KeyValueStore\Api\ReadException
     * @expectedExceptionMessage Permission denied
     */
    public function testGetThrowsReadExceptionIfReadFails()
    {
        if ('\\' === DIRECTORY_SEPARATOR) {
            $this->markTestSkipped('Cannot deny read access on Windows.');
        }

        touch($notReadable = $this->tempDir.'/not-readable.json');
        $store = new JsonFileStore($notReadable);

        chmod($notReadable, 0000);
        $store->get('key');
    }

    /**
     * @expectedException \Webmozart\KeyValueStore\Api\ReadException
     * @expectedExceptionMessage Expected one of:
     */
    public function testGetThrowsReadExceptionIfInvalidJsonSyntax()
    {
        file_put_contents($invalid = $this->tempDir.'/data.json', '{"foo":');
        $store = new JsonFileStore($invalid);
        $store->get('key');
    }

    /**
     * @expectedException \Webmozart\KeyValueStore\Api\UnserializationFailedException
     */
    public function testGetThrowsExceptionIfNotUnserializable()
    {
        file_put_contents($path = $this->tempDir.'/data.json', '{"key":"foobar"}');
        $store = new JsonFileStore($path);
        $store->get('key');
    }

    /**
     * @expectedException \Webmozart\KeyValueStore\Api\ReadException
     * @expectedExceptionMessage Permission denied
     */
    public function testGetOrFailThrowsReadExceptionIfReadFails()
    {
        if ('\\' === DIRECTORY_SEPARATOR) {
            $this->markTestSkipped('Cannot deny read access on Windows.');
        }

        touch($notReadable = $this->tempDir.'/not-readable.json');
        $store = new JsonFileStore($notReadable);

        chmod($notReadable, 0000);
        $store->getOrFail('key');
    }

    /**
     * @expectedException \Webmozart\KeyValueStore\Api\ReadException
     * @expectedExceptionMessage Expected one of:
     */
    public function testGetOrFailThrowsReadExceptionIfInvalidJsonSyntax()
    {
        file_put_contents($invalid = $this->tempDir.'/data.json', '{"foo":');
        $store = new JsonFileStore($invalid);
        $store->getOrFail('key');
    }

    /**
     * @expectedException \Webmozart\KeyValueStore\Api\UnserializationFailedException
     */
    public function testGetOrFailThrowsExceptionIfNotUnserializable()
    {
        file_put_contents($path = $this->tempDir.'/data.json', '{"key":"foobar"}');
        $store = new JsonFileStore($path);
        $store->getOrFail('key');
    }

    /**
     * @expectedException \Webmozart\KeyValueStore\Api\ReadException
     * @expectedExceptionMessage Permission denied
     */
    public function testGetMultipleThrowsReadExceptionIfReadFails()
    {
        if ('\\' === DIRECTORY_SEPARATOR) {
            $this->markTestSkipped('Cannot deny read access on Windows.');
        }

        touch($notReadable = $this->tempDir.'/not-readable.json');
        $store = new JsonFileStore($notReadable);

        chmod($notReadable, 0000);
        $store->getMultiple(array('key'));
    }

    /**
     * @expectedException \Webmozart\KeyValueStore\Api\ReadException
     * @expectedExceptionMessage Expected one of:
     */
    public function testGetMultipleThrowsReadExceptionIfInvalidJsonSyntax()
    {
        file_put_contents($invalid = $this->tempDir.'/data.json', '{"foo":');
        $store = new JsonFileStore($invalid);
        $store->getMultiple(array('key'));
    }

    /**
     * @expectedException \Webmozart\KeyValueStore\Api\UnserializationFailedException
     */
    public function testGetMultipleThrowsExceptionIfNotUnserializable()
    {
        file_put_contents($path = $this->tempDir.'/data.json', '{"key":"foobar"}');
        $store = new JsonFileStore($path);
        $store->getMultiple(array('key'));
    }

    /**
     * @expectedException \Webmozart\KeyValueStore\Api\ReadException
     * @expectedExceptionMessage Permission denied
     */
    public function testGetMultipleOrFailThrowsReadExceptionIfReadFails()
    {
        if ('\\' === DIRECTORY_SEPARATOR) {
            $this->markTestSkipped('Cannot deny read access on Windows.');
        }

        touch($notReadable = $this->tempDir.'/not-readable.json');
        $store = new JsonFileStore($notReadable);

        chmod($notReadable, 0000);
        $store->getMultipleOrFail(array('key'));
    }

    /**
     * @expectedException \Webmozart\KeyValueStore\Api\ReadException
     * @expectedExceptionMessage Expected one of:
     */
    public function testGetMultipleOrFailThrowsReadExceptionIfInvalidJsonSyntax()
    {
        file_put_contents($invalid = $this->tempDir.'/data.json', '{"foo":');
        $store = new JsonFileStore($invalid);
        $store->getMultipleOrFail(array('key'));
    }

    /**
     * @expectedException \Webmozart\KeyValueStore\Api\UnserializationFailedException
     */
    public function testGetMultipleOrFailThrowsExceptionIfNotUnserializable()
    {
        file_put_contents($path = $this->tempDir.'/data.json', '{"key":"foobar"}');
        $store = new JsonFileStore($path);
        $store->getMultipleOrFail(array('key'));
    }

    /**
     * @expectedException \Webmozart\KeyValueStore\Api\ReadException
     * @expectedExceptionMessage Permission denied
     */
    public function testExistsThrowsReadExceptionIfReadFails()
    {
        if ('\\' === DIRECTORY_SEPARATOR) {
            $this->markTestSkipped('Cannot deny read access on Windows.');
        }

        touch($notReadable = $this->tempDir.'/not-readable.json');
        $store = new JsonFileStore($notReadable);

        chmod($notReadable, 0000);
        $store->exists('key');
    }

    /**
     * @expectedException \Webmozart\KeyValueStore\Api\ReadException
     * @expectedExceptionMessage Permission denied
     */
    public function testKeysThrowsReadExceptionIfReadFails()
    {
        if ('\\' === DIRECTORY_SEPARATOR) {
            $this->markTestSkipped('Cannot deny read access on Windows.');
        }

        touch($notReadable = $this->tempDir.'/not-readable.json');
        $store = new JsonFileStore($notReadable);

        chmod($notReadable, 0000);
        $store->keys();
    }

    /**
     * @expectedException \Webmozart\KeyValueStore\Api\ReadException
     */
    public function testSortThrowsReadExceptionIfReadFails()
    {
        if ('\\' === DIRECTORY_SEPARATOR) {
            $this->markTestSkipped('Cannot deny read access on Windows.');
        }

        touch($notReadable = $this->tempDir.'/not-readable.json');
        $store = new JsonFileStore($notReadable);

        chmod($notReadable, 0000);
        $store->sort();
    }

    /**
     * @expectedException \Webmozart\KeyValueStore\Api\WriteException
     */
    public function testSortThrowsWriteExceptionIfWriteFails()
    {
        file_put_contents($readOnlyFile = $this->tempDir.'/read-only.json', '{}');
        $store = new JsonFileStore($readOnlyFile);

        chmod($readOnlyFile, 0400);
        $store->sort();
    }

    /**
     * @expectedException \Webmozart\KeyValueStore\Api\ReadException
     */
    public function testCountThrowsReadExceptionIfReadFails()
    {
        if ('\\' === DIRECTORY_SEPARATOR) {
            $this->markTestSkipped('Cannot deny read access on Windows.');
        }

        touch($notReadable = $this->tempDir.'/not-readable.json');
        $store = new JsonFileStore($notReadable);

        chmod($notReadable, 0000);
        $store->count();
    }
}
