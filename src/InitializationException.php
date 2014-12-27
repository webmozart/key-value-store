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

use RuntimeException;

/**
 * Thrown when a key-value store cannot be initialized.
 *
 * @since  1.0
 * @author Bernhard Schussek <bschussek@gmail.com>
 */
class InitializationException extends RuntimeException
{
}
