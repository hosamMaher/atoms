<?php

namespace App\Services\Integration;

use Illuminate\Support\Facades\Http;

class WSO2Service {

    protected $enabled;
    protected $gatewayUrl;
    protected $guestUrl;
    protected $categoryUrl;
    protected $userUrl;

    public function __construct() {
        $this->enabled = env('WSO2_ENABLED', false);
        $this->gatewayUrl = env('WSO2_GATEWAY_URL');
        $this->guestUrl = rtrim(env('WSO2_GUEST_URL', 'http://localhost:8002/api/v1'), '/');
        $this->categoryUrl = rtrim(env('WSO2_CATEGORY_URL', 'http://localhost:8001/api/v1'), '/');
        $this->userUrl = rtrim(env('WSO2_USER_URL', 'http://localhost:8003/api/v1'), '/');
    }

    /** Generic request handler */
    public function request($service, $method, $endpoint, $data = []) {
        $baseUrl = $this->resolveService($service);

        $url = $baseUrl . '/' . ltrim($endpoint, '/');

        switch (strtolower($method)) {
            case 'get':
                return Http::get($url, $data)->json();
            case 'post':
                return Http::post($url, $data)->json();
            case 'put':
                return Http::put($url, $data)->json();
            case 'delete':
                return Http::delete($url)->json();
            default:
                throw new \Exception('Invalid HTTP method');
        }
    }

    /** Resolve which service URL to use */
    private function resolveService($service) {

        if ($this->enabled && $this->gatewayUrl) {
            return rtrim($this->gatewayUrl, '/');
        }

        return match ($service) {
            'guest' => $this->guestUrl,
            'category' => $this->categoryUrl,
            'user' => $this->userUrl,
            default => throw new \Exception('Unknown service: ' . $service)
        };
    }
}

