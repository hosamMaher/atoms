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
    public function request($service, $method, $endpoint, $data = [], $headers = []) {
        try {
            $baseUrl = $this->resolveService($service);

            $url = $baseUrl . '/' . ltrim($endpoint, '/');

            $http = Http::withHeaders($headers)->timeout(10);

            $response = null;
            switch (strtolower($method)) {
                case 'get':
                    $response = $http->get($url, $data);
                    break;
                case 'post':
                    $response = $http->post($url, $data);
                    break;
                case 'put':
                    $response = $http->put($url, $data);
                    break;
                case 'delete':
                    $response = $http->delete($url);
                    break;
                default:
                    throw new \Exception('Invalid HTTP method');
            }

            // Check if request was successful
            if ($response->successful()) {
                return $response->json();
            }

            // Log error and return error response
            \Log::error("WSO2 request failed: {$method} {$url}", [
                'status' => $response->status(),
                'body' => $response->body()
            ]);

            return [
                'status' => false,
                'message' => 'Service unavailable',
                'error' => $response->status() >= 500 ? 'Internal server error' : 'Request failed'
            ];

        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            // Service is down or unreachable
            \Log::error("WSO2 service unreachable: {$service}", [
                'endpoint' => $endpoint,
                'error' => $e->getMessage()
            ]);

            return [
                'status' => false,
                'message' => 'Service temporarily unavailable',
                'error' => 'Connection failed'
            ];

        } catch (\Exception $e) {
            \Log::error("WSO2 request error: {$service}", [
                'endpoint' => $endpoint,
                'error' => $e->getMessage()
            ]);

            return [
                'status' => false,
                'message' => 'Request failed',
                'error' => $e->getMessage()
            ];
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

