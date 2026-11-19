<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Models\SystemSetting;
use App\Models\AuditLog;

class SettingController extends Controller
{
    private SystemSetting $settingModel;

    public function __construct()
    {
        $this->settingModel = new SystemSetting();
    }

    /**
     * System Settings Dashboard & Configuration Hub
     */
    public function index(Request $request, Response $response): string
    {
        $activeTab = $request->query('tab', 'hospital');
        $groupedSettings = $this->settingModel->allGrouped();

        return $this->view('settings/index', [
            'settings'  => $groupedSettings,
            'activeTab' => $activeTab,
        ]);
    }

    /**
     * Update settings by group
     */
    public function update(Request $request, Response $response): void
    {
        $group = trim((string)$request->input('group', 'hospital'));
        $data = $request->all();

        // Filter out csrf and group
        unset($data['_csrf_token'], $data['group']);

        foreach ($data as $key => $val) {
            SystemSetting::set($key, $val, $group);
        }

        AuditLog::log(
            Session::get('user_id', 1),
            'update_system_settings',
            'system_settings',
            null,
            ['group' => $group, 'updated_keys' => array_keys($data)]
        );

        Session::flash('success', 'تم حفظ وتحديث إعدادات النظام بنجاح.');
        $response->redirect("/settings?tab={$group}");
    }
}
