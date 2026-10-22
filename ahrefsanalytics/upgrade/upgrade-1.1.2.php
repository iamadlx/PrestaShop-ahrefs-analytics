<?php
/**
 * Ahrefs Web Analytics 1.1.2
 *
 * The "Clear stored key" switch is gone: emptying the key field and saving
 * now erases the key. Removes its configuration entry if a shop ever stored
 * one. Idempotent: deleting a missing key is a no-op.
 *
 * Author : ADLX (https://github.com/iamadlx)
 * License: MIT
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

function upgrade_module_1_1_2($module)
{
    Configuration::deleteByName('AHREFS_ANALYTICS_CLEAR_KEY');

    return true;
}
