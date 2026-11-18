<?php

namespace App\Core;

class Config
{
    private static array $configs = [];

    /**
     * Load all configuration files from the config directory
     */
    public static function load(string $configPath): void
    {
        $files = glob(rtrim($configPath, '/\\') . '/*.php');
        foreach ($files as $file) {
            $key = basename($file, '.php');
            self::$configs[$key] = require $file;
        }
    }

    /**
     * Get a configuration value using dot notation (e.g. 'app.timezone')
     */
    public static function get(string $key, mixed $default = null): mixed
    {
        $segments = explode('.', $key);
        $data = self::$configs;

        foreach ($segments as $segment) {
            if (!is_array($data) || !array_key_exists($segment, $data)) {
                return $default;
            }
            $data = $data[$segment];
        }

        return $data;
    }

    /**
     * Set a configuration value at runtime
     */
    public static function set(string $key, mixed $value): void
    {
        $segments = explode('.', $key);
        $data = &self::$configs;

        foreach ($segments as $segment) {
            if (!isset($data[$segment]) || !is_array($data[$segment])) {
                $data[$segment] = [];
            }
            $data = &$data[$segment];
        }

        $data = $value;
    }
}
