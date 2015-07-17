<?php

/*
 * This file is part of the webmozart/key-value-store package.
 *
 * (c) Bernhard Schussek <bschussek@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Webmozart\KeyValueStore\Tests\Fixtures;

use Doctrine\Common\Cache\Cache;
use Doctrine\Common\Cache\FlushableCache;

/**
 * @since  1.0
 *
 * @author Bernhard Schussek <bschussek@gmail.com>
 */
interface TestFlushableCache extends Cache, FlushableCache
{
}
