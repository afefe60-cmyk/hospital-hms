<?php

namespace App\Core;

use Throwable;

class App
{
    private static ?App $instance = null;
    private Router $router;
    private Request $request;
    private Response $response;
    private string $basePath;

    public function __construct(string $basePath)
    {
        $this->basePath = rtrim($basePath, '/\\');
        self::$instance = $this;

        $this->bootstrap();
    }

    public static function getInstance(): self
    {
        return self::$instance;
    }

    public function getBasePath(): string
    {
        return $this->basePath;
    }

    public function getRouter(): Router
    {
        return $this->router;
    }

    private function bootstrap(): void
    {
        // 1. Load Environment variables
        Env::load($this->basePath . '/.env');

        // 2. Load Configuration files
        Config::load($this->basePath . '/config');

        // 3. Set Application Timezone
        date_default_timezone_set(Config::get('app.timezone', 'Africa/Khartoum'));

        // 4. Register Error Handling
        $this->registerErrorHandling();

        // 5. Initialize Core Components
        Session::start();
        $this->request = new Request();
        $this->response = new Response();
        $this->router = new Router();
        View::setViewsPath($this->basePath . '/resources/views');

        // 6. Load Routes
        $this->loadRoutes();
    }

    private function registerErrorHandling(): void
    {
        error_reporting(E_ALL);

        set_error_handler(function ($severity, $message, $file, $line) {
            if (!(error_reporting() & $severity)) {
                return false;
            }
            throw new \ErrorException($message, 0, $severity, $file, $line);
        });

        set_exception_handler(function (Throwable $e) {
            $this->handleException($e);
        });
    }

    private function handleException(Throwable $e): void
    {
        $logDir = $this->basePath . '/storage/logs';
        if (!is_dir($logDir)) {
            mkdir($logDir, 0755, true);
        }

        $logMessage = sprintf(
            "[%s] Exception: %s in %s:%d\nStack Trace:\n%s\n--------------------\n",
            date('Y-m-d H:i:s'),
            $e->getMessage(),
            $e->getFile(),
            $e->getLine(),
            $e->getTraceAsString()
        );

        file_put_contents($logDir . '/app.log', $logMessage, FILE_APPEND);

        $debug = Config::get('app.debug', false);

        if (!headers_sent()) {
            http_response_code(500);
        }

        if ($this->request->wantsJson()) {
            echo json_encode([
                'error' => 'Internal Server Error',
                'message' => $debug ? $e->getMessage() : 'حدث خطأ في النظام. يرجى مراجعة المسؤول.',
                'file' => $debug ? $e->getFile() : null,
                'line' => $debug ? $e->getLine() : null,
            ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        } else {
            if ($debug) {
                echo "<div style='background:#f8d7da;color:#721c24;padding:20px;font-family:monospace;direction:ltr;'>";
                echo "<h2>Application Error</h2>";
                echo "<p><strong>Message:</strong> " . htmlspecialchars($e->getMessage()) . "</p>";
                echo "<p><strong>File:</strong> " . htmlspecialchars($e->getFile()) . " (Line " . $e->getLine() . ")</p>";
                echo "<pre>" . htmlspecialchars($e->getTraceAsString()) . "</pre>";
                echo "</div>";
            } else {
                try {
                    echo View::render('errors/500', ['exception' => $e], 'layouts/main');
                } catch (\Throwable $renderEx) {
                    echo "<!DOCTYPE html><html lang='ar' dir='rtl'><head><meta charset='utf-8'><title>خطأ في النظام</title><style>body{font-family:sans-serif;text-align:center;padding:50px;background:#f8fafc;color:#1e293b;}h1{color:#dc2626;}</style></head><body><h1>500 - حدث خطأ في النظام</h1><p>يرجى التأكد من إعدادات الاتصال بقاعدة البيانات في ملف .env واستيراد الجداول.</p></body></html>";
                }
            }
        }
        exit;
    }

    private function loadRoutes(): void
    {
        $router = $this->router;

        $webRoutes = $this->basePath . '/routes/web.php';
        if (file_exists($webRoutes)) {
            require $webRoutes;
        }

        $apiRoutes = $this->basePath . '/routes/api.php';
        if (file_exists($apiRoutes)) {
            require $apiRoutes;
        }
    }

    public function run(): void
    {
        $output = $this->router->dispatch($this->request, $this->response);

        if ($output instanceof Response) {
            $output->send();
        } elseif (is_string($output)) {
            $this->response->setContent($output)->send();
        }
    }
}
