<?php

declare(strict_types=1);

namespace Iniznet\Mahout\Assets\Tests;

use Iniznet\Mahout\Assets\Tests\Fixtures\InMemoryQuerySource;
use Iniznet\Mahout\Kernel\Diagnostics;
use Iniznet\Mahout\Kernel\Environment;

/**
 * The base test case for this package.
 *
 * Core's WP_UnitTestCase already wraps each test in a transaction and provides
 * the factories. This class exists so every test extends one name, so
 * Diagnostics is built without touching the running site's environment, and so
 * a test can forget the script modules and styles another test registered.
 *
 * @internal
 */
abstract class TestCase extends \WP_UnitTestCase
{
    protected function diagnostics(
        ?Environment $environment = null,
        ?InMemoryQuerySource $queries = null,
    ): Diagnostics {
        return new Diagnostics(
            environment: $environment ?? new Environment(type: 'production', debug: false, developmentMode: false),
            queries: $queries ?? new InMemoryQuerySource(),
        );
    }

    protected function developmentEnvironment(): Environment
    {
        return new Environment(type: 'development', debug: true, developmentMode: true);
    }

    /**
     * Put the request on an admin screen, because core's own admin_enqueue_scripts
     * listeners read get_current_screen()->id and it is null without one.
     */
    protected function onAdminScreen(string $id = 'index.php'): void
    {
        if (!\function_exists('set_current_screen')) {
            require_once ABSPATH.'wp-admin/includes/screen.php';
        }

        \set_current_screen($id);
    }

    /**
     * @param list<string> $handles
     */
    protected function forget(array $handles): void
    {
        foreach ($handles as $handle) {
            \wp_deregister_script_module($handle);
            \wp_dequeue_script_module($handle);
            \wp_deregister_style($handle);
            \wp_dequeue_style($handle);
        }
    }
}
