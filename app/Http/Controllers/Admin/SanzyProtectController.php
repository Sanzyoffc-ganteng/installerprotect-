<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Support\Facades\Artisan;

class SanzyProtectController extends Controller
{
    public function index(): View
    {
        $level = config('sanzy-protect.level', 9);
        $whitelistFile = '/var/www/pterodactyl/storage/sanzy_whitelist.json';
        $whitelist = file_exists($whitelistFile) 
            ? json_decode(file_get_contents($whitelistFile), true)['admins'] ?? [] 
            : [];
        
        return view('admin.sanzy-protect', compact('level', 'whitelist'));
    }
    
    public function updateLevel(Request $request)
    {
        $request->validate([
            'level' => 'required|integer|min:0|max:9'
        ]);
        
        $level = $request->input('level');
        
        // Update .env file
        $envFile = base_path('.env');
        $envContent = file_get_contents($envFile);
        
        if (preg_match('/SANZY_PROTECT_LEVEL=[0-9]+/', $envContent)) {
            $envContent = preg_replace('/SANZY_PROTECT_LEVEL=[0-9]+/', "SANZY_PROTECT_LEVEL={$level}", $envContent);
        } else {
            $envContent .= "\nSANZY_PROTECT_LEVEL={$level}";
        }
        
        file_put_contents($envFile, $envContent);
        Artisan::call('config:clear');
        
        return back()->with('success', "✅ Protection level updated to Level {$level}");
    }
    
    public function toggleWhitelist(Request $request)
    {
        $request->validate([
            'admin_id' => 'required|integer|min:1'
        ]);
        
        $file = '/var/www/pterodactyl/storage/sanzy_whitelist.json';
        $data = file_exists($file) ? json_decode(file_get_contents($file), true) : ['admins' => []];
        
        $adminId = $request->input('admin_id');
        
        if (in_array($adminId, $data['admins'])) {
            $data['admins'] = array_values(array_filter($data['admins'], fn($id) => $id !== $adminId));
            $msg = "✅ Admin #{$adminId} removed from whitelist";
        } else {
            $data['admins'][] = $adminId;
            $msg = "✅ Admin #{$adminId} added to whitelist";
        }
        
        file_put_contents($file, json_encode($data, JSON_PRETTY_PRINT));
        
        return back()->with('success', $msg);
    }
}
