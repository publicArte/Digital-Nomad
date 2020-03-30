<?php

defined('BASEPATH') or exit('No direct script access allowed');

/**
 * @since  2.3.0
 * NEW Global hooks function
 * This function must be used for all hooks
 * @return object Hooks instance
 */
function hooks()
{
    global $hooks;

    return $hooks;
}
