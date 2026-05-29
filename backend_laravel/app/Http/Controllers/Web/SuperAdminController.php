<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Mail\PlanActivatedMail;
use App\Mail\PlusRequestMail;
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
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SuperAdminController extends Controller
{
    public function dashboard()
    {
        $totalUsers = User::count();
        $activeUsers = User::whereHas('inferenceRecords', fn ($q) => $q->where('created_at', '>=', now()->subDays(7)))->count();
        $totalRecords = InferenceRecord::count();
        $totalInstitutions = Institution::count();

        $avgProbability = InferenceRecord::whereNotNull('predicted_probability')->avg('predicted_probability');

        // Nota: se usa DB::select() con SQL crudo en lugar del query builder
        // porque MySQL ONLY_FULL_GROUP_BY rechaza GROUP BY con subqueries correlacionadas
        // cuando se usan a través de DB::table(DB::raw(...)). PDO parametrizado es seguro.
        $now = Carbon::now()->toDateTimeString();
        $planDistributionRows = DB::select("
            SELECT plan_slug, COUNT(*) AS total
            FROM (
                SELECT COALESCE(
                    (SELECT p.slug
                     FROM subscriptions s
                     JOIN plans p ON p.id = s.plan_id
                     WHERE s.user_id = users.id
                       AND s.status = 'active'
                       AND (s.expires_at IS NULL OR s.expires_at >= ?)
                     ORDER BY s.created_at DESC
                     LIMIT 1),
                    'free'
                ) AS plan_slug
                FROM users
            ) AS user_plans
            GROUP BY plan_slug
        ", [$now]);
        $planDistribution = collect($planDistributionRows)->pluck('total', 'plan_slug');

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

        // Solicitudes Plus pendientes de revisión
        $pendingPlusOrders = ProOrder::with('user')
            ->where('plan_slug', 'plus')
            ->whereIn('status', ['inquiry', 'in_review'])
            ->orderByDesc('created_at')
            ->limit(5)
            ->get();

        $pendingPlusCount = ProOrder::where('plan_slug', 'plus')
            ->whereIn('status', ['inquiry', 'in_review'])
            ->count();

        return view('superadmin.dashboard', compact(
            'totalUsers', 'activeUsers', 'totalRecords', 'totalInstitutions',
            'avgProbability', 'planDistribution', 'activityChart', 'registrationChart',
            'recentUsers', 'institutions', 'pendingPlusOrders', 'pendingPlusCount',
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
            'action' => 'required|in:set_plan,set_roles,set_institution,toggle_status,notify_plan',
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

            case 'notify_plan':
                $activeSub = $user->subscriptions()
                    ->where('status', 'active')
                    ->with('plan')
                    ->latest()
                    ->first();
                if ($activeSub?->plan) {
                    try {
                        Mail::to($user->email)->send(new PlanActivatedMail(
                            $user,
                            $activeSub->plan->slug,
                            ucfirst($activeSub->plan->name),
                            $activeSub->effectiveFeatures()
                        ));
                    } catch (\Throwable $e) {
                        Log::warning('Reenvío email plan falló', ['user' => $user->id, 'error' => $e->getMessage()]);
                        return back()->with('error', 'No se pudo enviar el email: ' . $e->getMessage());
                    }
                }
                return back()->with('success', "Email de plan enviado a {$user->email}.");
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
            'name'               => 'required|string|max:255',
            'slug'               => 'required|string|max:100|unique:institutions,slug',
            'type'               => 'nullable|string|max:100',
            'description'        => 'nullable|string|max:2000',
            'website'            => 'nullable|url|max:255',
            'contact_name'       => 'nullable|string|max:255',
            'contact_email'      => 'nullable|email|max:255',
            'contact_phone'      => 'nullable|string|max:50',
            'country'            => 'nullable|string|max:100',
            'state'              => 'nullable|string|max:100',
            'city'               => 'nullable|string|max:100',
            'address'            => 'nullable|string|max:500',
            'max_users'          => 'nullable|integer|min:1',
            'is_active'          => 'nullable|boolean',
            'contract_starts_at' => 'nullable|date',
            'contract_ends_at'   => 'nullable|date|after_or_equal:contract_starts_at',
            'notes'              => 'nullable|string|max:3000',
            'logo_url'           => 'nullable|url|max:500',
        ]);

        $validated['is_active'] = $request->boolean('is_active', true);

        Institution::create($validated);

        return back()->with('success', "Institución {$validated['name']} creada correctamente.");
    }

    public function editInstitution(Institution $institution)
    {
        $institution->loadCount('users');
        $recentUsers = $institution->users()->latest()->limit(10)->get();
        return view('superadmin.institution-edit', compact('institution', 'recentUsers'));
    }

    public function updateInstitution(Request $request, Institution $institution)
    {
        $validated = $request->validate([
            'name'               => 'required|string|max:255',
            'slug'               => 'required|string|max:100|unique:institutions,slug,' . $institution->id,
            'type'               => 'nullable|string|max:100',
            'description'        => 'nullable|string|max:2000',
            'website'            => 'nullable|url|max:255',
            'contact_name'       => 'nullable|string|max:255',
            'contact_email'      => 'nullable|email|max:255',
            'contact_phone'      => 'nullable|string|max:50',
            'country'            => 'nullable|string|max:100',
            'state'              => 'nullable|string|max:100',
            'city'               => 'nullable|string|max:100',
            'address'            => 'nullable|string|max:500',
            'max_users'          => 'nullable|integer|min:1',
            'is_active'          => 'nullable|boolean',
            'contract_starts_at' => 'nullable|date',
            'contract_ends_at'   => 'nullable|date|after_or_equal:contract_starts_at',
            'notes'              => 'nullable|string|max:3000',
            'logo_url'           => 'nullable|url|max:500',
        ]);

        $validated['is_active'] = $request->boolean('is_active', true);

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

    public function showSubscription(Subscription $subscription)
    {
        $subscription->load(['user', 'plan']);
        $plans = Plan::all();
        return view('superadmin.subscription-show', compact('subscription', 'plans'));
    }

    public function updateSubscription(Request $request, Subscription $subscription)
    {
        $validated = $request->validate([
            'action'   => 'required|in:cancel,activate,extend,update_features',
            'days'     => 'nullable|integer|min:1|max:365',
            'features' => 'nullable|array',
            'features.*' => 'boolean',
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
            case 'update_features':
                $featureInput = $validated['features'] ?? [];
                $base = $subscription->plan?->features ?? [];
                $merged = [];
                foreach (array_keys($base + $featureInput) as $key) {
                    $merged[$key] = isset($featureInput[$key]) ? (bool)$featureInput[$key] : ($base[$key] ?? false);
                }
                $subscription->update(['features_override' => $merged]);
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
        $status = $request->get('status', 'inquiry');

        $orders = ProOrder::with(['user', 'assignedAdmin'])
            ->where('plan_slug', 'plus')
            ->when($status !== '', fn ($q) => $q->where('status', $status))
            ->orderByDesc('created_at')
            ->paginate(20)
            ->appends($request->query());

        return view('superadmin.pro-orders', compact('orders', 'status'));
    }

    public function showProOrder(ProOrder $proOrder)
    {
        $proOrder->load(['user', 'assignedAdmin', 'reviewer']);
        $allUsers = User::orderBy('name')->get(['id', 'name', 'email', 'role']);
        $plusFeatures = [
            'texto'            => 'Chat de texto',
            'audio'            => 'Chat de audio (voz)',
            'emociones'        => 'Análisis de emociones',
            'historial'        => 'Historial de sesiones',
            'imagen'           => 'Análisis facial',
            'estadisticas'     => 'Estadísticas avanzadas',
            'historial_completo' => 'Historial ilimitado',
            'crisis_alerts'    => 'Alertas de crisis',
            'reporte_clinico'  => 'Reporte clínico PDF',
            'multimodal'       => 'Detección multimodal',
        ];

        // Features activas en la orden (o defaults del plan Plus)
        $plusPlan   = Plan::where('slug', 'plus')->first();
        $defaultFeatures = $plusPlan?->features ?? array_fill_keys(array_keys($plusFeatures), false);

        return view('superadmin.pro-orders-show', compact(
            'proOrder', 'allUsers', 'plusFeatures', 'defaultFeatures'
        ));
    }

    public function reviewProOrder(Request $request, ProOrder $proOrder)
    {
        $validated = $request->validate([
            'action'            => 'required|in:approve,reject,in_review',
            'status_notes'      => 'nullable|string|max:2000',
            'admin_message'     => 'nullable|string|max:3000',
            'send_email'        => 'nullable|boolean',
            'assigned_admin_id' => 'nullable|exists:users,id',
            'features'          => 'nullable|array',
            'features.*'        => 'boolean',
        ]);

        $sendEmail     = $request->boolean('send_email', true);
        $adminMessage  = $validated['admin_message'] ?? null;
        $notes         = is_array($proOrder->notes) ? $proOrder->notes : [];
        $requesterName = $notes['requester_name'] ?? $proOrder->full_name ?? 'el solicitante';
        $requesterEmail = $notes['requester_email'] ?? $proOrder->email ?? null;
        $orgName       = $notes['org_name'] ?? '—';

        $proOrder->update([
            'status_notes' => $validated['status_notes'] ?? null,
            'reviewed_at'  => now(),
            'reviewed_by'  => $request->user()->id,
        ]);

        // ── En revisión ──────────────────────────────────────────────────
        if ($validated['action'] === 'in_review') {
            $proOrder->update(['status' => 'in_review']);

            $emailSent = false;
            if ($sendEmail && $requesterEmail) {
                $this->sendPlusStatusEmail($proOrder, 'in_review', $adminMessage);
                $emailSent = true;
            }

            $lines = [
                "Solicitud #{{$proOrder->id}} de {$requesterName} ({$orgName}) marcada como En revisión.",
                $emailSent
                    ? "✉ Notificación enviada a {$requesterEmail}."
                    : '⚠ No se envió email (sin dirección o envío desactivado).',
            ];
            if ($adminMessage) $lines[] = 'Mensaje incluido: "' . $adminMessage . '"';

            return back()->with('info', ['title' => '🔍 Solicitud marcada como En revisión', 'lines' => $lines]);
        }

        // ── Aprobar ───────────────────────────────────────────────────────
        if ($validated['action'] === 'approve') {
            $proOrder->update([
                'status'            => 'paid',
                'paid_at'           => now(),
                'assigned_admin_id' => $validated['assigned_admin_id'] ?? null,
            ]);

            $userId    = $validated['assigned_admin_id'] ?? $proOrder->user_id;
            $adminUser = null;
            $rolePromotion = false;

            if ($userId) {
                $adminUser = User::find($userId);
                if ($adminUser) {
                    $plusPlan       = Plan::where('slug', 'plus')->firstOrFail();
                    $baseFeatures   = $plusPlan->features ?? [];
                    $featureInput   = $validated['features'] ?? [];
                    $featuresActive = [];
                    foreach (array_keys($baseFeatures + $featureInput) as $key) {
                        $featuresActive[$key] = isset($featureInput[$key]) ? (bool) $featureInput[$key] : ($baseFeatures[$key] ?? false);
                    }

                    $adminUser->subscriptions()->where('status', 'active')->update(['status' => 'cancelled']);

                    Subscription::create([
                        'user_id'           => $adminUser->id,
                        'plan_id'           => $plusPlan->id,
                        'status'            => 'active',
                        'provider'          => 'manual',
                        'started_at'        => now(),
                        'expires_at'        => null,
                        'features_override' => $featuresActive,
                    ]);

                    if (!$adminUser->isAdmin()) {
                        $adminUser->update(['role' => 'admin']);
                        $rolePromotion = true;
                    }
                }
            }

            $emailSent = false;
            if ($sendEmail && $requesterEmail) {
                $this->sendPlusStatusEmail($proOrder, 'approved', $adminMessage);
                $emailSent = true;
            }

            $lines = [
                "Solicitud #{{$proOrder->id}} de {$requesterName} ({$orgName}) aprobada.",
            ];
            if ($adminUser) {
                $lines[] = "Suscripción Plus activada en la cuenta de {$adminUser->name} ({$adminUser->email}).";
                if ($rolePromotion) $lines[] = "Rol elevado a Admin en la plataforma.";
            } else {
                $lines[] = '⚠ No se asignó cuenta de usuario (sin usuario registrado).';
            }
            $lines[] = $emailSent
                ? "✉ Notificación de aprobación enviada a {$requesterEmail}."
                : '⚠ No se envió email (sin dirección o envío desactivado).';
            if ($adminMessage) $lines[] = 'Mensaje incluido: "' . $adminMessage . '"';

            return redirect()->route('superadmin.pro-orders.show', $proOrder)
                ->with('success', ['title' => '✅ Solicitud Plus aprobada y suscripción activada', 'lines' => $lines]);
        }

        // ── Rechazar ──────────────────────────────────────────────────────
        $proOrder->update(['status' => 'rejected']);

        $emailSent = false;
        if ($sendEmail && $requesterEmail) {
            $this->sendPlusStatusEmail($proOrder, 'rejected', $adminMessage);
            $emailSent = true;
        }

        $lines = [
            "Solicitud #{{$proOrder->id}} de {$requesterName} ({$orgName}) rechazada.",
            $emailSent
                ? "✉ Notificación de rechazo enviada a {$requesterEmail}."
                : '⚠ No se envió email (sin dirección o envío desactivado).',
        ];
        if ($adminMessage) $lines[] = 'Mensaje incluido: "' . $adminMessage . '"';

        return redirect()->route('superadmin.pro-orders.show', $proOrder)
            ->with('warning', ['title' => '✗ Solicitud rechazada', 'lines' => $lines]);
    }

    private function assignPlan(User $user, string $planSlug, array $featuresOverride = []): void
    {
        $user->subscriptions()->where('status', 'active')->update(['status' => 'cancelled']);

        if ($planSlug === 'free') {
            return;
        }

        $plan = Plan::where('slug', $planSlug)->firstOrFail();

        $sub = Subscription::create([
            'user_id'           => $user->id,
            'plan_id'           => $plan->id,
            'status'            => 'active',
            'provider'          => 'manual',
            'started_at'        => Carbon::now(),
            'expires_at'        => null,
            'features_override' => !empty($featuresOverride) ? $featuresOverride : null,
        ]);

        if ($planSlug === 'plus' && $user->role === 'user') {
            $user->update(['role' => 'admin']);
        }

        // Email de activación de plan
        try {
            Mail::to($user->email)->send(new PlanActivatedMail(
                $user,
                $planSlug,
                ucfirst($plan->name),
                $sub->effectiveFeatures()
            ));
        } catch (\Throwable $e) {
            Log::warning("Email activación plan {$planSlug} falló", ['user' => $user->id, 'error' => $e->getMessage()]);
        }
    }

    private function sendPlusStatusEmail(ProOrder $proOrder, string $type, ?string $adminMessage = null): void
    {
        $notes = is_array($proOrder->notes) ? $proOrder->notes : [];
        if (empty($notes['requester_email']) && empty($proOrder->email)) return;

        $data = array_merge($notes, [
            'requester_name'  => $notes['requester_name']  ?? $proOrder->full_name,
            'requester_email' => $notes['requester_email'] ?? $proOrder->email,
            'org_name'        => $notes['org_name']        ?? '—',
            'status_notes'    => $proOrder->status_notes   ?? null,
            'admin_message'   => $adminMessage,
        ]);

        try {
            Mail::to($data['requester_email'])
                ->send(new PlusRequestMail($data, $type));
        } catch (\Throwable $e) {
            Log::warning("Email Plus status '{$type}' falló", ['order' => $proOrder->id, 'error' => $e->getMessage()]);
        }
    }
}
