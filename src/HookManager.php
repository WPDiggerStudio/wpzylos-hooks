<?php

declare(strict_types=1);

namespace WPZylos\Framework\Hooks;

use WPZylos\Framework\Core\Contracts\ContextInterface;

/**
 * Hook manager for WordPress actions and filters.
 *
 * Provides two APIs:
 * - wpAction/wpFilter: For WordPress core hooks (NEVER prefixed)
 * - action/filter/doAction/applyFilter: For custom plugin hooks (ALWAYS prefixed)
 *
 * @package WPZylos\Framework\Hooks
 */
class HookManager
{
    /**
     * @var ContextInterface Plugin context
     */
    private ContextInterface $context;

    /**
     * @var array<string, array<int, array{callable: callable, priority: int}>> Registered actions
     */
    private array $actions = [];

    /**
     * @var array<string, array<int, array{callable: callable, priority: int}>> Registered filters
     */
    private array $filters = [];

    /**
     * Create hook manager.
     *
     * @param ContextInterface $context Plugin context for prefixing
     */
    public function __construct(ContextInterface $context)
    {
        $this->context = $context;
    }

    // =========================================================================
    // WordPress Core Hooks (NEVER PREFIXED)
    // =========================================================================

    /**
     * Add action to a WordPress core hook.
     *
     * Hook name is used exactly as provided - no prefixing.
     *
     * @param string $hook WordPress hook name (e.g., 'init', 'admin_menu')
     * @param callable $callback Callback function
     * @param int $priority Hook priority (default: 10)
     * @param int $acceptedArgs Number of arguments (default: 1)
     * @return static
     */
    public function wpAction(string $hook, callable $callback, int $priority = 10, int $acceptedArgs = 1): static
    {
        add_action($hook, $callback, $priority, $acceptedArgs);
        $this->trackAction($hook, $callback, $priority);
        return $this;
    }

    /**
     * Add filter to a WordPress core hook.
     *
     * Hook name is used exactly as provided - no prefixing.
     *
     * @param string $hook WordPress hook name (e.g., 'the_content', 'plugin_action_links')
     * @param callable $callback Callback function
     * @param int $priority Hook priority (default: 10)
     * @param int $acceptedArgs Number of arguments (default: 1)
     * @return static
     */
    public function wpFilter(string $hook, callable $callback, int $priority = 10, int $acceptedArgs = 1): static
    {
        add_filter($hook, $callback, $priority, $acceptedArgs);
        $this->trackFilter($hook, $callback, $priority);
        return $this;
    }

    /**
     * Remove action from a WordPress core hook.
     *
     * @param string $hook WordPress hook name
     * @param callable $callback Callback to remove
     * @param int $priority Priority used when adding
     * @return bool True if removed
     */
    public function removeWpAction(string $hook, callable $callback, int $priority = 10): bool
    {
        $this->untrackAction($hook, $callback, $priority);
        return remove_action($hook, $callback, $priority);
    }

    /**
     * Remove filter from a WordPress core hook.
     *
     * @param string $hook WordPress hook name
     * @param callable $callback Callback to remove
     * @param int $priority Priority used when adding
     * @return bool True if removed
     */
    public function removeWpFilter(string $hook, callable $callback, int $priority = 10): bool
    {
        $this->untrackFilter($hook, $callback, $priority);
        return remove_filter($hook, $callback, $priority);
    }

    // =========================================================================
    // Custom Plugin Hooks (ALWAYS PREFIXED)
    // =========================================================================

    /**
     * Register a custom action hook listener.
     *
     * Hook name is automatically prefixed with plugin prefix.
     *
     * @param string $hook Custom hook name without prefix
     * @param callable $callback Callback function
     * @param int $priority Hook priority (default: 10)
     * @param int $acceptedArgs Number of arguments (default: 1)
     * @return static
     */
    public function action(string $hook, callable $callback, int $priority = 10, int $acceptedArgs = 1): static
    {
        $prefixedHook = $this->context->hook($hook);
        add_action($prefixedHook, $callback, $priority, $acceptedArgs);
        $this->trackAction($prefixedHook, $callback, $priority);
        return $this;
    }

    /**
     * Register a custom filter hook listener.
     *
     * Hook name is automatically prefixed with plugin prefix.
     *
     * @param string $hook Custom hook name without prefix
     * @param callable $callback Callback function
     * @param int $priority Hook priority (default: 10)
     * @param int $acceptedArgs Number of arguments (default: 1)
     * @return static
     */
    public function filter(string $hook, callable $callback, int $priority = 10, int $acceptedArgs = 1): static
    {
        $prefixedHook = $this->context->hook($hook);
        add_filter($prefixedHook, $callback, $priority, $acceptedArgs);
        $this->trackFilter($prefixedHook, $callback, $priority);
        return $this;
    }

    /**
     * Fire a custom action hook.
     *
     * Hook name is automatically prefixed with plugin prefix.
     *
     * @param string $hook Custom hook name without prefix
     * @param mixed ...$args Arguments to pass to callbacks
     * @return void
     */
    public function doAction(string $hook, mixed ...$args): void
    {
        $prefixedHook = $this->context->hook($hook);
        do_action($prefixedHook, ...$args);
    }

    /**
     * Apply a custom filter hook.
     *
     * Hook name is automatically prefixed with plugin prefix.
     *
     * @param string $hook Custom hook name without prefix
     * @param mixed $value Value to filter
     * @param mixed ...$args Additional arguments
     * @return mixed Filtered value
     */
    public function applyFilter(string $hook, mixed $value, mixed ...$args): mixed
    {
        $prefixedHook = $this->context->hook($hook);
        return apply_filters($prefixedHook, $value, ...$args);
    }

    /**
     * Remove a custom action hook listener.
     *
     * @param string $hook Custom hook name without prefix
     * @param callable $callback Callback to remove
     * @param int $priority Priority used when adding
     * @return bool True if removed
     */
    public function removeAction(string $hook, callable $callback, int $priority = 10): bool
    {
        $prefixedHook = $this->context->hook($hook);
        $this->untrackAction($prefixedHook, $callback, $priority);
        return remove_action($prefixedHook, $callback, $priority);
    }

    /**
     * Remove a custom filter hook listener.
     *
     * @param string $hook Custom hook name without prefix
     * @param callable $callback Callback to remove
     * @param int $priority Priority used when adding
     * @return bool True if removed
     */
    public function removeFilter(string $hook, callable $callback, int $priority = 10): bool
    {
        $prefixedHook = $this->context->hook($hook);
        $this->untrackFilter($prefixedHook, $callback, $priority);
        return remove_filter($prefixedHook, $callback, $priority);
    }

    // =========================================================================
    // One-time Hooks
    // =========================================================================

    /**
     * Add a one-time action that removes itself after first execution.
     *
     * @param string $hook WordPress hook name (NOT prefixed)
     * @param callable $callback Callback function
     * @param int $priority Hook priority
     * @return static
     */
    public function once(string $hook, callable $callback, int $priority = 10): static
    {
        $wrapper = static function (...$args) use ($hook, $callback, $priority, &$wrapper) {
            remove_action($hook, $wrapper, $priority);
            return $callback(...$args);
        };

        return $this->wpAction($hook, $wrapper, $priority);
    }

    // =========================================================================
    // Registry Access
    // =========================================================================

    /**
     * Get all registered actions.
     *
     * @return array<string, array<int, array{callable: callable, priority: int}>>
     */
    public function getRegisteredActions(): array
    {
        return $this->actions;
    }

    /**
     * Get all registered filters.
     *
     * @return array<string, array<int, array{callable: callable, priority: int}>>
     */
    public function getRegisteredFilters(): array
    {
        return $this->filters;
    }

    /**
     * Check if any hooks are registered for the given hook name.
     *
     * @param string $hook Hook name (exact, with prefix if custom)
     * @return bool
     */
    public function hasHook(string $hook): bool
    {
        return isset($this->actions[$hook]) || isset($this->filters[$hook]);
    }

    /**
     * Get the prefixed version of a custom hook name.
     *
     * @param string $hook Hook name without prefix
     * @return string Prefixed hook name
     */
    public function prefix(string $hook): string
    {
        return $this->context->hook($hook);
    }

    // =========================================================================
    // Internal Tracking
    // =========================================================================

    /**
     * Track an action in the registry.
     *
     * @param string $hook Hook name
     * @param callable $callback Callback
     * @param int $priority Priority
     * @return void
     */
    private function trackAction(string $hook, callable $callback, int $priority): void
    {
        $this->actions[$hook][] = [
            'callable' => $callback,
            'priority' => $priority,
        ];
    }

    /**
     * Track a filter in the registry.
     *
     * @param string $hook Hook name
     * @param callable $callback Callback
     * @param int $priority Priority
     * @return void
     */
    private function trackFilter(string $hook, callable $callback, int $priority): void
    {
        $this->filters[$hook][] = [
            'callable' => $callback,
            'priority' => $priority,
        ];
    }

    /**
     * Remove an action from the registry.
     *
     * @param string $hook Hook name
     * @param callable $callback Callback
     * @param int $priority Priority
     * @return void
     */
    private function untrackAction(string $hook, callable $callback, int $priority): void
    {
        if (!isset($this->actions[$hook])) {
            return;
        }

        $this->actions[$hook] = array_filter(
            $this->actions[$hook],
            static fn($entry) => $entry['callable'] !== $callback || $entry['priority'] !== $priority
        );
    }

    /**
     * Remove a filter from the registry.
     *
     * @param string $hook Hook name
     * @param callable $callback Callback
     * @param int $priority Priority
     * @return void
     */
    private function untrackFilter(string $hook, callable $callback, int $priority): void
    {
        if (!isset($this->filters[$hook])) {
            return;
        }

        $this->filters[$hook] = array_filter(
            $this->filters[$hook],
            static fn($entry) => $entry['callable'] !== $callback || $entry['priority'] !== $priority
        );
    }
}
