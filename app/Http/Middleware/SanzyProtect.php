<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Pterodactyl\Models\Server;

class SanzyProtect
{
    protected const MAIN_ADMIN_IDS = [1];
    protected const WHITELIST_FILE = '/var/www/pterodactyl/storage/sanzy_whitelist.json';
    protected const CACHE_TTL = 300;

    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();
        
        if (!$user || !$user->root_admin) {
            return $next($request);
        }
        
        if (in_array($user->id, self::MAIN_ADMIN_IDS)) {
            return $next($request);
        }
        
        $level = config('sanzy-protect.level', 9);
        $path = $request->path();
        $method = $request->method();
        
        // LEVEL 1: Anti-Kudeta & Anti-Sabotase
        if ($level >= 1) {
            foreach (self::MAIN_ADMIN_IDS as $mainId) {
                if (preg_match('#api/application/users/' . $mainId . '#', $path) && 
                    in_array($method, ['PUT', 'PATCH', 'DELETE'])) {
                    $this->logBlock($user->id, $path, $method, 'Anti-Kudeta');
                    return $this->deny('Admin Utama dilindungi SANZY PROTECT.');
                }
            }
            
            $locked = ['settings', 'nodes', 'locations', 'mounts', 'nests', 'eggs'];
            foreach ($locked as $route) {
                if (str_contains($path, $route) && in_array($method, ['POST', 'PUT', 'PATCH', 'DELETE'])) {
                    $this->logBlock($user->id, $path, $method, 'Anti-Sabotase');
                    return $this->deny('Sistem dikunci SANZY PROTECT.');
                }
            }
        }
        
        // LEVEL 2: Anti-User Tampering
        if ($level >= 2 && preg_match('#api/application/users/(\d+)#', $path, $m)) {
            if ((int)$m[1] !== $user->id && in_array($method, ['PUT', 'PATCH', 'DELETE'])) {
                $this->logBlock($user->id, $path, $method, 'Anti-UserTamper');
                return $this->deny('Modifikasi user lain diblokir.');
            }
        }
        
        // LEVEL 3: Anti-Server Delete
        if ($level >= 3 && preg_match('#api/application/servers/(\d+)#', $path) && $method === 'DELETE') {
            $this->logBlock($user->id, $path, $method, 'Anti-ServerDelete');
            return $this->deny('Hapus server diblokir.');
        }
        
        // LEVEL 4: Anti-2FA
        if ($level >= 4 && str_contains($path, '2fa') && in_array($method, ['POST', 'PUT', 'DELETE'])) {
            $this->logBlock($user->id, $path, $method, 'Anti-2FA');
            return $this->deny('Modifikasi 2FA diblokir.');
        }
        
        // LEVEL 5-6: Anti-File Access
        if ($level >= 5 && preg_match('#api/client/servers/([a-f0-9-]+)/files#', $path, $m)) {
            if (!$this->isServerOwner($user->id, $m[1])) {
                if ($level >= 6 && in_array($method, ['POST', 'PUT', 'PATCH', 'DELETE'])) {
                    $this->logBlock($user->id, $path, $method, 'Anti-FileEdit');
                    return $this->deny('Edit file server diblokir.');
                }
                if ($method === 'GET') {
                    $this->logBlock($user->id, $path, $method, 'Anti-FileView');
                    return $this->deny('Lihat file server diblokir.');
                }
            }
        }
        
        // LEVEL 7: Anti-Server View
        if ($level >= 7 && preg_match('#api/client/servers/([a-f0-9-]+)#', $path, $m)) {
            if (!$this->isServerOwner($user->id, $m[1]) && $method === 'GET') {
                $this->logBlock($user->id, $path, $method, 'Anti-ServerView');
                return $this->deny('Akses server orang lain diblokir.');
            }
        }
        
        // LEVEL 8: Whitelist Admin
        if ($level >= 8) {
            $data = $request->all();
            if ((isset($data['root_admin']) && $data['root_admin']) || 
                str_contains($path, 'admin/users/new')) {
                if (!$this->isWhitelisted($user->id)) {
                    $this->logBlock($user->id, $path, $method, 'Anti-AdminCreate');
                    return $this->deny('Buat admin hanya untuk whitelist.');
                }
            }
        }
        
        // LEVEL 9: Auto-Filter
        if ($level >= 9) {
            if (str_contains($path, 'api/application/users') && $method === 'GET') {
                $request->merge(['filter[email]' => $user->email]);
            }
            if (str_contains($path, 'api/application/servers') && $method === 'GET') {
                $request->merge(['filter[owner_id]' => $user->id]);
            }
        }
        
        return $next($request);
    }
    
    private function deny(string $message)
    {
        if (request()->expectsJson()) {
            return response()->json(['errors' => [[
                'code' => 'SanzyProtect',
                'status' => '403',
                'detail' => '🛡️ ' . $message
            ]]], 403);
        }
        return back()->with('error', '🛡️ ' . $message)->setStatusCode(403);
    }
    
    private function isServerOwner(int $userId, string $uuid): bool
    {
        return Cache::remember("sanzy:owner:{$userId}:{$uuid}", self::CACHE_TTL, function() use ($userId, $uuid) {
            $server = Server::where('uuid', $uuid)->first(['owner_id']);
            return $server && $server->owner_id === $userId;
        });
    }
    
    private function isWhitelisted(int $adminId): bool
    {
        return Cache::remember("sanzy:whitelist:{$adminId}", self::CACHE_TTL, function() use ($adminId) {
            if (!file_exists(self::WHITELIST_FILE)) return false;
            $data = @json_decode(file_get_contents(self::WHITELIST_FILE), true);
            return in_array($adminId, $data['admins'] ?? [], true);
        });
    }
    
    private function logBlock(int $userId, string $path, string $method, string $reason)
    {
        Log::warning("[SANZY PROTECT] User #{$userId} blocked: {$reason} | {$method} {$path}");
    }
}
