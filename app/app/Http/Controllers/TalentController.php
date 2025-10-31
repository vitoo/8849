<?php

namespace App\Http\Controllers;

use App\Jobs\AyonSyncJob;
use App\Models\Talent;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;

class TalentController extends Controller
{
    public function index()
    {
        $perPage = 10; // items per page
        $paginated = Talent::orderBy('created_at', 'desc')
            ->paginate($perPage)
            ->through(fn ($talent) => [
                'id' => $talent->id,
                'username' => $talent->username,
                'first_name' => $talent->first_name,
                'last_name' => $talent->last_name,
                'email' => $talent->email,
                'synced_at' => $talent->synced_at?->format('Y-m-d H:i:s'),
                'sync_status' => $talent->sync_status,
                'is_synced' => $talent->is_synced,
            ]);

        return Inertia::render('Talents/Index', [
            'talents' => $paginated,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email|unique:talents,email',
        ]);

        // Generate base username
        $baseUsername = Str::slug($validated['first_name'].' '.$validated['last_name']);
        $username = $baseUsername;
        $i = 1;

        // Ensure uniqueness
        while (Talent::where('username', $username)->exists()) {
            $username = $baseUsername.'-'.$i++;
        }

        Talent::create([
            ...$validated,
            'username' => $username,
        ]);

        return redirect()->route('talents.index')
            ->with('success', 'Talent créé avec succès');
    }

    public function update(Request $request, Talent $talent)
    {
        $validated = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email|unique:talents,email,'.$talent->id,
        ]);

        $talent->update($validated);

        return redirect()->route('talents.index');
    }

    public function destroy(Talent $talent)
    {
        $user = auth()->user();
        abort_unless($user->is_admin, 403, 'Vous n\'êtes pas autorisé à supprimer un talent.');
        $talent->delete();

        return redirect()->route('talents.index');
    }

    /**
     * Resynchronise un talent spécifique
     */
    public function sync(Talent $talent)
    {
        AyonSyncJob::dispatch($talent, 'update');

        return redirect()->route('talents.index')
            ->with('success', 'Synchronisation du talent lancée');
    }
}
