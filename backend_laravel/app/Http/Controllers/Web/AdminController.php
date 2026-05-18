<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\InferenceRecord;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdminController extends Controller
{
    public function dashboard(Request $request)
    {
        $admin = $request->user();
        $institutionId = $admin->institution_id;

        $usersQuery = User::where('institution_id', $institutionId);
        $totalUsers = $usersQuery->count();
        $activeUsers = $usersQuery->clone()
            ->whereHas('inferenceRecords', fn ($q) => $q->where('created_at', '>=', now()->subDays(7)))
            ->count();

        $recordsQuery = InferenceRecord::whereHas('user', fn ($q) => $q->where('institution_id', $institutionId));
        $totalRecords = $recordsQuery->clone()->count();

        $avgProbability = $recordsQuery->clone()
            ->whereNotNull('predicted_probability')
            ->avg('predicted_probability');

        $records = $recordsQuery->clone()
            ->whereNotNull('predicted_probability')
            ->select('predicted_probability')
            ->get();

        $levels = ['low' => 0, 'moderate' => 0, 'high' => 0];
        foreach ($records as $r) {
            $pct = round($r->predicted_probability * 100);
            if ($pct > 65) $levels['high']++;
            elseif ($pct > 40) $levels['moderate']++;
            else $levels['low']++;
        }
        $levelsTotal = array_sum($levels) ?: 1;

        $dailyActivity = $recordsQuery->clone()
            ->where('inference_records.created_at', '>=', now()->subDays(29)->startOfDay())
            ->selectRaw('DATE(inference_records.created_at) as date, COUNT(*) as total')
            ->groupByRaw('DATE(inference_records.created_at)')
            ->orderBy('date')
            ->pluck('total', 'date');

        $activityChart = collect();
        for ($i = 29; $i >= 0; $i--) {
            $date = now()->subDays($i)->format('Y-m-d');
            $activityChart->put($date, $dailyActivity->get($date, 0));
        }

        $users = User::where('institution_id', $institutionId)
            ->where('id', '!=', $admin->id)
            ->withCount('inferenceRecords')
            ->withAvg('inferenceRecords', 'predicted_probability')
            ->with(['inferenceRecords' => fn ($q) => $q->latest()->limit(1)])
            ->orderByDesc('inference_records_count')
            ->get();

        return view('admin.dashboard', compact(
            'admin', 'totalUsers', 'activeUsers', 'totalRecords',
            'avgProbability', 'levels', 'levelsTotal', 'activityChart', 'users',
        ));
    }

    public function users(Request $request)
    {
        $admin = $request->user();
        $query = User::where('institution_id', $admin->institution_id)
            ->where('id', '!=', $admin->id)
            ->withCount('inferenceRecords')
            ->withAvg('inferenceRecords', 'predicted_probability');

        if ($search = $request->input('search')) {
            $query->where(fn ($q) => $q->where('name', 'like', "%{$search}%")->orWhere('email', 'like', "%{$search}%"));
        }

        if ($status = $request->input('status')) {
            if ($status === 'active') {
                $query->whereHas('inferenceRecords', fn ($q) => $q->where('created_at', '>=', now()->subDays(7)));
            } elseif ($status === 'inactive') {
                $query->whereDoesntHave('inferenceRecords', fn ($q) => $q->where('created_at', '>=', now()->subDays(7)));
            }
        }

        $users = $query->latest()->paginate(20)->withQueryString();

        return view('admin.users', compact('admin', 'users'));
    }

    public function userDetail(Request $request, User $user)
    {
        $admin = $request->user();

        if ($user->institution_id !== $admin->institution_id) {
            abort(403);
        }

        $user->loadCount('inferenceRecords');

        $records = $user->inferenceRecords()->latest()->paginate(20);

        $avgProbability = $user->inferenceRecords()
            ->whereNotNull('predicted_probability')
            ->avg('predicted_probability');

        $monthlyActivity = $user->inferenceRecords()
            ->where('created_at', '>=', now()->subDays(29)->startOfDay())
            ->selectRaw('DATE(created_at) as date, COUNT(*) as total')
            ->groupByRaw('DATE(created_at)')
            ->orderBy('date')
            ->pluck('total', 'date');

        $userChart = collect();
        for ($i = 29; $i >= 0; $i--) {
            $date = now()->subDays($i)->format('Y-m-d');
            $userChart->put($date, $monthlyActivity->get($date, 0));
        }

        $weeklyAvg = $user->inferenceRecords()
            ->where('created_at', '>=', now()->subWeeks(4))
            ->whereNotNull('predicted_probability')
            ->selectRaw(DB::getDriverName() === 'sqlite'
                ? 'strftime("%W", created_at) as week, AVG(predicted_probability) as avg_prob'
                : 'WEEK(created_at) as week, AVG(predicted_probability) as avg_prob')
            ->groupByRaw(DB::getDriverName() === 'sqlite'
                ? 'strftime("%W", created_at)'
                : 'WEEK(created_at)')
            ->orderBy('week')
            ->pluck('avg_prob', 'week');

        return view('admin.user-detail', compact('user', 'records', 'avgProbability', 'userChart', 'weeklyAvg'));
    }

    public function updateUser(Request $request, User $user)
    {
        $admin = $request->user();

        if ($user->institution_id !== $admin->institution_id) {
            abort(403);
        }

        $validated = $request->validate([
            'action' => 'required|in:toggle_access,set_role,add_note',
            'role' => 'nullable|in:user,psychologist',
            'note' => 'nullable|string|max:500',
        ]);

        switch ($validated['action']) {
            case 'toggle_access':
                $newRole = $user->role === 'user' ? 'psychologist' : 'user';
                $user->update(['role' => $newRole]);
                break;
            case 'set_role':
                if ($validated['role']) {
                    $user->update(['role' => $validated['role']]);
                }
                break;
        }

        return back()->with('success', "Usuario {$user->name} actualizado.");
    }

    public function groupAction(Request $request)
    {
        $admin = $request->user();

        $validated = $request->validate([
            'user_ids' => 'required|array',
            'user_ids.*' => 'integer|exists:users,id',
            'action' => 'required|in:activate,deactivate,export',
        ]);

        $users = User::where('institution_id', $admin->institution_id)
            ->whereIn('id', $validated['user_ids'])
            ->get();

        switch ($validated['action']) {
            case 'activate':
                $users->each(fn ($u) => $u->update(['role' => 'user']));
                $msg = count($users) . ' usuarios activados.';
                break;
            case 'deactivate':
                $users->each(fn ($u) => $u->update(['role' => 'user']));
                $msg = count($users) . ' usuarios procesados.';
                break;
            case 'export':
                return $this->exportUsers($users);
        }

        return back()->with('success', $msg ?? 'Acción completada.');
    }

    public function sessions(Request $request)
    {
        $admin = $request->user();

        $records = InferenceRecord::whereHas('user', fn ($q) => $q->where('institution_id', $admin->institution_id))
            ->with('user')
            ->latest()
            ->paginate(30);

        return view('admin.sessions', compact('admin', 'records'));
    }

    public function reports(Request $request)
    {
        $admin = $request->user();
        $institutionId = $admin->institution_id;

        $period = $request->input('period', '30');
        $since = now()->subDays((int) $period);

        $totalSessions = InferenceRecord::whereHas('user', fn ($q) => $q->where('institution_id', $institutionId))
            ->where('created_at', '>=', $since)->count();

        $activeUsersInPeriod = User::where('institution_id', $institutionId)
            ->whereHas('inferenceRecords', fn ($q) => $q->where('created_at', '>=', $since))
            ->count();

        $avgAnxiety = InferenceRecord::whereHas('user', fn ($q) => $q->where('institution_id', $institutionId))
            ->where('created_at', '>=', $since)
            ->whereNotNull('predicted_probability')
            ->avg('predicted_probability');

        $topUsers = User::where('institution_id', $institutionId)
            ->whereHas('inferenceRecords', fn ($q) => $q->where('created_at', '>=', $since))
            ->withCount(['inferenceRecords' => fn ($q) => $q->where('created_at', '>=', $since)])
            ->withAvg(['inferenceRecords' => fn ($q) => $q->where('created_at', '>=', $since)->whereNotNull('predicted_probability')], 'predicted_probability')
            ->orderByDesc('inference_records_count')
            ->limit(10)
            ->get();

        $levelDist = InferenceRecord::whereHas('user', fn ($q) => $q->where('institution_id', $institutionId))
            ->where('created_at', '>=', $since)
            ->whereNotNull('predicted_probability')
            ->get()
            ->groupBy(fn ($r) => $r->predicted_probability * 100 > 65 ? 'high' : ($r->predicted_probability * 100 > 40 ? 'moderate' : 'low'))
            ->map->count();

        return view('admin.reports', compact('admin', 'totalSessions', 'activeUsersInPeriod', 'avgAnxiety', 'topUsers', 'levelDist', 'period'));
    }

    public function deleteSession(Request $request, InferenceRecord $record)
    {
        $admin = $request->user();

        if (!$record->user || $record->user->institution_id !== $admin->institution_id) {
            abort(403);
        }

        $record->delete();

        return back()->with('success', 'Sesión eliminada.');
    }

    public function exportSessions(Request $request)
    {
        $admin = $request->user();

        $records = InferenceRecord::whereHas('user', fn ($q) => $q->where('institution_id', $admin->institution_id))
            ->with('user')
            ->latest()
            ->get();

        $csv = "Fecha,Usuario,Email,Texto,Respuesta IA,Ansiedad %,Audio\n";
        foreach ($records as $r) {
            $pct = $r->predicted_probability !== null ? round($r->predicted_probability * 100) : '';
            $text = str_replace('"', '""', $r->input_text ?? '');
            $resp = str_replace('"', '""', $r->generated_text ?? '');
            $csv .= "{$r->created_at->format('Y-m-d H:i')},\"{$r->user?->name}\",{$r->user?->email},\"{$text}\",\"{$resp}\",{$pct}," . ($r->audio_filename ? 'Sí' : 'No') . "\n";
        }

        return response($csv, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="sesiones_' . now()->format('Y-m-d') . '.csv"',
        ]);
    }

    public function institution(Request $request)
    {
        $admin = $request->user();
        $institution = $admin->institution;

        if (!$institution) {
            return view('admin.institution-empty', compact('admin'));
        }

        $institution->loadCount('users');

        return view('admin.institution', compact('admin', 'institution'));
    }

    public function updateInstitution(Request $request)
    {
        $admin = $request->user();
        $institution = $admin->institution;

        if (!$institution) {
            abort(404);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'contact_email' => 'nullable|email|max:255',
        ]);

        $institution->update($validated);

        return back()->with('success', 'Información de la institución actualizada.');
    }

    private function exportUsers($users)
    {
        $csv = "Nombre,Email,Rol,Interacciones,Última actividad\n";
        foreach ($users as $u) {
            $lastActivity = $u->inferenceRecords()->latest()->first()?->created_at?->format('Y-m-d') ?? 'N/A';
            $count = $u->inferenceRecords()->count();
            $csv .= "\"{$u->name}\",{$u->email},{$u->role},{$count},{$lastActivity}\n";
        }

        return response($csv, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="usuarios_' . now()->format('Y-m-d') . '.csv"',
        ]);
    }
}
