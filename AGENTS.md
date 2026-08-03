## Project Overview

HyperPress-Core is the Composer library that contains the HyperPress runtime.

It owns:
- API routing and rendering
- Asset loading for HTMX, Alpine, Datastar
- Admin options and compatibility logic
- Block integration and HyperFields/HyperBlocks wiring

## Scope Rules

- Keep `api-for-htmx` as a thin WordPress plugin adapter.
- Put runtime logic changes in this package (`HyperPress-Core`).
- Preserve backwards compatibility for public hooks, constants, and helper functions.

## Bedrock / Composer-managed WordPress sites

When this library is installed **transitively** into a Bedrock-style project, Composer places it in the project **root `vendor/`** (outside `wp-content/`), because the package is `type: library` and Bedrock's `installer-paths` only route `wordpress-plugin` / `wordpress-muplugin` / `wordpress-theme` types. That root-vendor copy is not under any web-accessible WordPress content root, so its frontend assets (HTMX/Alpine/Datastar) cannot be served over HTTP, and a `files`-autoloaded `bootstrap.php` running from there would resolve an empty `HYPERPRESS_PLUGIN_URL` / `Config::$pluginUrl`.

In **library mode**, `Bootstrap::init()` therefore **defers** when it cannot resolve a web URL: it returns without claiming the namespace-scoped `LOADED` guard or writing `Config::$pluginUrl`, leaving a web-reachable copy (one bundled inside a plugin under `wp-content/`) free to claim the identity and serve assets. An explicit `plugin_url` entry in the `$args` passed to `init()` overrides the deferral when a consumer knows the URL but the resolver cannot infer it. **Plugin mode** (a real `hyperpress.php` / `api-for-htmx.php` entry file under wp-content) always resolves a URL via `plugin_dir_url()` and never defers.

**Recommended pattern for plugins that bundle HyperPress-Core:** ship it inside the plugin's own committed `vendor/` (e.g. `wp-content/plugins/<your-plugin>/vendor/estebanforge/hyperpress-core/`) and load the plugin's own `vendor/autoload.php`. That copy is web-reachable and wins. Do not rely on a Bedrock root-vendor copy to serve assets; it never can.
