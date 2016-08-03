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

/**
 * A key-value store backed by a PHP array with serialized entries.
 *
 * The contents of the store are lost when the store is released from memory.
 *
 * This store behaves more like persistent key-value stores than
 * {@link ArrayStore}. It is useful for testing.
 *
 * @since  1.0
 *
 * @author Bernhard Schussek <bschussek@gmail.com>
 *
 * @deprecated Deprecated as of version 1.0, will be removed in version
 *             2.0. Use the `ArrayStore` with the `SERIALIZE` flag
 *             instead.
 */
class SerializingArrayStore extends ArrayStore
{
    public function __construct(array $array = array())
    {
        parent::__construct($array, parent::SERIALIZE);
    }
}
