<?php

namespace jetstore\app\controllers;

class AppController
{
    public function render(): string
    {
        $indexPath = RootPath.'resources/index.html';
        $content = file_get_contents($indexPath);

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