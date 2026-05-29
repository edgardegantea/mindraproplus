# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

---

## Repository layout

```
mindra-pro/
├── backend_laravel/          # Laravel 12 REST API + Python ML microservice
│   └── python-ml-service/    # FastAPI ML service (embedded in backend repo)
└── mindra_app/               # Flutter mobile app (Android/iOS)
```

---

## backend\_laravel

### Common commands

```bash
# Local development server (port 8000)
php artisan serve

# Run all tests
php artisan test

# Run a single test file
php artisan test --filter=InferenceTest

# Migrations
php artisan migrate
php artisan migrate:fresh --seed   # reset + seed

# Clear all caches (required after .env changes)
php artisan config:clear && php artisan route:clear && php artisan view:clear

# Queue worker (for SendWeeklyReportJob and similar)
php artisan queue:work

# Tinker (REPL)
php artisan tinker

# Deploy to Plesk production
bash deploy.sh
```

### Python ML microservice

```bash
cd python-ml-service
pip install -r requirements.txt
uvicorn main:app --host 0.0.0.0 --port 8001 --reload   # dev
# Production: see supervisor.conf
```

### Key env variables

| Variable | Default | Purpose |
|---|---|---|
| `MINDRABACK_URL` | `http://localhost:8001` | FastAPI ML service URL |
| `MINDRABACK_TIMEOUT` | `60` | Request timeout (s) |
| `MINDRABACK_CONNECT_TIMEOUT` | `8` | Connection timeout (s) |
| `MERCADOPAGO_ACCESS_TOKEN` | — | MercadoPago payments |
| `MERCADOPAGO_WEBHOOK_SECRET` | — | Webhook signature verification |

> **Plesk quirk**: `bootstrap/app.php` loads `.env` with `Dotenv::createMutable()` (not immutable) so that `.env` values override Plesk-injected environment variables (APP\_KEY, DB\_\*).

### Architecture

**Request flow for an inference:**
```
Flutter → POST /api/inference/predict
  → throttle:predict middleware (plan-aware rate limiter in AppServiceProvider)
  → InferenceController::predict
    → InferenceService::predict
      1. Feature-gate image/multimodal by plan
      2. Check ML cache (Cache key: ml_result:{md5(text)}, 300 s TTL, text-only)
      3. MindrabackClient::predict → FastAPI /predict
         (Circuit breaker: 3 consecutive failures → opens for 60 s, rejects immediately)
      4. On failure/circuit-open → fallbackResult() keyword matching
      5. DB transaction: create/update VisitorSession + InferenceRecord
      6. BotMemoryService: last 5 sessions → prepend contextual sentence to response
```

**Plan feature system:**

Plans (`free` / `pro` / `plus`) each have a `features` JSON column. The `feature:X` route middleware checks `$user->features()[$feature]`. Features include: `texto`, `audio`, `emociones`, `historial`, `estadisticas`, `imagen`, `multimodal`, `reporte_clinico`, `crisis_alerts`.

```
Route::get('/inference/history', ...)->middleware('feature:historial');
```

`User::features()` merges the plan's base features with any `features_override` on the active `Subscription` row (allows per-user overrides without changing the plan).

**Rate limiting** (`throttle:predict` named limiter):
- Anonymous: 10 req/hour by IP
- Free: 20 req/hour by user ID
- Pro: 100 req/hour by user ID
- Plus: unlimited

**Crisis alerts**: `InferenceRecordObserver` fires on `created`; if `predicted_probability >= 0.75` and the user has `crisis_alerts` feature + hasn't been alerted in the last 2 hours, sends `CrisisAlertMail`.

**Authentication**: Laravel Sanctum bearer tokens. `POST /api/auth/login` → returns `{ token, user, plan }`. Token stored client-side and sent as `Authorization: Bearer {token}`.

**MercadoPago**: `POST /api/checkout/{pro|plus}` → creates a preference → returns `checkout_url`. `POST /api/webhooks/mercadopago` validates HMAC signature before processing. `ProOrder` tracks orders; status updated on webhook.

---

## mindra\_app

### Common commands

```bash
# Get dependencies
flutter pub get

# Analyze (no errors expected; ~14 info-level hints are normal)
flutter analyze

# Run on device/emulator
flutter run

# Build release APK
flutter build apk --release
# Output: build/app/outputs/flutter-apk/app-release.apk

# Build App Bundle (Play Store)
flutter build appbundle --release
```

### Key configuration

- **API base URL**: hardcoded in `lib/services/api_service.dart` → `https://mindra.cafined.org/api`
- **Android package**: `org.cafined.mindra` (namespace and applicationId in `android/app/build.gradle.kts`)
- **MainActivity**: `android/app/src/main/kotlin/org/cafined/mindra/MainActivity.kt`

### Architecture

**State management**: Provider (no Riverpod/Bloc).

| Provider | Purpose |
|---|---|
| `AuthProvider` | Auth state machine (`unknown → authenticated / unauthenticated`), holds current user + plan |
| `PlanProvider` | Plan list for the plans screen |
| `ThemeProvider` | Dark/light mode toggle, persisted via StorageService |

**Router**: `_AppRouter` in `main.dart` switches on `AuthProvider.state`. After login, `_MainShell` provides the 3-tab bottom nav (Home / Wellness / Profile). On wide screens (≥ 700 px) it uses `NavigationRail` instead.

**Effective plan**: `AuthProvider.effectivePlan` returns `null` (Free) on web regardless of actual subscription; returns the real plan on mobile. Use `effectivePlan` in UI, not `currentPlan`.

**API layer**: `ApiService` is a plain Dart HTTP wrapper (no Dio). `StorageService` wraps SharedPreferences for token + onboarding state. `ApiException` is thrown on non-2xx responses.

**Notifications**: `NotificationService` (in `lib/services/`) uses `flutter_local_notifications`. Timezone is resolved with `flutter_timezone` (`localTz.identifier`) and set via `tz.setLocalLocation()` before scheduling. Uses `inexactAllowWhileIdle` (no `SCHEDULE_EXACT_ALARM` permission needed). Reminders are available to **all** authenticated users.

**Chat history persistence**: `ChatScreen` stores up to 60 messages in SharedPreferences under key `chat_history_v1`. Fallback responses (when ML is unreachable) are flagged with `isFallback: true` and shown with an offline banner.

**Theming / contrast**: `MindraColors` in `lib/theme/mindra_theme.dart` defines accessible text color variants (`blueOnLight`, `violetOnDark`, `indigoOnDark`) that meet WCAG AA. Use `MindraColors.planTextColor(planColor, isDark: isDark)` for any text/icon placed over a plan-tinted background. Never use raw `MindraColors.blue/violet/indigo` for text in light mode.

**Responsive layout**: Wrap screen body with `WebFrame` (from `lib/utils/responsive.dart`) to constrain width on web/tablet. `isWideScreen(context)` returns true when width ≥ 700 px.

### Screen inventory

| Screen | Feature gate |
|---|---|
| `ChatScreen` | Free (all users) |
| `HistoryScreen` | `feature:historial` |
| `WeeklyReportScreen` | `feature:historial` |
| `AssessmentScreen` (GAD-7) | Free |
| `MoodJournalScreen` | Free |
| `BreathingScreen` | Free |
| `WellnessScreen` | Free |
| `PlansScreen` | Free |
| `ProfileScreen` | Authenticated |

The Flutter app does **not** enforce feature gates itself — it calls the API and shows upgrade prompts on 403 responses.
