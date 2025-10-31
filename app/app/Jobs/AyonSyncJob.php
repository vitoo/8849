<?php

namespace App\Jobs;

use App\Models\Talent;
// use App\Services\AyonService;
use Vendor\AyonApi\AyonService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Exception;

class AyonSyncJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 2;
    public $timeout = 120;

    public function __construct(
        private ?Talent $talent = null,
        private ?string $action = null
    ) {}

    public function handle(AyonService $ayonService): void
    {
        // Si aucun talent spécifié, synchroniser tous les talents
        if ($this->talent === null) {
            $this->syncAll($ayonService);
            return;
        }

        // Synchroniser un talent spécifique
        $this->syncTalent($ayonService, $this->talent, $this->action);
    }

    /**
     * Synchronise tous les talents qui ont besoin d'être synchronisés
     */
    private function syncAll(AyonService $ayonService): void
    {
        Talent::all()->each(function ($talent) use ($ayonService) {
            if ($talent->needsSync()) {
                $this->syncTalent($ayonService, $talent);
            }
        });
    }

    /**
     * Synchronise un talent spécifique
     */
    private function syncTalent(AyonService $ayonService, Talent $talent, ?string $action = null): void
    {
        try {
            if ($action === 'delete') {
                // Suppression
                $ayonService->deleteUser($talent->username);
                Log::info("Talent deleted from AYON: {$talent->username}");
                return;
            }

            // Vérifier si synchronisation nécessaire
            if (!$talent->needsSync() && $action !== 'create') {
                Log::info("Talent {$talent->username} already synced, skipping");
                return;
            }

            $fullName = trim("{$talent->first_name} {$talent->last_name}");

            // Création ou mise à jour (idempotent grâce à AyonService)
            if ($action === 'create' || !$ayonService->userExists($talent->username)) {
                Log::info("Creating talent in AYON: {$talent->username}");
                $ayonService->createUser(
                    $talent->username,
                    $fullName,
                    $talent->email
                );
                Log::info("Talent created in AYON: {$talent->username}");
            } else {
                $ayonService->updateUser(
                    $talent->username,
                    $fullName,
                    $talent->email
                );
                Log::info("Talent updated in AYON: {$talent->username}");
            }

            // Marquer comme synchronisé
            $talent->withoutEvents(function () use ($talent) {
                $talent->update(['synced_at' => now()]);
            });
        } catch (Exception $e) {
            Log::error("Failed to sync talent {$talent->username}: " . $e->getMessage());
            throw $e;
        }
    }

    public function failed(\Throwable $exception): void
    {
        Log::error('AyonSyncJob failed: ' . $exception->getMessage());
    }
}
