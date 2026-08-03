<?php

declare(strict_types=1);

namespace HyperPress\Tests\Unit;

use HyperPress\Bootstrap;
use PHPUnit\Framework\TestCase;

/**
 * Covers Bootstrap::init()'s web-reachability deferral in library mode.
 *
 * When this library is installed transitively into a Bedrock-style root
 * composer vendor (outside wp-content), init() must NOT claim the
 * namespace-scoped LOADED identity, so a web-reachable copy bundled inside a
 * plugin under wp-content can still win and serve frontend assets
 * (htmx/alpine/datastar). Mirrors HyperFields\LibraryBootstrap and
 * HyperBlocks\WordPress\Bootstrap.
 */
class BootstrapTest extends TestCase
{
    /**
     * Runs without process isolation: this test never defines HyperPress\LOADED
     * (it asserts the deferral), so it neither depends on nor affects other
     * tests. The explicit plugin_url override path is covered by HyperFields'
     * LibraryBootstrapTest.
     */
    public function testInitDefersInLibraryModeWhenNotWebReachable(): void
    {
        // plugin_file basename is not a known entry file -> library mode;
        // base_dir (its dirname) is outside every WP content root -> not
        // web-reachable -> init() must defer without claiming LOADED.
        Bootstrap::init([
            'plugin_file' => sys_get_temp_dir() . '/bedrock-app/vendor/estebanforge/hyperpress-core/bootstrap.php',
        ]);

        $this->assertFalse(
            defined('HyperPress\\LOADED'),
            'A non-web-reachable library-mode copy must defer and not define the LOADED guard; a web-reachable copy must be free to claim it.'
        );
    }
}
