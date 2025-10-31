<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Exception;
use Illuminate\Support\Facades\Log;

class AyonService
{
    private string $baseUrl;
    private ?string $token = null;

    public function __construct()
    {
        $hostname = config('services.ayon.hostname', env('AYON_HOSTNAME', 'ayon:5000'));
        $this->baseUrl = "http://{$hostname}/api";
    }

    /**
     * Authentification et récupération du token
     */
    private function authenticate(): string
    {
        // Cache le token pendant 1 heure
        return Cache::remember('ayon_auth_token', 3600, function () {
            $response = Http::post("{$this->baseUrl}/auth/login", [
                'name' => config('services.ayon.username', 'admin'),
                'password' => config('services.ayon.password', 'admin'),
            ]);

            if (!$response->successful()) {
                throw new Exception("AYON authentication failed: " . $response->body());
            }

            return $response->json('token');
        });
    }

    /**
     * Effectue une requête HTTP avec authentification
     */
    private function request(string $method, string $endpoint, array $data = [])
    {
        $token = $this->authenticate();
        $response = Http::withToken($token)
            ->acceptJson()
            ->asJson()
            ->$method("{$this->baseUrl}{$endpoint}", $data);

        if ($response->status() === 404) {
            throw new Exception("404 Not Found");
        }

        if (!$response->successful()) {
            throw new Exception("AYON API error ({$response->status()}): " . $response->body());
        }

        return $response->json();
    }

    /**
     * Vérifie si un utilisateur existe
     */
    public function userExists(string $username): bool
    {
        $user = $this->getUser($username);
        return $user !== null;
    }

    /**
     * Récupère un utilisateur
     */
    public function getUser(string $username): ?array
    {
        try {
            $response = $this->request('get', "/users/{$username}");
            return $response;
        } catch (Exception $e) {
            // Si c’est une 404, on renvoie null (utilisateur non trouvé)
            if (str_contains($e->getMessage(), '404')) {
                return null;
            }

            // Pour d'autres erreurs, on relance
            throw $e;
        }
    }

    /**
     * Crée un utilisateur (idempotent)
     */
    public function createUser(string $username, string $fullName, string $email): ?array
    {
        // Vérification d'idempotence
        if ($this->userExists($username)) {
            return $this->updateUser($username, $fullName, $email);
        }

        return $this->request('put', "/users/{$username}", [
            'attrib' => [
                'fullName' => $fullName,
                'email' => $email,
            ],
        ]);
    }

    /**
     * Met à jour un utilisateur
     */
    public function updateUser(string $username, string $fullName, string $email): ?array
    {
        return $this->request('patch', "/users/{$username}", [
            'attrib' => [
                'fullName' => $fullName,
                'email' => $email,
            ],
        ]);
    }

    /**
     * Supprime un utilisateur
     */
    public function deleteUser(string $username): ?array
    {
        return $this->request('delete', "/users/{$username}");
    }
}
