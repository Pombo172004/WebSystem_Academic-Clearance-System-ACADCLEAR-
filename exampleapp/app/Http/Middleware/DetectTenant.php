<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Services\TenantService;
use Illuminate\Support\Facades\Storage;

class DetectTenant
{
    protected $tenantService;

    public function __construct(TenantService $tenantService)
    {
        $this->tenantService = $tenantService;
    }

    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next)
    {
        // Detect tenant from subdomain
        $tenantSlug = $this->tenantService->getCurrentTenant();
        
        // Store tenant info for this request
        $request->attributes->set('tenant_slug', $tenantSlug);
        
        // Get tenant details from Central App
        $tenantDetails = $this->tenantService->getTenantDetails($tenantSlug);
        $tenantFeatures = $this->tenantService->getTenantFeatures($tenantSlug);
        
        $request->attributes->set('tenant_details', $tenantDetails);
        $request->attributes->set('tenant_features', $tenantFeatures);
        
        // Share with all views
        $tenantLocalLogoUrl = null;
        $tenantBranding = [];

        $brandingPath = "tenant-branding/{$tenantSlug}.json";
        if (Storage::disk('public')->exists($brandingPath)) {
            $decoded = json_decode(Storage::disk('public')->get($brandingPath), true);
            if (is_array($decoded)) {
                $tenantBranding = $decoded;
            }
        }

        foreach (['png', 'jpg', 'jpeg', 'webp', 'gif'] as $extension) {
            $candidatePath = "tenant-branding/{$tenantSlug}.{$extension}";
            if (Storage::disk('public')->exists($candidatePath)) {
                $tenantLocalLogoUrl = asset('storage/' . $candidatePath);
                break;
            }
        }

        if (!$tenantLocalLogoUrl && !empty($tenantBranding['logo'])) {
            $tenantLocalLogoUrl = asset('storage/' . ltrim($tenantBranding['logo'], '/'));
        }

        // 60-30-10 rule: primary = sidebar (60%), accent = buttons/icons (30%), black = text/details (10%)
        $tenantPrimaryColor  = $tenantBranding['custom_primary'] ?? '#122C4F';  // default: Midnight blue
        $tenantAccentColor   = $tenantBranding['custom_accent']  ?? '#5B88B2';  // default: Ocean blue

        // Validate hex format – fall back to defaults on bad data
        $hexPattern = '/^#[0-9A-Fa-f]{6}$/';
        if (!preg_match($hexPattern, $tenantPrimaryColor)) {
            $tenantPrimaryColor = '#122C4F';
        }
        if (!preg_match($hexPattern, $tenantAccentColor)) {
            $tenantAccentColor = '#5B88B2';
        }

        view()->share('currentTenant', $tenantDetails);
        view()->share('tenantFeatures', $tenantFeatures);
        view()->share('tenantLocalLogoUrl', $tenantLocalLogoUrl);
        view()->share('tenantBranding', $tenantBranding);
        view()->share('tenantColorScheme', 'custom'); // kept for legacy blade references
        view()->share('tenantPrimaryColor', $tenantPrimaryColor);
        view()->share('tenantAccentColor', $tenantAccentColor);
        
        return $next($request);
    }
}