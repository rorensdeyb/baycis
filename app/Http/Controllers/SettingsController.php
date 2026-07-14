<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\AuditLog;

class SettingsController extends Controller
{
    private $path;

    public function __construct()
    {
        $this->path = storage_path('app/settings.json');
    }

    private function defaultSettings(): array
    {
        return [
            'system_name' => 'BayCIS Inventory Management System',
            'org_name' => 'Bay Central Elementary School',
            'timezone' => 'Asia/Manila',
            'date_format' => 'Y-m-d',
            'auto_tags' => 'on',
            'low_stock_threshold' => 5,
            'density' => 'densityCozy',
        ];
    }

    private function loadSettings(): array
    {
        $settings = $this->defaultSettings();

        if (file_exists($this->path)) {
            $savedSettings = json_decode(file_get_contents($this->path), true) ?? [];
            $settings = array_merge($settings, $savedSettings);
        }

        return $settings;
    }

    public function index()
    {
        $settings = $this->loadSettings();
        return view('admin.settings', compact('settings'));
    }

    public function updateGeneral(Request $request)
    {
        $settings = $this->loadSettings();
        $settings['system_name'] = $request->input('system_name');
        $settings['org_name'] = $request->input('org_name');
        $settings['timezone'] = $request->input('timezone');
        $settings['date_format'] = $request->input('date_format');
        file_put_contents($this->path, json_encode($settings, JSON_PRETTY_PRINT));

        \App\Models\AuditLog::create([
        'user_id' => Auth::id(), 'action' => 'Settings', 'table_name' => 'system', 'record_id' => 0,
        'description' => 'Updated System General Settings'
    ]);
    return redirect()->back()->with('success', 'General settings updated!');
    }

    public function updateInventory(Request $request)
    {
        $settings = $this->loadSettings();
        $settings['auto_tags'] = $request->has('auto_tags') ? 'on' : 'off';
        $settings['low_stock_threshold'] = $request->input('low_stock_threshold');
        file_put_contents($this->path, json_encode($settings, JSON_PRETTY_PRINT));
        return redirect()->back()->with('success', 'Inventory rules updated!');
    }

    public function updateAppearance(Request $request)
    {
        $settings = $this->loadSettings();
        $settings['density'] = $request->input('density', 'densityCozy');
        file_put_contents($this->path, json_encode($settings, JSON_PRETTY_PRINT));
        return redirect()->back()->with('success', 'Appearance updated!');
    }
}