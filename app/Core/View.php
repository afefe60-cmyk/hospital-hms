<?php

namespace App\Core;

use RuntimeException;

class View
{
    private static string $viewsPath = '';
    private static array $sections = [];
    private static ?string $currentSection = null;
    private static array $translations = [];

    public static function setViewsPath(string $path): void
    {
        self::$viewsPath = rtrim($path, '/\\');
    }

    public static function render(string $view, array $data = [], ?string $layout = 'layouts/main'): string
    {
        if (empty(self::$viewsPath)) {
            self::$viewsPath = dirname(__DIR__, 2) . '/resources/views';
        }

        $viewFile = self::$viewsPath . '/' . str_replace('.', '/', $view) . '.php';
        if (!file_exists($viewFile)) {
            throw new RuntimeException("View file not found: [{$viewFile}]");
        }

        // Extract variables to local scope
        extract($data, EXTR_SKIP);

        // Render the view content
        ob_start();
        require $viewFile;
        $content = ob_get_clean();

        // If layout is specified, wrap view content inside layout
        if ($layout !== null) {
            $layoutFile = self::$viewsPath . '/' . str_replace('.', '/', $layout) . '.php';
            if (!file_exists($layoutFile)) {
                throw new RuntimeException("Layout file not found: [{$layoutFile}]");
            }

            ob_start();
            require $layoutFile;
            return ob_get_clean();
        }

        return $content;
    }

    public static function startSection(string $name): void
    {
        self::$currentSection = $name;
        ob_start();
    }

    public static function endSection(): void
    {
        if (self::$currentSection === null) {
            throw new RuntimeException("No section started.");
        }
        self::$sections[self::$currentSection] = ob_get_clean();
        self::$currentSection = null;
    }

    public static function yieldSection(string $name, string $default = ''): string
    {
        return self::$sections[$name] ?? $default;
    }

    /**
     * Localization helper
     */
    public static function trans(string $key, array $replace = [], ?string $locale = null): string
    {
        $locale = $locale ?? Session::get('locale', Config::get('app.locale', 'ar'));
        $langDir = dirname(__DIR__, 2) . "/resources/languages/{$locale}";

        $segments = explode('.', $key);
        $file = array_shift($segments);

        if (!isset(self::$translations[$locale][$file])) {
            $path = "{$langDir}/{$file}.php";
            if (file_exists($path)) {
                self::$translations[$locale][$file] = require $path;
            } else {
                self::$translations[$locale][$file] = [];
            }
        }

        $text = self::$translations[$locale][$file];
        foreach ($segments as $segment) {
            if (!is_array($text) || !isset($text[$segment])) {
                return $key;
            }
            $text = $text[$segment];
        }

        if (!is_string($text)) {
            return $key;
        }

        foreach ($replace as $k => $v) {
            $text = str_replace(":{$k}", (string)$v, $text);
        }

        return $text;
    }
}
