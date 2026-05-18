<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Group;
use App\Models\InferenceRecord;
use App\Models\Institution;
use App\Models\Plan;
use App\Models\PlanRequest;
use App\Models\ProOrder;
use App\Models\Subscription;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SuperAdminController extends Controller
{
    public function dashboard()
    {
        $totalUsers = User::count();
        $activeUsers = User::whereHas('inferenceRecords', fn ($q) => $q->where('created_at', '>=', now()->subDays(7)))->count();
        $totalRecords = InferenceRecord::count();
        $totalInstitutions = Institution::count();

        $avgProbability = InferenceRecord::whereNotNull('predicted_probability')->avg('predicted_probability');

        $now = Carbon::now()->toDateTimeString();
        $planDistribution = DB::table(DB::raw('(SELECT COALESCE(
                (SELECT p.slug FROM subscriptions s
                 JOIN plans p ON p.id = s.plan_id
                 WHERE s.user_id = users.id AND s.status = \'active\'
                 AND (s.expires_at IS NULL OR s.expires_at >= ?)
                 ORDER BY s.created_at DESC LIMIT 1),
                \'free\'
            ) as plan_slug FROM users) as user_plans'))
            ->selectRaw('plan_slug, COUNT(*) as total')
            ->setBindings([$now])
            ->groupBy('plan_slug')
            ->pluck('total', 'plan_slug');

        $dailyActivity = InferenceRecord::where('created_at', '>=', now()->subDays(29)->startOfDay())
            ->selectRaw('DATE(created_at) as date, COUNT(*) as total')
            ->groupByRaw('DATE(created_at)')
            ->orderBy('date')
            ->pluck('total', 'date');

        $activityChart = collect();
        for ($i = 29; $i >= 0; $i--) {
            $date = now()->subDays($i)->format('Y-m-d');
            $activityChart->put($date, $dailyActivity->get($date, 0));
        }

        $dailyRegistrations = User::where('created_at', '>=', now()->subDays(29)->startOfDay())
            ->selectRaw('DATE(created_at) as date, COUNT(*) as total')
            ->groupByRaw('DATE(created_at)')
            ->orderBy('date')
            ->pluck('total', 'date');

        $registrationChart = collect();
        for ($i = 29; $i >= 0; $i--) {
            $date = now()->subDays($i)->format('Y-m-d');
            $registrationChart->put($date, $dailyRegistrations->get($date, 0));
        }

        $recentUsers = User::withCount('inferenceRecords')
            ->latest()
            ->limit(10)
            ->get();

        $institutions = Institution::withCount('users')->get();

        return view('superadmin.dashboard', compact(
            'totalUsers', 'activeUsers', 'totalRecords', 'totalInstitutions',
            'avgProbability', 'planDistribution', 'activityChart', 'registrationChart',
            'recentUsers', 'institutions',
        ));
    }

    public function users(Request $request)
    {
        $query = User::withCount('inferenceRecords')
            ->with(['subscriptions' => fn ($q) => $q->where('status', 'active')->latest()->limit(1), 'subscriptions.plan', 'institution']);

        if ($search = $request->input('search')) {
            $query->where(fn ($q) => $q->where('name', 'like', "%{$search}%")->orWhere('email', 'like', "%{$search}%"));
        }

        if ($role = $request->input('role')) {
            $query->where('role', $role);
        }

        if ($plan = $request->input('plan')) {
            if ($plan === 'free') {
                $query->whereDoesntHave('subscriptions', fn ($q) => $q->where('status', 'active')
                    ->where(fn ($sq) => $sq->whereNull('expires_at')->orWhere('expires_at', '>=', now())));
            } else {
                $query->whereHas('subscriptions', fn ($q) => $q->where('status', 'active')
                    ->where(fn ($sq) => $sq->whereNull('expires_at')->orWhere('expires_at', '>=', now()))
                    ->whereHas('plan', fn ($pq) => $pq->where('slug', $plan)));
            }
        }

        $users = $query->latest()->paginate(25)->withQueryString();
        $plans = Plan::all();
        $institutions = Institution::all();

        return view('superadmin.users', compact('users', 'plans', 'institutions'));
    }

    public function updateUser(Request $request, User $user)
    {
        $validated = $request->validate([
            'action' => 'required|in:set_plan,set_roles,set_institution,toggle_status',
            'plan_slug' => 'required_if:action,set_plan|nullable|string',
            'roles' => 'required_if:action,set_roles|nullable|array',
            'roles.*' => 'in:user,psychologist,admin,superadmin',
            'institution_id' => 'required_if:action,set_institution|nullable|integer',
        ]);

        switch ($validated['action']) {
            case 'set_plan':
                $this->assignPlan($user, $validated['plan_slug']);
                break;

            case 'set_roles':
                $roles = $validated['roles'] ?? ['user'];
                $user->syncRoles($roles);
                break;

            case 'set_institution':
                $user->update(['institution_id' => $validated['institution_id'] ?: null]);
                break;

            case 'toggle_status':
                $sub = $user->subscriptions()->where('status', 'active')->latest()->first();
                if ($sub) {
                    $sub->update(['status' => 'cancelled']);
                }
                break;
        }

        return back()->with('success', "Usuario {$user->name} actualizado correctamente.");
    }

    public function userDetail(User $user)
    {
        $user->loadCount('inferenceRecords');

        $records = $user->inferenceRecords()->latest()->paginate(20);

        $avgProbability = $user->inferenceRecords()
            ->whereNotNull('predicted_probability')
            ->avg('predicted_probability');

        $activePlan = $user->activePlan();

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

        return view('superadmin.user-detail', compact('user', 'records', 'avgProbability', 'activePlan', 'userChart'));
    }

    public function institutions()
    {
        $institutions = Institution::withCount('users')->latest()->get();
        return view('superadmin.institutions', compact('institutions'));
    }

    public function storeInstitution(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:100|unique:institutions,slug',
            'description' => 'nullable|string|max:1000',
            'contact_email' => 'nullable|email|max:255',
        ]);

        Institution::create($validated);

        return back()->with('success', "Institución {$validated['name']} creada correctamente.");
    }

    public function editInstitution(Institution $institution)
    {
        $institution->loadCount('users');
        return view('superadmin.institution-edit', compact('institution'));
    }

    public function updateInstitution(Request $request, Institution $institution)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:100|unique:institutions,slug,' . $institution->id,
            'description' => 'nullable|string|max:1000',
            'contact_email' => 'nullable|email|max:255',
        ]);

        $institution->update($validated);

        return redirect()->route('superadmin.institutions')->with('success', "Institución {$institution->name} actualizada.");
    }

    // --- Sessions ---

    public function sessions(Request $request)
    {
        $query = InferenceRecord::with('user');

        if ($search = $request->input('search')) {
            $query->whereHas('user', fn ($q) => $q->where('name', 'like', "%{$search}%")->orWhere('email', 'like', "%{$search}%"));
        }

        $records = $query->latest()->paginate(30)->withQueryString();

        return view('superadmin.sessions', compact('records'));
    }

    public function deleteSession(InferenceRecord $record)
    {
        $record->delete();
        return back()->with('success', 'Sesión eliminada.');
    }

    public function exportSessions()
    {
        $records = InferenceRecord::with('user')->latest()->get();

        $csv = "Fecha,Usuario,Email,Texto,Respuesta IA,Ansiedad %,Audio\n";
        foreach ($records as $r) {
            $pct = $r->predicted_probability !== null ? round($r->predicted_probability * 100) : '';
            $text = str_replace('"', '""', $r->input_text ?? '');
            $resp = str_replace('"', '""', $r->generated_text ?? '');
            $csv .= "{$r->created_at->format('Y-m-d H:i')},\"{$r->user?->name}\",{$r->user?->email},\"{$text}\",\"{$resp}\",{$pct}," . ($r->audio_filename ? 'Sí' : 'No') . "\n";
        }

        return response($csv, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="sesiones_global_' . now()->format('Y-m-d') . '.csv"',
        ]);
    }

    // --- Plan Requests ---

    public function planRequests(Request $request)
    {
        $query = PlanRequest::with(['user', 'plan', 'reviewer']);

        if ($status = $request->input('status')) {
            $query->where('status', $status);
        } else {
            $query->where('status', 'pending');
        }

        $requests = $query->latest()->paginate(20)->withQueryString();

        return view('superadmin.plan-requests', compact('requests'));
    }

    public function reviewPlanRequest(Request $request, PlanRequest $planRequest)
    {
        $validated = $request->validate([
            'action' => 'required|in:approve,reject',
            'admin_notes' => 'nullable|string|max:500',
        ]);

        $planRequest->update([
            'status' => $validated['action'] === 'approve' ? 'approved' : 'rejected',
            'admin_notes' => $validated['admin_notes'],
            'reviewed_at' => now(),
            'reviewed_by' => $request->user()->id,
        ]);

        if ($validated['action'] === 'approve') {
            $this->assignPlan($planRequest->user, $planRequest->plan->slug);
        }

        return back()->with('success', 'Solicitud ' . ($validated['action'] === 'approve' ? 'aprobada' : 'rechazada') . '.');
    }

    // --- Subscriptions ---

    public function subscriptions(Request $request)
    {
        $query = Subscription::with(['user', 'plan']);

        if ($planFilter = $request->input('plan')) {
            $query->whereHas('plan', fn ($q) => $q->where('slug', $planFilter));
        }

        if ($statusFilter = $request->input('status')) {
            $query->where('status', $statusFilter);
        }

        $subscriptions = $query->latest()->paginate(25)->withQueryString();
        $plans = Plan::all();

        return view('superadmin.subscriptions', compact('subscriptions', 'plans'));
    }

    public function updateSubscription(Request $request, Subscription $subscription)
    {
        $validated = $request->validate([
            'action' => 'required|in:cancel,activate,extend',
            'days' => 'nullable|integer|min:1|max:365',
        ]);

        switch ($validated['action']) {
            case 'cancel':
                $subscription->update(['status' => 'cancelled']);
                break;
            case 'activate':
                $subscription->update(['status' => 'active']);
                break;
            case 'extend':
                $days = $validated['days'] ?? 30;
                $base = $subscription->expires_at ?? now();
                $subscription->update(['expires_at' => Carbon::parse($base)->addDays($days)]);
                break;
        }

        return back()->with('success', 'Suscripción actualizada.');
    }

    // --- Groups ---

    public function groups(Request $request)
    {
        $groups = Group::with('institution')
            ->withCount('users')
            ->latest()
            ->get();

        $institutions = Institution::all();

        return view('superadmin.groups', compact('groups', 'institutions'));
    }

    public function storeGroup(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'institution_id' => 'required|exists:institutions,id',
            'description' => 'nullable|string|max:1000',
        ]);

        Group::create($validated);

        return back()->with('success', "Grupo {$validated['name']} creado.");
    }

    public function editGroup(Group $group)
    {
        $group->load('users', 'institution');

        $availableUsers = User::where('institution_id', $group->institution_id)
            ->whereNotIn('id', $group->users->pluck('id'))
            ->orderBy('name')
            ->get();

        return view('superadmin.group-edit', compact('group', 'availableUsers'));
    }

    public function updateGroup(Request $request, Group $group)
    {
        $validated = $request->validate([
            'action' => 'required|in:update_info,add_users,remove_user',
            'name' => 'nullable|string|max:255',
            'description' => 'nullable|string|max:1000',
            'user_ids' => 'nullable|array',
            'user_ids.*' => 'integer|exists:users,id',
            'remove_user_id' => 'nullable|integer|exists:users,id',
        ]);

        switch ($validated['action']) {
            case 'update_info':
                $group->update(array_filter([
                    'name' => $validated['name'],
                    'description' => $validated['description'] ?? '',
                ]));
                break;
            case 'add_users':
                if (!empty($validated['user_ids'])) {
                    $group->users()->syncWithoutDetaching($validated['user_ids']);
                }
                break;
            case 'remove_user':
                if ($validated['remove_user_id']) {
                    $group->users()->detach($validated['remove_user_id']);
                }
                break;
        }

        return back()->with('success', 'Grupo actualizado.');
    }

    public function deleteGroup(Group $group)
    {
        $group->delete();
        return redirect()->route('superadmin.groups')->with('success', 'Grupo eliminado.');
    }

    public function proOrders(Request $request)
    {
        $status = $request->get('status', 'pending');

        $orders = ProOrder::with('user')
            ->when($status, fn ($q) => $q->where('status', $status))
            ->orderByDesc('created_at')
            ->paginate(20)
            ->appends($request->query());

        return view('superadmin.pro-orders', compact('orders', 'status'));
    }

    public function reviewProOrder(Request $request, ProOrder $proOrder)
    {
        $validated = $request->validate([
            'action'      => 'required|in:approve,reject',
            'admin_notes' => 'nullable|string|max:1000',
        ]);

        if ($validated['action'] === 'approve') {
            $proOrder->update([
                'status'      => 'paid',
                'admin_notes' => $validated['admin_notes'] ?? null,
                'paid_at'     => now(),
            ]);

            if ($proOrder->user_id) {
                $slug = $proOrder->plan_slug ?? 'pro';
                $this->assignPlan(User::find($proOrder->user_id), $slug);
            }
        } else {
            $proOrder->update([
                'status'      => 'rejected',
                'admin_notes' => $validated['admin_notes'] ?? null,
            ]);
        }

        $label = $validated['action'] === 'approve' ? 'aprobada' : 'rechazada';
        return back()->with('success', "Orden Pro {$label}.");
    }

    private function assignPlan(User $user, string $planSlug): void
    {
        $user->subscriptions()->where('status', 'active')->update(['status' => 'cancelled']);

        if ($planSlug === 'free') {
            return;
        }

        $plan = Plan::where('slug', $planSlug)->firstOrFail();

        Subscription::create([
            'user_id' => $user->id,
            'plan_id' => $plan->id,
            'status' => 'active',
            'provider' => 'manual',
            'started_at' => Carbon::now(),
            'expires_at' => null,
        ]);

        if ($planSlug === 'plus') {
            if ($user->role === 'user') {
                $user->update(['role' => 'admin']);
            }
        }
    }
}
