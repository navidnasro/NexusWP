<?php

namespace nexuswp\app\controllers;

/**
 * Serves the built Vue application for the /app rewrite rule.
 *
 * The rule is declared in routes/web.php and resolved lazily, so this
 * controller is loaded only when someone visits /app.
 */
class AppController
{
    public function render(): string
    {
        $indexPath = RootPath.'resources/index.html';

        if (!is_file($indexPath))
            return '';

        $content = file_get_contents($indexPath);

        if ($content === false)
            return '';

        // Inject dynamic base URL for assets
        $pluginUrl = RootUrl.'resources/';
        $content = str_replace(
            '<base href="/" id="vite-base">',
            '<base href="'.esc_url($pluginUrl).'" id="vite-base">',
            $content
        );

        header('Content-Type: text/html');

        return $content;
    }
}
