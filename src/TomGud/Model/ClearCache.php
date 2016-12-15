<?php

namespace TomGud\Model;

/**
 * Class ClearCache
 * @package TomGud\Model
 */
abstract class ClearCache
{
    const HEADER = [
        'Cache-Control' => 'no-cache, no-store, must-revalidate',
        'Pragma' => 'no-cache',
        'Expires' => 0
    ];
}
