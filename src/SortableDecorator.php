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

use Webmozart\KeyValueStore\Api\SortableStore;

/**
 * A sortable decorator implementing a sort system for any store.
 *
 * @since  1.0
 *
 * @author Bernhard Schussek <bschussek@gmail.com>
 * @author Titouan Galopin <galopintitouan@gmail.com>
 */
class SortableDecorator extends AbstractDecorator implements SortableStore
{
    /**
     * @var int
     */
    private $flags;

    /**
     * {@inheritdoc}
     */
    public function sort($flags = SORT_REGULAR)
    {
        $this->flags = $flags;
    }

    /**
     * {@inheritdoc}
     */
    public function set($key, $value)
    {
        $this->flags = null;
        $this->store->set($key, $value);
    }

    /**
     * {@inheritdoc}
     */
    public function keys()
    {
        $keys = $this->store->keys();

        if (null !== $this->flags) {
            sort($keys, $this->flags);
        }

        return $keys;
    }
}
