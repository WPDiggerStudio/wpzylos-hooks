<?php

declare(strict_types=1);

/**
 * PHPUnit bootstrap with WordPress hook mocks.
 */

require_once dirname(__DIR__) . '/vendor/autoload.php';

// Track hook calls
$GLOBALS['wp_actions'] = [];
$GLOBALS['wp_filters'] = [];
$GLOBALS['fired_actions'] = [];
$GLOBALS['applied_filters'] = [];

if (!function_exists('add_action')) {
    function add_action($tag, $callback, $priority = 10, $args = 1)
    {
        $GLOBALS['wp_actions'][$tag][] = [
            'callback' => $callback,
            'priority' => $priority,
        ];
    }
}

if (!function_exists('add_filter')) {
    function add_filter($tag, $callback, $priority = 10, $args = 1)
    {
        $GLOBALS['wp_filters'][$tag][] = [
            'callback' => $callback,
            'priority' => $priority,
        ];
    }
}

if (!function_exists('do_action')) {
    function do_action($tag, ...$args)
    {
        $GLOBALS['fired_actions'][$tag] = $args;
    }
}

if (!function_exists('apply_filters')) {
    function apply_filters($tag, $value, ...$args)
    {
        $GLOBALS['applied_filters'][$tag] = $value;
        return $value;
    }
}

if (!function_exists('remove_action')) {
    function remove_action($tag, $callback, $priority = 10)
    {
        return true;
    }
}

if (!function_exists('remove_filter')) {
    function remove_filter($tag, $callback, $priority = 10)
    {
        return true;
    }
}
