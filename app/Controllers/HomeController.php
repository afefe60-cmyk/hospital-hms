<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Database;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;

class HomeController extends Controller
{
    public function index(Request $request, Response $response): string
    {
        // System status health check
        $dbStatus = false;
        $dbVersion = 'Unknown';
        $dbTablesCount = 0;

        try {
            $db = Database::getInstance();
            $dbVersion = $db->fetchColumn("SELECT VERSION()");
            $dbTablesCount = (int)$db->fetchColumn("SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = DATABASE()");
            $dbStatus = true;
        } catch (\Throwable $e) {
            $dbStatus = false;
            $dbVersion = $e->getMessage();
        }

        $systemInfo = [
            'app_name' => config('app.name'),
            'app_env' => config('app.env'),
            'php_version' => PHP_VERSION,
            'db_status' => $dbStatus,
            'db_version' => $dbVersion,
            'db_tables_count' => $dbTablesCount,
            'timezone' => date_default_timezone_get(),
            'current_time' => date('Y-m-d H:i:s'),
            'locale' => Session::get('locale', config('app.locale', 'ar')),
            'currency' => config('app.currency', 'SDG'),
        ];

        return $this->view('home', [
            'pageTitle' => __('messages.system_overview'),
            'info' => $systemInfo
        ]);
    }

    public function switchLanguage(Request $request, Response $response): void
    {
        $lang = $request->input('lang', 'ar');
        if (in_array($lang, ['ar', 'en'])) {
            Session::set('locale', $lang);
        }
        $referer = $_SERVER['HTTP_REFERER'] ?? url('/');
        $this->redirect($referer);
    }
}
