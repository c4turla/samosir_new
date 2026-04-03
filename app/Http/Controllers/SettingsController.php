<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use Illuminate\Http\Request;

class SettingsController extends Controller
{
    /**
     * Show settings page with all settings grouped.
     */
    public function index(Request $request)
    {
        $settings = Setting::allGrouped();
        
        // Group labels for the UI
        $groupLabels = [
            'app' => 'Informasi Aplikasi',
            'port' => 'Informasi Pelabuhan',
            'officials' => 'Pejabat Pelabuhan',
            'contact' => 'Informasi Kontak',
            'document' => 'Pengaturan Dokumen',
            'operational' => 'Pengaturan Operasional',
        ];

        $groupIcons = [
            'app' => 'ri-settings-3-line',
            'port' => 'ri-anchor-line',
            'officials' => 'ri-user-star-line',
            'contact' => 'ri-contacts-book-line',
            'document' => 'ri-file-text-line',
            'operational' => 'ri-tools-line',
        ];

        return inertia('Settings/Index', [
            'settings' => $settings,
            'groupLabels' => $groupLabels,
            'groupIcons' => $groupIcons,
            'user' => $request->user(),
        ]);
    }

    /**
     * Update application settings (bulk update).
     */
    public function update(Request $request)
    {
        $user = $request->user();

        // Only admin can update app settings
        if ($user->role !== 'admin') {
            return back()->withErrors(['error' => 'Anda tidak memiliki izin untuk mengubah pengaturan.']);
        }

        $settings = $request->input('settings', []);

        foreach ($settings as $key => $value) {
            Setting::setValue($key, $value);
        }

        // Flush all caches
        Setting::flushCache();

        return back()->with('success', 'Pengaturan berhasil diperbarui!');
    }
}