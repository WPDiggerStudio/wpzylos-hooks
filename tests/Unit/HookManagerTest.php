<?php

declare(strict_types=1);

namespace WPZylos\Framework\Hooks\Tests\Unit;

use PHPUnit\Framework\TestCase;
use WPZylos\Framework\Core\Contracts\ContextInterface;
use WPZylos\Framework\Hooks\HookManager;

/**
 * Tests for HookManager class.
 */
class HookManagerTest extends TestCase
{
    private ContextInterface $context;
    private HookManager $hooks;

    protected function setUp(): void
    {
        // Reset globals
        $GLOBALS['wp_actions'] = [];
        $GLOBALS['wp_filters'] = [];
        $GLOBALS['fired_actions'] = [];
        $GLOBALS['applied_filters'] = [];

        // Create mock context
        $this->context = $this->createMock(ContextInterface::class);
        $this->context->method('hook')
            ->willReturnCallback(fn($name) => 'test_' . $name);

        $this->hooks = new HookManager($this->context);
    }

    public function testWpActionIsNotPrefixed(): void
    {
        $callback = fn() => null;

        $this->hooks->wpAction('init', $callback);

        $this->assertArrayHasKey('init', $GLOBALS['wp_actions']);
        $this->assertCount(1, $GLOBALS['wp_actions']['init']);
    }

    public function testWpFilterIsNotPrefixed(): void
    {
        $callback = fn() => null;

        $this->hooks->wpFilter('the_content', $callback);

        $this->assertArrayHasKey('the_content', $GLOBALS['wp_filters']);
    }

    public function testActionIsPrefixed(): void
    {
        $callback = fn() => null;

        $this->hooks->action('saved', $callback);

        $this->assertArrayHasKey('test_saved', $GLOBALS['wp_actions']);
        $this->assertArrayNotHasKey('saved', $GLOBALS['wp_actions']);
    }

    public function testFilterIsPrefixed(): void
    {
        $callback = fn() => null;

        $this->hooks->filter('output', $callback);

        $this->assertArrayHasKey('test_output', $GLOBALS['wp_filters']);
    }

    public function testDoActionFiresPrefixed(): void
    {
        $this->hooks->doAction('completed', 'arg1', 'arg2');

        $this->assertArrayHasKey('test_completed', $GLOBALS['fired_actions']);
        $this->assertSame(['arg1', 'arg2'], $GLOBALS['fired_actions']['test_completed']);
    }

    public function testApplyFilterAppliesPrefixed(): void
    {
        $result = $this->hooks->applyFilter('format', 'value');

        $this->assertArrayHasKey('test_format', $GLOBALS['applied_filters']);
        $this->assertSame('value', $result);
    }

    public function testPrefixReturnsHookName(): void
    {
        $prefixed = $this->hooks->prefix('my_event');

        $this->assertSame('test_my_event', $prefixed);
    }

    public function testHasHookReturnsTrueWhenRegistered(): void
    {
        $this->hooks->wpAction('init', fn() => null);

        $this->assertTrue($this->hooks->hasHook('init'));
    }

    public function testHasHookReturnsFalseWhenNotRegistered(): void
    {
        $this->assertFalse($this->hooks->hasHook('nonexistent'));
    }

    public function testGetRegisteredActionsReturnsTracked(): void
    {
        $callback = fn() => null;

        $this->hooks->wpAction('init', $callback, 20);

        $actions = $this->hooks->getRegisteredActions();

        $this->assertArrayHasKey('init', $actions);
        $this->assertSame($callback, $actions['init'][0]['callable']);
        $this->assertSame(20, $actions['init'][0]['priority']);
    }

    public function testMethodChainingWorks(): void
    {
        $result = $this->hooks
            ->wpAction('init', fn() => null)
            ->wpFilter('the_content', fn() => null)
            ->action('saved', fn() => null);

        $this->assertInstanceOf(HookManager::class, $result);
    }
}
