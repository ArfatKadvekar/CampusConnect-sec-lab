<?php
/**
 * CampusConnect Security Lab — Multi-User Password Security Demonstration Dashboard
 *
 * Dedicated Instructor Presentation Dashboard
 * Strictly restricted to localhost (127.0.0.1) and authorized instructor sessions.
 * EDUCATIONAL USE ONLY
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/demo_helper.php';

require_instructor_authorization();

$db = get_db();
$demo_accounts = get_demo_accounts_list($db);
$recent_logs = get_recent_demo_attempts($db, 15);
$csrf_token = get_demo_csrf_token();
$sim_results = $_SESSION['demo_sim_results'] ?? [];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Password Security Demonstration — <?= SITE_NAME ?> Security Lab</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=JetBrains+Mono:wght@400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= SITE_URL ?>/assets/css/style.css">
    <style>
        /* ── Instructor Presentation Dashboard Theme ─────────────────────── */
        :root {
            --demo-navy: #0f172a;
            --demo-slate: #1e293b;
            --demo-blue: #2563eb;
            --demo-blue-hover: #1d4ed8;
            --demo-blue-subtle: #eff6ff;
            --demo-surface: #ffffff;
            --demo-bg: #f8fafc;
            --demo-border: #e2e8f0;
            --demo-mono: 'JetBrains Mono', monospace, Consolas;
        }

        body.demo-dashboard-body {
            background-color: var(--demo-bg);
            color: #0f172a;
            font-family: 'Inter', -apple-system, sans-serif;
            margin: 0;
            padding: 0;
        }

        /* ── Presentation Top Header ─────────────────────────────────────── */
        .demo-topbar {
            background: #ffffff;
            border-bottom: 1px solid var(--demo-border);
            padding: 14px 28px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            position: sticky;
            top: 0;
            z-index: 50;
        }

        .demo-topbar-left {
            display: flex;
            align-items: center;
            gap: 14px;
        }

        .demo-brand-tag {
            background: var(--demo-navy);
            color: #ffffff;
            font-weight: 700;
            font-size: 0.8rem;
            padding: 4px 9px;
            border-radius: 6px;
            letter-spacing: 0.5px;
        }

        .demo-title-group h1 {
            font-size: 1.15rem;
            font-weight: 700;
            margin: 0;
            color: var(--demo-navy);
        }

        .demo-title-group p {
            font-size: 0.78rem;
            color: #64748b;
            margin: 2px 0 0 0;
        }

        .demo-topbar-actions {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .badge-pill {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            font-size: 0.75rem;
            font-weight: 600;
            padding: 5px 12px;
            border-radius: 9999px;
            border: 1px solid transparent;
        }

        .badge-localhost {
            background: #f0fdf4;
            color: #166534;
            border-color: #bbf7d0;
        }

        .badge-localhost .dot {
            width: 7px;
            height: 7px;
            background: #22c55e;
            border-radius: 50%;
        }

        .btn-reset-demo {
            background: #ffffff;
            border: 1px solid #cbd5e1;
            color: #334155;
            font-weight: 600;
            font-size: 0.8rem;
            padding: 6px 14px;
            border-radius: 6px;
            cursor: pointer;
            transition: all 0.15s ease;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }

        .btn-reset-demo:hover {
            background: #f1f5f9;
            border-color: #94a3b8;
            color: #0f172a;
        }

        /* ── Main Container ─────────────────────────────────────────────── */
        .demo-container {
            max-width: 1440px;
            margin: 0 auto;
            padding: 24px 28px 60px 28px;
        }

        /* ── Navigation Tabs ─────────────────────────────────────────────── */
        .demo-nav-tabs {
            display: flex;
            gap: 8px;
            border-bottom: 2px solid #e2e8f0;
            margin-bottom: 24px;
        }

        .demo-tab-btn {
            background: none;
            border: none;
            border-bottom: 3px solid transparent;
            padding: 10px 18px;
            font-size: 0.9rem;
            font-weight: 600;
            color: #64748b;
            cursor: pointer;
            transition: all 0.15s;
            margin-bottom: -2px;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .demo-tab-btn:hover {
            color: var(--demo-blue);
        }

        .demo-tab-btn.active {
            color: var(--demo-blue);
            border-bottom-color: var(--demo-blue);
        }

        .demo-panel-content {
            display: none;
        }

        .demo-panel-content.active {
            display: block;
        }

        /* ── Section Cards ──────────────────────────────────────────────── */
        .demo-card {
            background: var(--demo-surface);
            border: 1px solid var(--demo-border);
            border-radius: 12px;
            padding: 24px;
            margin-bottom: 24px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.04);
        }

        .demo-card-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 20px;
            padding-bottom: 14px;
            border-bottom: 1px solid #f1f5f9;
        }

        .demo-card-title {
            font-size: 1.1rem;
            font-weight: 700;
            color: var(--demo-navy);
            margin: 0;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .demo-card-subtitle {
            font-size: 0.82rem;
            color: #64748b;
            margin: 4px 0 0 0;
        }

        /* ── Account Cards Grid ─────────────────────────────────────────── */
        .accounts-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 18px;
            margin-bottom: 20px;
        }

        .account-card {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 10px;
            padding: 18px;
            position: relative;
            transition: box-shadow 0.2s;
        }

        .account-card:hover {
            box-shadow: 0 4px 12px rgba(0,0,0,0.05);
        }

        .account-card-header {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            margin-bottom: 12px;
        }

        .account-badge-code {
            width: 32px;
            height: 32px;
            border-radius: 8px;
            background: #eff6ff;
            color: var(--demo-blue);
            font-weight: 700;
            font-size: 0.95rem;
            display: flex;
            align-items: center;
            justify-content: center;
            border: 1px solid #bfdbfe;
        }

        .account-uname {
            font-family: var(--demo-mono);
            font-weight: 600;
            font-size: 0.9rem;
            color: #0f172a;
        }

        .account-cat {
            font-size: 0.78rem;
            color: #64748b;
            margin-top: 2px;
        }

        .account-metric-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            font-size: 0.8rem;
            padding: 6px 0;
            border-bottom: 1px dashed #f1f5f9;
        }

        .account-metric-label {
            color: #64748b;
        }

        .account-metric-value {
            font-weight: 600;
            color: #1e293b;
        }

        .account-password-box {
            margin-top: 12px;
            padding: 8px 10px;
            background: #f8fafc;
            border-radius: 6px;
            border: 1px solid #e2e8f0;
            display: flex;
            align-items: center;
            justify-content: space-between;
            font-size: 0.8rem;
        }

        .account-password-val {
            font-family: var(--demo-mono);
            letter-spacing: 0.5px;
        }

        /* ── Candidate Search Simulation Panel (Experiment 1) ───────────── */
        .sim-grid {
            display: grid;
            grid-template-columns: 340px 1fr;
            gap: 24px;
        }

        @media (max-width: 960px) {
            .sim-grid { grid-template-columns: 1fr; }
        }

        .sim-controls-card {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 10px;
            padding: 20px;
        }

        .sim-meters-card {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 10px;
            padding: 22px;
        }

        .metric-big-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(160px, 1fr));
            gap: 16px;
            margin-bottom: 22px;
        }

        .metric-big-box {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 14px 16px;
            text-align: center;
        }

        .metric-big-number {
            font-size: 1.65rem;
            font-weight: 800;
            color: var(--demo-navy);
            font-family: var(--demo-mono);
            line-height: 1.2;
        }

        .metric-big-label {
            font-size: 0.72rem;
            color: #64748b;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-top: 4px;
        }

        .sim-progress-bar-wrap {
            background: #f1f5f9;
            height: 20px;
            border-radius: 10px;
            overflow: hidden;
            margin-bottom: 12px;
            border: 1px solid #e2e8f0;
            position: relative;
        }

        .sim-progress-bar-fill {
            height: 100%;
            background: linear-gradient(90deg, #3b82f6, #2563eb);
            width: 0%;
            transition: width 0.08s ease;
        }

        .sim-candidate-stream {
            background: #0f172a;
            color: #f8fafc;
            border-radius: 8px;
            padding: 14px 18px;
            font-family: var(--demo-mono);
            font-size: 0.86rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-top: 14px;
        }

        .sim-candidate-text {
            color: #93c5fd;
            font-weight: 600;
        }

        .sim-banner-result {
            padding: 16px 20px;
            border-radius: 8px;
            margin-top: 20px;
            display: none;
            font-size: 0.88rem;
            line-height: 1.5;
        }

        .sim-banner-found {
            background: #f0fdf4;
            border: 1px solid #86efac;
            color: #14532d;
        }

        .sim-banner-notfound {
            background: #eff6ff;
            border: 1px solid #93c5fd;
            color: #1e3a8a;
        }

        /* ── Comparison Table ───────────────────────────────────────────── */
        .comparison-table-wrap {
            overflow-x: auto;
        }

        .table-demo {
            width: 100%;
            border-collapse: collapse;
            font-size: 0.85rem;
            text-align: left;
        }

        .table-demo th {
            background: #f8fafc;
            color: #475569;
            font-weight: 700;
            padding: 12px 16px;
            border-bottom: 2px solid #e2e8f0;
            white-space: nowrap;
        }

        .table-demo td {
            padding: 12px 16px;
            border-bottom: 1px solid #f1f5f9;
            color: #1e293b;
        }

        .table-demo tr:hover td {
            background: #fafafa;
        }

        /* ── Online Auth Test Panel (Experiment 2) ──────────────────────── */
        .auth-test-grid {
            display: grid;
            grid-template-columns: 380px 1fr;
            gap: 24px;
        }

        @media (max-width: 960px) {
            .auth-test-grid { grid-template-columns: 1fr; }
        }

        .scenario-pill-group {
            display: flex;
            gap: 6px;
            background: #f1f5f9;
            padding: 4px;
            border-radius: 8px;
            margin-bottom: 18px;
        }

        .scenario-pill-btn {
            flex: 1;
            padding: 8px 12px;
            border: none;
            border-radius: 6px;
            font-size: 0.8rem;
            font-weight: 600;
            cursor: pointer;
            background: none;
            color: #64748b;
            transition: all 0.15s;
            text-align: center;
        }

        .scenario-pill-btn.active {
            background: #ffffff;
            color: #0f172a;
            box-shadow: 0 1px 3px rgba(0,0,0,0.08);
        }

        .defense-badge {
            display: inline-block;
            font-size: 0.72rem;
            font-weight: 600;
            padding: 3px 8px;
            border-radius: 4px;
            margin-right: 4px;
            margin-bottom: 4px;
        }

        .defense-badge-on {
            background: #dcfce7;
            color: #15803d;
        }

        .defense-badge-off {
            background: #fee2e2;
            color: #b91c1c;
        }

        /* ── Presentation Stepper Guide (Section 9) ─────────────────────── */
        .presentation-step-card {
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 14px 18px;
            margin-bottom: 10px;
            background: #ffffff;
            transition: border-color 0.15s;
            cursor: pointer;
        }

        .presentation-step-card.active {
            border-color: var(--demo-blue);
            background: #f8fafc;
            box-shadow: 0 0 0 1px var(--demo-blue);
        }

        .step-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .step-num {
            font-size: 0.75rem;
            font-weight: 700;
            color: var(--demo-blue);
            background: #eff6ff;
            padding: 3px 8px;
            border-radius: 4px;
        }

        .step-title {
            font-weight: 600;
            font-size: 0.9rem;
            color: #0f172a;
            flex: 1;
            margin-left: 10px;
        }

        .step-notes {
            margin-top: 10px;
            padding-top: 10px;
            border-top: 1px dashed #e2e8f0;
            font-size: 0.82rem;
            color: #475569;
            line-height: 1.5;
            display: none;
        }

        .presentation-step-card.active .step-notes {
            display: block;
        }

        /* ── Modal & Alerts ─────────────────────────────────────────────── */
        .toast-msg {
            position: fixed;
            bottom: 24px;
            right: 28px;
            background: #0f172a;
            color: #ffffff;
            padding: 12px 20px;
            border-radius: 8px;
            font-size: 0.85rem;
            box-shadow: 0 10px 15px -3px rgba(0,0,0,0.1);
            display: none;
            z-index: 9999;
        }
    </style>
</head>
<body class="demo-dashboard-body">

<!-- ════════════════════════════════════════════════════════════════════════════
     TOP BAR: LOCALHOST & LAB STATUS
     ════════════════════════════════════════════════════════════════════════════ -->
<header class="demo-topbar">
    <div class="demo-topbar-left">
        <span class="demo-brand-tag">CC LAB</span>
        <div class="demo-title-group">
            <h1>Password Security Demonstration Dashboard</h1>
            <p>Educational Multi-User Authentication &amp; Search Space Analysis — Kali / Localhost Environment</p>
        </div>
    </div>

    <div class="demo-topbar-actions">
        <span class="badge-pill badge-localhost">
            <span class="dot"></span>
            127.0.0.1 Isolated Lab
        </span>

        <button type="button" class="btn-reset-demo" id="btn-reset-global" title="Reset all demo accounts and attempt logs">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M3 12a9 9 0 1 0 9-9 9.75 9.75 0 0 0-6.74 2.74L3 8"/><path d="M3 3v5h5"/></svg>
            Reset Demo State
        </button>

        <a href="<?= SITE_URL ?>/" class="btn-reset-demo" style="text-decoration: none;" target="_blank">
            Public Portal ↗
        </a>

        <a href="<?= SITE_URL ?>/admin/dashboard.php" class="btn-reset-demo" style="text-decoration: none;" target="_blank">
            Admin Dashboard ↗
        </a>
    </div>
</header>

<!-- ════════════════════════════════════════════════════════════════════════════
     MAIN WORKSPACE
     ════════════════════════════════════════════════════════════════════════════ -->
<div class="demo-container">

    <!-- ── Tab Navigation ────────────────────────────────────────────────── -->
    <nav class="demo-nav-tabs">
        <button class="demo-tab-btn active" onclick="switchDemoTab('accounts-tab', this)">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
            1. Demo Accounts
        </button>
        <button class="demo-tab-btn" onclick="switchDemoTab('sim-tab', this)">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/></svg>
            2. Candidate Search Simulation
        </button>
        <button class="demo-tab-btn" onclick="switchDemoTab('comparison-tab', this)">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 3v18h18"/><path d="m19 9-5 5-4-4-3 3"/></svg>
            3. Results Comparison
        </button>
        <button class="demo-tab-btn" onclick="switchDemoTab('auth-tab', this)">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
            4. Online Authentication Controls
        </button>
        <button class="demo-tab-btn" onclick="switchDemoTab('guide-tab', this)">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"/><path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"/></svg>
            5. Instructor Presentation Flow
        </button>
    </nav>

    <!-- ════════════════════════════════════════════════════════════════════════
         TAB 1: DEMO ACCOUNTS PANEL (SECTION 4 & 5.A)
         ════════════════════════════════════════════════════════════════════════ -->
    <div id="accounts-tab" class="demo-panel-content active">
        <div class="demo-card">
            <div class="demo-card-header">
                <div>
                    <h2 class="demo-card-title">Fictional Demonstration Accounts</h2>
                    <p class="demo-card-subtitle">
                        Four disposable synthetic accounts representing distinct password patterns and security characteristics.
                    </p>
                </div>
                <div>
                    <label style="font-size: 0.8rem; font-weight: 600; color: #475569; display: flex; align-items: center; gap: 6px; cursor: pointer;">
                        <input type="checkbox" id="toggle-reveal-passwords" onchange="togglePasswordVisibility(this.checked)">
                        Instructor Reveal Control (Local Only)
                    </label>
                </div>
            </div>

            <div class="accounts-grid" id="accounts-grid-container">
                <?php foreach ($demo_accounts as $acc): ?>
                    <div class="account-card" id="card-acc-<?= htmlspecialchars($acc['account_code']) ?>">
                        <div class="account-card-header">
                            <div>
                                <div class="account-uname"><?= htmlspecialchars($acc['username']) ?></div>
                                <div class="account-cat"><?= htmlspecialchars($acc['display_name']) ?></div>
                            </div>
                            <div class="account-badge-code"><?= htmlspecialchars($acc['account_code']) ?></div>
                        </div>

                        <div class="account-metric-row">
                            <span class="account-metric-label">Password Category:</span>
                            <span class="account-metric-value"><?= htmlspecialchars($acc['category']) ?></span>
                        </div>

                        <div class="account-metric-row">
                            <span class="account-metric-label">Length &amp; Predictability:</span>
                            <span class="account-metric-value">
                                <?= (int)$acc['password_length'] ?> chars 
                                (<?= $acc['is_predictable'] ? '<span style="color:#d97706;">Predictable</span>' : '<span style="color:#16a34a;">High Entropy</span>' ?>)
                            </span>
                        </div>

                        <div class="account-metric-row">
                            <span class="account-metric-label">In Synthetic Wordlist:</span>
                            <span class="account-metric-value">
                                <?php if ($acc['in_dataset']): ?>
                                    <span style="color:#2563eb; font-weight:700;">Position #<?= number_format($acc['candidate_position']) ?></span>
                                <?php else: ?>
                                    <span style="color:#64748b;">Not present (0/25,001)</span>
                                <?php endif; ?>
                            </span>
                        </div>

                        <div class="account-metric-row">
                            <span class="account-metric-label">Scenario Configuration:</span>
                            <span class="account-metric-value">
                                <span class="defense-badge <?= $acc['scenario_mode'] === 'protected' ? 'defense-badge-on' : 'defense-badge-off' ?>">
                                    <?= ucfirst($acc['scenario_mode']) ?>
                                </span>
                            </span>
                        </div>

                        <div class="account-metric-row">
                            <span class="account-metric-label">Lockout Status:</span>
                            <span class="account-metric-value" id="lock-status-<?= htmlspecialchars($acc['account_code']) ?>">
                                <?php if ($acc['is_locked']): ?>
                                    <span style="color:#dc2626; font-weight:700;">Locked (<?= (int)$acc['lockout_remaining_minutes'] ?>m left)</span>
                                <?php else: ?>
                                    <span style="color:#16a34a;">Unlocked (0/5 fails)</span>
                                <?php endif; ?>
                            </span>
                        </div>

                        <div class="account-password-box">
                            <span style="color: #64748b;">Password:</span>
                            <span class="account-password-val pass-masked" data-plain="<?= htmlspecialchars($acc['target_password']) ?>">
                                ••••••••••••
                            </span>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 12px 16px; font-size: 0.8rem; color: #64748b; line-height: 1.5;">
                <strong>Presentation Note:</strong> Accounts A and B utilize predictable college-themed words that appear inside targeted campus candidate lists. Accounts C and D demonstrate high-entropy passwords that are <em>not</em> present in the candidate dataset.
            </div>
        </div>
    </div>

    <!-- ════════════════════════════════════════════════════════════════════════
         TAB 2: CANDIDATE SEARCH PROGRESS PANEL (EXPERIMENT 1 - SECTION 5.B & 6 & 7)
         ════════════════════════════════════════════════════════════════════════ -->
    <div id="sim-tab" class="demo-panel-content">
        <div class="demo-card">
            <div class="demo-card-header">
                <div>
                    <h2 class="demo-card-title">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/></svg>
                        Experiment 1: Candidate Search Simulation
                    </h2>
                    <p class="demo-card-subtitle">
                        Simulates offline dictionary candidate evaluation across the 25,001 entry dataset without sending live HTTP requests.
                    </p>
                </div>
                <div>
                    <span class="badge-pill" style="background:#eff6ff; color:#1d4ed8; border-color:#bfdbfe;">
                        Candidate Dataset: 25,001 entries
                    </span>
                </div>
            </div>

            <div class="sim-grid">
                <!-- Controls Card -->
                <div class="sim-controls-card">
                    <h3 style="font-size: 0.95rem; font-weight: 700; color: #0f172a; margin: 0 0 16px 0;">Demonstration Controls</h3>

                    <div style="margin-bottom: 16px;">
                        <label class="form-label" style="font-size: 0.82rem; font-weight: 600;">Select Target Account</label>
                        <select id="sim-target-select" class="form-control" style="font-size: 0.85rem;" onchange="updateSimTargetInfo()">
                            <option value="A" data-pos="4999" data-cat="Predictable">Account A — Predictable (campus2026)</option>
                            <option value="B" data-pos="12000" data-cat="Modified Pattern">Account B — Modified Pattern (Campus@2026)</option>
                            <option value="C" data-pos="-1" data-cat="Random">Account C — Random High Entropy (rN7#...)</option>
                            <option value="D" data-pos="-1" data-cat="Passphrase">Account D — Passphrase (falcon-river-...)</option>
                            <option value="BENCHMARK" data-pos="25000" data-cat="Stress Test">Benchmark Target at #25,000 (V7q!2mL...)</option>
                        </select>
                    </div>

                    <div style="margin-bottom: 16px;">
                        <label class="form-label" style="font-size: 0.82rem; font-weight: 600;">Simulation Speed Mode</label>
                        <select id="sim-speed-select" class="form-control" style="font-size: 0.85rem;">
                            <option value="visual">Visual Stepping (Teaching Pace ~2,500/s)</option>
                            <option value="max" selected>Full Speed (Real CPU Execution ~100k-500k/s)</option>
                        </select>
                    </div>

                    <div style="margin-top: 24px; display: flex; flex-direction: column; gap: 8px;">
                        <button type="button" class="btn btn-primary" id="btn-start-sim" onclick="startCandidateSearch()" style="width: 100%; justify-content: center;">
                            ▶ Start Candidate Search
                        </button>
                        <button type="button" class="btn btn-secondary" id="btn-pause-sim" onclick="pauseCandidateSearch()" style="width: 100%; justify-content: center;" disabled>
                            ⏸ Pause
                        </button>
                        <button type="button" class="btn btn-outline" id="btn-reset-sim" onclick="resetCandidateSearch()" style="width: 100%; justify-content: center;">
                            ↺ Reset Simulation
                        </button>
                    </div>

                    <div style="margin-top: 20px; padding: 12px; background: #ffffff; border: 1px solid #e2e8f0; border-radius: 6px; font-size: 0.78rem; color: #64748b;">
                        <strong>Target Status:</strong><br>
                        <span id="sim-target-hint">Account A: Campus keyword + year. Target position is masked until search ends.</span>
                    </div>
                </div>

                <!-- Visualization & Gauges Card -->
                <div class="sim-meters-card">
                    <div class="metric-big-grid">
                        <div class="metric-big-box">
                            <div class="metric-big-number" id="meter-evaluated">0</div>
                            <div class="metric-big-label">Candidates Evaluated</div>
                        </div>

                        <div class="metric-big-box">
                            <div class="metric-big-number" id="meter-percent">0.0%</div>
                            <div class="metric-big-label">Search Space Tested</div>
                        </div>

                        <div class="metric-big-box">
                            <div class="metric-big-number" id="meter-time">0.000 s</div>
                            <div class="metric-big-label">Actual Elapsed Time</div>
                        </div>

                        <div class="metric-big-box">
                            <div class="metric-big-number" id="meter-rate">0 /s</div>
                            <div class="metric-big-label">Candidates / Sec</div>
                        </div>
                    </div>

                    <!-- Visual Progress Bar -->
                    <div style="display: flex; justify-content: space-between; font-size: 0.75rem; color: #64748b; margin-bottom: 6px; font-weight: 600;">
                        <span>Progress: 0 / 25,001 candidates</span>
                        <span id="sim-progress-text">Ready</span>
                    </div>
                    <div class="sim-progress-bar-wrap">
                        <div class="sim-progress-bar-fill" id="sim-bar-fill"></div>
                    </div>

                    <!-- Live candidate stream box -->
                    <div class="sim-candidate-stream">
                        <span style="color: #94a3b8; font-size: 0.78rem;">Evaluating candidate:</span>
                        <span class="sim-candidate-text" id="sim-active-candidate">—</span>
                        <span style="color: #94a3b8; font-size: 0.78rem;" id="sim-candidate-index">#0 / 25,001</span>
                    </div>

                    <!-- Outcome Banner -->
                    <div class="sim-banner-result" id="sim-banner"></div>

                    <!-- Educational Notice -->
                    <div style="margin-top: 18px; font-size: 0.8rem; color: #64748b; line-height: 1.5; border-left: 3px solid #2563eb; padding-left: 12px;">
                        <strong>Measured Timing Note:</strong> Timing represents the actual JavaScript evaluation loop across the local 25,001 dataset on this machine. We never claim this represents a universal offline cracking time.
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- ════════════════════════════════════════════════════════════════════════
         TAB 3: RESULTS COMPARISON TABLE (SECTION 5.C)
         ════════════════════════════════════════════════════════════════════════ -->
    <div id="comparison-tab" class="demo-panel-content">
        <div class="demo-card">
            <div class="demo-card-header">
                <div>
                    <h2 class="demo-card-title">Demonstration Results Comparison</h2>
                    <p class="demo-card-subtitle">
                        Empirical measurements recorded directly from actual demonstration runs.
                    </p>
                </div>
                <div>
                    <button type="button" class="btn-reset-demo" onclick="clearComparisonResults()">
                        Clear Recorded Runs
                    </button>
                </div>
            </div>

            <div class="comparison-table-wrap">
                <table class="table-demo" id="comparison-table">
                    <thead>
                        <tr>
                            <th>Account Code</th>
                            <th>Password Category</th>
                            <th>Target Characteristics</th>
                            <th>Candidate Position</th>
                            <th>Candidates Evaluated</th>
                            <th>Measured Elapsed Time</th>
                            <th>Observed Search Rate</th>
                            <th>Outcome</th>
                            <th>Authentication Controls</th>
                        </tr>
                    </thead>
                    <tbody id="comparison-table-body">
                        <!-- Populated dynamically by JavaScript -->
                    </tbody>
                </table>
            </div>

            <div style="margin-top: 20px; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 14px 18px; font-size: 0.82rem; color: #475569; line-height: 1.5;">
                <strong>Key Takeaway:</strong> Notice how Account A (Predictable) was discovered early in the dictionary, Account B (Modified pattern) required searching further into patterned campus lists, whereas Accounts C and D were <em>not found</em> even after checking every candidate. This proves that predictability—not merely complexity rules—governs search efficiency.
            </div>
        </div>
    </div>

    <!-- ════════════════════════════════════════════════════════════════════════
         TAB 4: ONLINE AUTHENTICATION CONTROLS (EXPERIMENT 2 - SECTION 7 & 8)
         ════════════════════════════════════════════════════════════════════════ -->
    <div id="auth-tab" class="demo-panel-content">
        <div class="demo-card">
            <div class="demo-card-header">
                <div>
                    <h2 class="demo-card-title">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                        Experiment 2: Online Authentication Controls &amp; Lockout
                    </h2>
                    <p class="demo-card-subtitle">
                        Dispatches real HTTP requests to the local CampusConnect authentication endpoint to demonstrate server-side defenses.
                    </p>
                </div>
                <div>
                    <span class="badge-pill" id="scenario-active-badge" style="background:#fef2f2; color:#b91c1c; border-color:#fecaca;">
                        Scenario A: Insecure
                    </span>
                </div>
            </div>

            <div class="auth-test-grid">
                <!-- Authentication Form & Trigger Controls -->
                <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 10px; padding: 20px;">
                    <div style="margin-bottom: 16px;">
                        <label class="form-label" style="font-size: 0.82rem; font-weight: 600;">Scenario Mode</label>
                        <div class="scenario-pill-group">
                            <button type="button" class="scenario-pill-btn active" id="btn-scen-insecure" onclick="setAuthScenario('insecure')">
                                Scenario A: Insecure
                            </button>
                            <button type="button" class="scenario-pill-btn" id="btn-scen-protected" onclick="setAuthScenario('protected')">
                                Scenario B: Protected
                            </button>
                        </div>
                    </div>

                    <div style="margin-bottom: 14px;">
                        <label class="form-label" style="font-size: 0.82rem; font-weight: 600;">Target Demo Account</label>
                        <select id="auth-target-account" class="form-control" style="font-size: 0.85rem;" onchange="syncAuthFields()">
                            <?php foreach ($demo_accounts as $acc): ?>
                                <option value="<?= htmlspecialchars($acc['username']) ?>" data-code="<?= htmlspecialchars($acc['account_code']) ?>">
                                    <?= htmlspecialchars($acc['username']) ?> (<?= htmlspecialchars($acc['display_name']) ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div style="margin-bottom: 14px;">
                        <label class="form-label" style="font-size: 0.82rem; font-weight: 600;">Test Password</label>
                        <input type="text" id="auth-test-password" class="form-control" style="font-size: 0.85rem; font-family: var(--demo-mono);" value="wrong_password_999">
                        <div style="display: flex; gap: 8px; margin-top: 6px;">
                            <button type="button" class="btn-reset-demo" style="font-size: 0.72rem; padding: 3px 8px;" onclick="fillCorrectPassword()">
                                Fill Correct Password
                            </button>
                            <button type="button" class="btn-reset-demo" style="font-size: 0.72rem; padding: 3px 8px;" onclick="fillIncorrectPassword()">
                                Fill Incorrect Password
                            </button>
                        </div>
                    </div>

                    <div style="margin-top: 22px; display: flex; flex-direction: column; gap: 8px;">
                        <button type="button" class="btn btn-primary" onclick="sendAuthAttempt()" style="width: 100%; justify-content: center;">
                            Send 1 Authentication Attempt
                        </button>
                        <button type="button" class="btn btn-secondary" onclick="sendBatchAttempts(5)" style="width: 100%; justify-content: center;">
                            Send 5 Failed Attempts (Trigger Lockout)
                        </button>
                        <button type="button" class="btn btn-outline" onclick="resetDemoLocks()" style="width: 100%; justify-content: center;">
                            ↺ Reset Account Lockouts
                        </button>
                    </div>
                </div>

                <!-- Live HTTP Response & Defense Inspection -->
                <div>
                    <!-- Response Box -->
                    <div style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 10px; padding: 20px; margin-bottom: 20px;">
                        <h3 style="font-size: 0.95rem; font-weight: 700; color: #0f172a; margin: 0 0 14px 0;">
                            Server Response Inspection
                        </h3>

                        <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 12px; margin-bottom: 16px;">
                            <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 6px; padding: 10px; text-align: center;">
                                <div style="font-size: 0.7rem; color: #64748b; font-weight: 600;">HTTP STATUS</div>
                                <div id="resp-status" style="font-size: 1.2rem; font-weight: 800; font-family: var(--demo-mono); color: #0f172a;">—</div>
                            </div>
                            <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 6px; padding: 10px; text-align: center;">
                                <div style="font-size: 0.7rem; color: #64748b; font-weight: 600;">FAILED ATTEMPTS</div>
                                <div id="resp-fails" style="font-size: 1.2rem; font-weight: 800; font-family: var(--demo-mono); color: #0f172a;">0 / 5</div>
                            </div>
                            <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 6px; padding: 10px; text-align: center;">
                                <div style="font-size: 0.7rem; color: #64748b; font-weight: 600;">RESPONSE TIME</div>
                                <div id="resp-latency" style="font-size: 1.2rem; font-weight: 800; font-family: var(--demo-mono); color: #0f172a;">— ms</div>
                            </div>
                        </div>

                        <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 6px; padding: 12px 14px; font-size: 0.85rem; margin-bottom: 14px;">
                            <div style="font-weight: 600; color: #475569; font-size: 0.75rem; margin-bottom: 4px;">SERVER RESPONSE MESSAGE:</div>
                            <div id="resp-message" style="color: #0f172a; font-family: var(--demo-mono);">Waiting for authentication attempt...</div>
                        </div>

                        <!-- MFA Step Container (Visible if MFA triggered) -->
                        <div id="mfa-challenge-box" style="display: none; background: #eff6ff; border: 1px solid #bfdbfe; border-radius: 8px; padding: 14px; margin-bottom: 14px;">
                            <div style="font-weight: 700; color: #1e40af; font-size: 0.85rem; margin-bottom: 6px;">
                                🔐 Multi-Factor Authentication Challenge Active
                            </div>
                            <p style="font-size: 0.78rem; color: #3b82f6; margin: 0 0 10px 0;">
                                Primary credentials verified. Enter the 6-digit TOTP demonstration token (Demo code: <strong>849201</strong> or <strong>123456</strong>):
                            </p>
                            <div style="display: flex; gap: 8px;">
                                <input type="text" id="mfa-input-code" class="form-control" placeholder="849201" style="max-width: 140px; font-family: var(--demo-mono);" maxlength="6">
                                <button type="button" class="btn btn-primary" onclick="submitMfaCode()">Verify MFA Token</button>
                            </div>
                            <div id="mfa-result-msg" style="margin-top: 8px; font-size: 0.8rem;"></div>
                        </div>

                        <!-- Controls Triggered Breakdown -->
                        <div>
                            <div style="font-weight: 600; color: #475569; font-size: 0.75rem; margin-bottom: 6px;">SERVER DEFENSIVE CONTROLS:</div>
                            <div id="resp-controls-list">
                                <span class="defense-badge defense-badge-off">Rate Limiting: Off</span>
                                <span class="defense-badge defense-badge-off">Account Lockout: Off</span>
                                <span class="defense-badge defense-badge-off">Bcrypt Hashing: Off</span>
                            </div>
                        </div>
                    </div>

                    <!-- Live Server Audit Log Viewer -->
                    <div style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 10px; padding: 20px;">
                        <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 12px;">
                            <h3 style="font-size: 0.95rem; font-weight: 700; color: #0f172a; margin: 0;">Server Audit Logs (demo_login_attempts)</h3>
                            <button type="button" class="btn-reset-demo" style="font-size: 0.72rem; padding: 3px 8px;" onclick="refreshLogs()">Refresh Logs</button>
                        </div>
                        <div style="max-height: 220px; overflow-y: auto; border: 1px solid #e2e8f0; border-radius: 6px;">
                            <table class="table-demo" style="font-size: 0.78rem;">
                                <thead>
                                    <tr>
                                        <th>Time</th>
                                        <th>User</th>
                                        <th>Scenario</th>
                                        <th>Status</th>
                                        <th>Defense Triggered</th>
                                        <th>Latency</th>
                                    </tr>
                                </thead>
                                <tbody id="audit-log-body">
                                    <?php foreach ($recent_logs as $log): ?>
                                        <tr>
                                            <td><?= htmlspecialchars(date('H:i:s', strtotime($log['attempted_at']))) ?></td>
                                            <td><?= htmlspecialchars($log['username']) ?></td>
                                            <td><?= htmlspecialchars($log['scenario_mode']) ?></td>
                                            <td>
                                                <?= $log['success'] ? '<span style="color:#16a34a;font-weight:700;">Success</span>' : '<span style="color:#dc2626;">Failed</span>' ?>
                                            </td>
                                            <td><?= htmlspecialchars($log['control_triggered']) ?></td>
                                            <td><?= number_format((float)$log['response_time_ms'], 1) ?> ms</td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- ════════════════════════════════════════════════════════════════════════
         TAB 5: INSTRUCTOR PRESENTATION FLOW & GUIDE (SECTION 9)
         ════════════════════════════════════════════════════════════════════════ -->
    <div id="guide-tab" class="demo-panel-content">
        <div class="demo-card">
            <div class="demo-card-header">
                <div>
                    <h2 class="demo-card-title">10-Step Instructor Presentation Flow</h2>
                    <p class="demo-card-subtitle">
                        Standard presentation script and speaker notes for a classroom projector session.
                    </p>
                </div>
            </div>

            <div id="presentation-stepper">
                <!-- Step 1 -->
                <div class="presentation-step-card active" onclick="activateStep(this)">
                    <div class="step-header">
                        <span class="step-num">Step 1</span>
                        <span class="step-title">Introduce the CampusConnect Portal</span>
                    </div>
                    <div class="step-notes">
                        <strong>Speaker Note:</strong> Open the public portal in a separate tab. Explain: "CampusConnect is a college student club portal. Students register for events, and administrators manage sessions. Notice how legitimate and normal it looks. Today we examine what happens when student accounts use weak or predictable credentials."
                    </div>
                </div>

                <!-- Step 2 -->
                <div class="presentation-step-card" onclick="activateStep(this)">
                    <div class="step-header">
                        <span class="step-num">Step 2</span>
                        <span class="step-title">Explain the Fictional Accounts &amp; Password Patterns</span>
                    </div>
                    <div class="step-notes">
                        <strong>Speaker Note:</strong> Switch to the Demo Accounts tab. Explain: "We have four fictional student accounts: Account A uses a predictable campus word; Account B uses a common pattern with an uppercase letter, symbol, and year; Account C uses a 16-character random string; Account D uses a 4-word passphrase. Let's see how an attacker evaluates them."
                    </div>
                </div>

                <!-- Step 3 -->
                <div class="presentation-step-card" onclick="activateStep(this)">
                    <div class="step-header">
                        <span class="step-num">Step 3</span>
                        <span class="step-title">Start Predictable Password Demonstration (Account A)</span>
                    </div>
                    <div class="step-notes">
                        <strong>Speaker Note:</strong> Switch to Candidate Search Simulation tab, select Account A, and click 'Start Candidate Search'. Point to the candidate stream on screen.
                    </div>
                </div>

                <!-- Step 4 -->
                <div class="presentation-step-card" onclick="activateStep(this)">
                    <div class="step-header">
                        <span class="step-num">Step 4</span>
                        <span class="step-title">Show Live Timer and Measured Progress</span>
                    </div>
                    <div class="step-notes">
                        <strong>Speaker Note:</strong> Highlight the candidate counter and timer. Explain: "Notice the timer measuring real candidates evaluated on this machine. Because candidates are ordered by common campus words, Account A is found very quickly."
                    </div>
                </div>

                <!-- Step 5 -->
                <div class="presentation-step-card" onclick="activateStep(this)">
                    <div class="step-header">
                        <span class="step-num">Step 5</span>
                        <span class="step-title">Reveal Candidate Position After the Experiment</span>
                    </div>
                    <div class="step-notes">
                        <strong>Speaker Note:</strong> The simulation stops at candidate #4,999. Explain: "Account A was cracked after evaluating only 4,999 candidates—less than 20% of the small 25,001 list! Anyone who creates a password around their college name and graduation year is at high risk."
                    </div>
                </div>

                <!-- Step 6 -->
                <div class="presentation-step-card" onclick="activateStep(this)">
                    <div class="step-header">
                        <span class="step-num">Step 6</span>
                        <span class="step-title">Repeat with Modified-Pattern Password (Account B)</span>
                    </div>
                    <div class="step-notes">
                        <strong>Speaker Note:</strong> Select Account B (<code>Campus@2026</code>) and run the search. It stops at position #12,000. Explain: "Account B looks complex to a human—it has a capital letter, an '@' symbol, and a year. But attackers know this exact pattern! It was still discovered in the wordlist because the pattern itself is completely predictable."
                    </div>
                </div>

                <!-- Step 7 -->
                <div class="presentation-step-card" onclick="activateStep(this)">
                    <div class="step-header">
                        <span class="step-num">Step 7</span>
                        <span class="step-title">Demonstrate Random Password &amp; Explain "Not Found" Outcome</span>
                    </div>
                    <div class="step-notes">
                        <strong>Speaker Note:</strong> Select Account C and run. All 25,001 candidates are tested and the search finishes with: <em>'Target not found in the tested candidate set'</em>. Explain: "Crucial lesson: This does NOT mean the password is mathematically unbreakable. It means candidate wordlists only succeed if the target is within the tested dataset. Random generation makes dictionary prediction impossible."
                    </div>
                </div>

                <!-- Step 8 -->
                <div class="presentation-step-card" onclick="activateStep(this)">
                    <div class="step-header">
                        <span class="step-num">Step 8</span>
                        <span class="step-title">Activate Protected Authentication Scenario</span>
                    </div>
                    <div class="step-notes">
                        <strong>Speaker Note:</strong> Switch to the Online Authentication Controls tab and activate Scenario B: Protected. Explain: "Now we test against the live server backend. In Scenario A (Insecure), an attacker can submit unlimited attempts with no delay. Now watch what happens when we enforce defensive controls."
                    </div>
                </div>

                <!-- Step 9 -->
                <div class="presentation-step-card" onclick="activateStep(this)">
                    <div class="step-header">
                        <span class="step-num">Step 9</span>
                        <span class="step-title">Show the Effect of Throttling &amp; Account Lockout</span>
                    </div>
                    <div class="step-notes">
                        <strong>Speaker Note:</strong> Click 'Send 5 Failed Attempts'. Show the audit log and HTTP status 429: "On attempt 5, the server locked the account for 15 minutes. Even if an attacker has a list of millions of passwords, rate limiting reduces their speed from 500,000 guesses per second to only 5 attempts per 15 minutes!"
                    </div>
                </div>

                <!-- Step 10 -->
                <div class="presentation-step-card" onclick="activateStep(this)">
                    <div class="step-header">
                        <span class="step-num">Step 10</span>
                        <span class="step-title">Summarize the Security Lessons</span>
                    </div>
                    <div class="step-notes">
                        <strong>Speaker Note:</strong> Conclude with the summary: "1) Length and randomness beat complexity rules. 2) Defense-in-depth is essential: strong passwords combined with server-side rate limiting, bcrypt hashing, and MFA provide robust security."
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>

<!-- ── Toast Notification ────────────────────────────────────────────────── -->
<div class="toast-msg" id="demo-toast">Action Completed</div>

<!-- ════════════════════════════════════════════════════════════════════════════
     CLIENT-SIDE SIMULATION & API CONTROLLER
     ════════════════════════════════════════════════════════════════════════════ -->
<script>
const CSRF_TOKEN = <?= json_encode($csrf_token) ?>;
const SITE_URL = <?= json_encode(SITE_URL) ?>;

// Cached Wordlist & Simulation State
let candidateWordlist = [];
let isWordlistLoaded = false;
let simRunning = false;
let simPaused = false;
let simInterval = null;
let currentEvaluatedIndex = 0;
let simStartTime = 0;
let simElapsedAccumulated = 0;
let currentTargetPosition = 4999;
let currentTargetAccount = 'A';
let currentTargetFound = false;

// Stored Simulation Results for Comparison Table
let comparisonResults = <?= json_encode($sim_results) ?> || {};

// ── Tab Switching ──────────────────────────────────────────────────────────
function switchDemoTab(tabId, btn) {
    document.querySelectorAll('.demo-panel-content').forEach(p => p.classList.remove('active'));
    document.querySelectorAll('.demo-tab-btn').forEach(b => b.classList.remove('active'));
    document.getElementById(tabId).classList.add('active');
    btn.classList.add('active');
}

function activateStep(card) {
    document.querySelectorAll('.presentation-step-card').forEach(c => c.classList.remove('active'));
    card.classList.add('active');
}

function showToast(msg) {
    const toast = document.getElementById('demo-toast');
    toast.textContent = msg;
    toast.style.display = 'block';
    setTimeout(() => { toast.style.display = 'none'; }, 3500);
}

// ── Password Reveal Control ────────────────────────────────────────────────
function togglePasswordVisibility(reveal) {
    document.querySelectorAll('.pass-masked').forEach(span => {
        if (reveal) {
            span.textContent = span.getAttribute('data-plain');
            span.style.color = '#dc2626';
            span.style.fontWeight = '700';
        } else {
            span.textContent = '••••••••••••';
            span.style.color = 'inherit';
            span.style.fontWeight = 'normal';
        }
    });
}

// ── Wordlist Loader ────────────────────────────────────────────────────────
async function loadCandidateWordlist() {
    if (isWordlistLoaded) return true;
    try {
        const resp = await fetch(SITE_URL + '/lab-demo/wordlist.php');
        const data = await resp.json();
        if (data && data.candidates) {
            candidateWordlist = data.candidates;
            isWordlistLoaded = true;
            console.log('Wordlist loaded:', candidateWordlist.length, 'candidates');
            return true;
        }
    } catch (err) {
        console.error('Failed to load candidate wordlist:', err);
    }
    return false;
}

// ── Simulation Target Info Sync ────────────────────────────────────────────
function updateSimTargetInfo() {
    const sel = document.getElementById('sim-target-select');
    const opt = sel.options[sel.selectedIndex];
    const code = opt.value;
    const pos = parseInt(opt.getAttribute('data-pos'), 10);
    currentTargetAccount = code;
    currentTargetPosition = pos;

    const hint = document.getElementById('sim-target-hint');
    if (code === 'A') {
        hint.innerHTML = '<strong>Account A:</strong> Weak campus theme. Target position is masked until search ends.';
    } else if (code === 'B') {
        hint.innerHTML = '<strong>Account B:</strong> Modified pattern (Capital + Symbol + Year). Target position is masked until search ends.';
    } else if (code === 'C') {
        hint.innerHTML = '<strong>Account C:</strong> Random high-entropy string. Evaluated against all 25,001 candidates.';
    } else if (code === 'D') {
        hint.innerHTML = '<strong>Account D:</strong> 4-word diceware passphrase. Evaluated against all 25,001 candidates.';
    } else {
        hint.innerHTML = '<strong>Benchmark:</strong> Evaluating near end of wordlist at entry #25,000.';
    }
    resetCandidateSearch();
}

// ── Experiment 1: Candidate Search Simulation ──────────────────────────────
async function startCandidateSearch() {
    if (simRunning) return;

    if (!isWordlistLoaded) {
        document.getElementById('sim-progress-text').textContent = 'Loading 25,001 wordlist...';
        const ok = await loadCandidateWordlist();
        if (!ok) {
            alert('Failed to load candidate wordlist from server.');
            return;
        }
    }

    simRunning = true;
    simPaused = false;
    document.getElementById('btn-start-sim').disabled = true;
    document.getElementById('btn-pause-sim').disabled = false;
    document.getElementById('sim-banner').style.display = 'none';

    simStartTime = performance.now();
    const speedMode = document.getElementById('sim-speed-select').value;
    const total = candidateWordlist.length; // 25001

    if (speedMode === 'max') {
        // High-speed chunked execution using requestAnimationFrame / microtasks
        runFastSimulation();
    } else {
        // Visual stepping: 40 candidates per 16ms tick
        runVisualSimulation();
    }
}

function runFastSimulation() {
    const total = candidateWordlist.length;
    const targetIdx = currentTargetPosition > 0 ? (currentTargetPosition - 1) : -1;
    const chunkSize = 250;

    function step() {
        if (!simRunning || simPaused) return;

        const nextIndex = Math.min(total, currentEvaluatedIndex + chunkSize);

        // Check if target is inside this chunk
        if (targetIdx >= 0 && targetIdx >= currentEvaluatedIndex && targetIdx < nextIndex) {
            currentEvaluatedIndex = targetIdx + 1;
            currentTargetFound = true;
            updateSimMeters(currentEvaluatedIndex, total);
            finishCandidateSearch(true);
            return;
        }

        currentEvaluatedIndex = nextIndex;
        updateSimMeters(currentEvaluatedIndex, total);

        if (currentEvaluatedIndex >= total) {
            currentTargetFound = false;
            finishCandidateSearch(false);
            return;
        }

        requestAnimationFrame(step);
    }

    requestAnimationFrame(step);
}

function runVisualSimulation() {
    const total = candidateWordlist.length;
    const targetIdx = currentTargetPosition > 0 ? (currentTargetPosition - 1) : -1;
    const stepSize = 35; // Visual pace

    simInterval = setInterval(() => {
        if (!simRunning || simPaused) return;

        const nextIndex = Math.min(total, currentEvaluatedIndex + stepSize);

        if (targetIdx >= 0 && targetIdx >= currentEvaluatedIndex && targetIdx < nextIndex) {
            currentEvaluatedIndex = targetIdx + 1;
            currentTargetFound = true;
            updateSimMeters(currentEvaluatedIndex, total);
            clearInterval(simInterval);
            finishCandidateSearch(true);
            return;
        }

        currentEvaluatedIndex = nextIndex;
        updateSimMeters(currentEvaluatedIndex, total);

        if (currentEvaluatedIndex >= total) {
            currentTargetFound = false;
            clearInterval(simInterval);
            finishCandidateSearch(false);
            return;
        }
    }, 20);
}

function updateSimMeters(evaluated, total) {
    const now = performance.now();
    const elapsedSec = (simElapsedAccumulated + (now - simStartTime)) / 1000;
    const pct = ((evaluated / total) * 100).toFixed(1);
    const rate = elapsedSec > 0 ? Math.round(evaluated / elapsedSec) : 0;

    document.getElementById('meter-evaluated').textContent = evaluated.toLocaleString();
    document.getElementById('meter-percent').textContent = pct + '%';
    document.getElementById('meter-time').textContent = elapsedSec.toFixed(3) + ' s';
    document.getElementById('meter-rate').textContent = rate.toLocaleString() + ' /s';

    document.getElementById('sim-bar-fill').style.width = pct + '%';
    document.getElementById('sim-progress-text').textContent = evaluated.toLocaleString() + ' / ' + total.toLocaleString();

    // Stream current candidate text
    if (evaluated > 0 && evaluated <= total) {
        const word = candidateWordlist[evaluated - 1] || '...';
        document.getElementById('sim-active-candidate').textContent = word;
        document.getElementById('sim-candidate-index').textContent = '#' + evaluated.toLocaleString();
    }
}

function pauseCandidateSearch() {
    if (!simRunning) return;
    simPaused = !simPaused;
    const btn = document.getElementById('btn-pause-sim');
    if (simPaused) {
        simElapsedAccumulated += (performance.now() - simStartTime);
        btn.textContent = '▶ Resume';
    } else {
        simStartTime = performance.now();
        btn.textContent = '⏸ Pause';
        if (document.getElementById('sim-speed-select').value === 'max') {
            runFastSimulation();
        }
    }
}

function resetCandidateSearch() {
    simRunning = false;
    simPaused = false;
    if (simInterval) clearInterval(simInterval);
    currentEvaluatedIndex = 0;
    simElapsedAccumulated = 0;
    currentTargetFound = false;

    document.getElementById('btn-start-sim').disabled = false;
    document.getElementById('btn-pause-sim').disabled = true;
    document.getElementById('btn-pause-sim').textContent = '⏸ Pause';

    document.getElementById('meter-evaluated').textContent = '0';
    document.getElementById('meter-percent').textContent = '0.0%';
    document.getElementById('meter-time').textContent = '0.000 s';
    document.getElementById('meter-rate').textContent = '0 /s';
    document.getElementById('sim-bar-fill').style.width = '0%';
    document.getElementById('sim-progress-text').textContent = 'Ready';
    document.getElementById('sim-active-candidate').textContent = '—';
    document.getElementById('sim-candidate-index').textContent = '#0 / 25,001';
    document.getElementById('sim-banner').style.display = 'none';
}

function finishCandidateSearch(found) {
    simRunning = false;
    document.getElementById('btn-start-sim').disabled = false;
    document.getElementById('btn-pause-sim').disabled = true;

    const total = candidateWordlist.length;
    const now = performance.now();
    const elapsedSec = (simElapsedAccumulated + (now - simStartTime)) / 1000;
    const elapsedMs = Math.round(elapsedSec * 1000);
    const rate = elapsedSec > 0 ? Math.round(currentEvaluatedIndex / elapsedSec) : 0;
    const banner = document.getElementById('sim-banner');

    if (found) {
        const pos = currentTargetPosition;
        const pct = ((pos / total) * 100).toFixed(2);
        banner.className = 'sim-banner-result sim-banner-found';
        banner.style.display = 'block';
        banner.innerHTML = `
            <strong>🎯 Target Discovered!</strong> Position: <strong>#${pos.toLocaleString()}</strong> of ${total.toLocaleString()} (${pct}% of search space evaluated).<br>
            Evaluated ${currentEvaluatedIndex.toLocaleString()} candidates in <strong>${elapsedSec.toFixed(3)}s</strong> (${rate.toLocaleString()} attempts/sec).
        `;
    } else {
        banner.className = 'sim-banner-result sim-banner-notfound';
        banner.style.display = 'block';
        banner.innerHTML = `
            <strong>Target not found in the tested candidate set.</strong><br>
            Evaluated all <strong>${total.toLocaleString()}</strong> candidates (100% of candidate dataset) in <strong>${elapsedSec.toFixed(3)}s</strong> without discovering target.<br>
            <em>Educational Note:</em> This does not prove the password is permanently unbreakable; it demonstrates that candidate list guessing only succeeds when the victim's password falls within the tested dictionary.
        `;
    }

    // Save result to comparison table
    saveSimulationResult(currentTargetAccount, currentEvaluatedIndex, total, elapsedMs, found, found ? currentTargetPosition : null, rate);
}

// ── Save Simulation Result to Server / Session ────────────────────────────
async function saveSimulationResult(code, evaluated, total, elapsedMs, found, pos, rate) {
    comparisonResults[code] = {
        account_code: code,
        evaluated: evaluated,
        total: total,
        elapsed_ms: elapsedMs,
        found: found,
        position: pos,
        rate: rate,
        recorded_at: new Date().toLocaleTimeString()
    };

    renderComparisonTable();

    // Push to server session
    try {
        const formData = new FormData();
        formData.append('action', 'record_sim_result');
        formData.append('csrf_token', CSRF_TOKEN);
        formData.append('account_code', code);
        formData.append('evaluated', evaluated);
        formData.append('total', total);
        formData.append('elapsed_ms', elapsedMs);
        formData.append('found', found ? '1' : '0');
        if (pos) formData.append('position', pos);
        formData.append('rate', rate);

        await fetch(SITE_URL + '/lab-demo/api.php', { method: 'POST', body: formData });
    } catch (e) {
        console.error('Failed to save sim result on server:', e);
    }
}

// ── Render Results Comparison Table (Section 5.C) ──────────────────────────
function renderComparisonTable() {
    const tbody = document.getElementById('comparison-table-body');
    if (!tbody) return;

    const defaultRows = [
        { code: 'A', cat: 'Predictable (Campus)', chars: 'Weak campus keyword + year', controls: 'Insecure (No Lockout)' },
        { code: 'B', cat: 'Modified Pattern', chars: 'Capitalized, symbol, number (Campus@2026)', controls: 'Insecure (No Lockout)' },
        { code: 'C', cat: 'Random String', chars: '16 chars high entropy (rN7#...)', controls: 'Protected (Lockout + Bcrypt)' },
        { code: 'D', cat: 'Multi-Word Passphrase', chars: '4-word Diceware passphrase', controls: 'Protected (Lockout + Bcrypt + MFA)' },
    ];

    let html = '';
    defaultRows.forEach(row => {
        const res = comparisonResults[row.code];
        if (res) {
            const timeStr = (res.elapsed_ms / 1000).toFixed(3) + ' s (' + res.elapsed_ms + ' ms)';
            const posStr = res.found ? ('#' + res.position.toLocaleString()) : 'Not in candidate set';
            const outcomeBadge = res.found ? '<span style="color:#16a34a;font-weight:700;">Found</span>' : '<span style="color:#2563eb;font-weight:600;">Not Found</span>';

            html += `
                <tr>
                    <td><strong>Account ${row.code}</strong></td>
                    <td>${row.cat}</td>
                    <td>${row.chars}</td>
                    <td><strong>${posStr}</strong></td>
                    <td>${res.evaluated.toLocaleString()} / ${res.total.toLocaleString()}</td>
                    <td>${timeStr}</td>
                    <td>${res.rate.toLocaleString()} /s</td>
                    <td>${outcomeBadge}</td>
                    <td>${row.controls}</td>
                </tr>
            `;
        } else {
            html += `
                <tr style="color: #94a3b8;">
                    <td><strong>Account ${row.code}</strong></td>
                    <td>${row.cat}</td>
                    <td>${row.chars}</td>
                    <td>—</td>
                    <td>Pending test</td>
                    <td>—</td>
                    <td>—</td>
                    <td>Not run yet</td>
                    <td>${row.controls}</td>
                </tr>
            `;
        }
    });

    tbody.innerHTML = html;
}

function clearComparisonResults() {
    comparisonResults = {};
    renderComparisonTable();
    showToast('Comparison results cleared.');
}

// ── Experiment 2: Online Authentication Controls ───────────────────────────
let currentScenario = 'insecure';

function setAuthScenario(scen) {
    currentScenario = scen;
    document.getElementById('btn-scen-insecure').classList.toggle('active', scen === 'insecure');
    document.getElementById('btn-scen-protected').classList.toggle('active', scen === 'protected');

    const badge = document.getElementById('scenario-active-badge');
    if (scen === 'protected') {
        badge.style.background = '#dcfce7';
        badge.style.color = '#15803d';
        badge.style.borderColor = '#bbf7d0';
        badge.textContent = 'Scenario B: Protected';
    } else {
        badge.style.background = '#fef2f2';
        badge.style.color = '#b91c1c';
        badge.style.borderColor = '#fecaca';
        badge.textContent = 'Scenario A: Insecure';
    }
}

function fillCorrectPassword() {
    const sel = document.getElementById('auth-target-account');
    const code = sel.options[sel.selectedIndex].getAttribute('data-code');
    const card = document.getElementById('card-acc-' + code);
    if (card) {
        const pass = card.querySelector('.pass-masked').getAttribute('data-plain');
        document.getElementById('auth-test-password').value = pass;
    }
}

function fillIncorrectPassword() {
    document.getElementById('auth-test-password').value = 'wrong_password_' + Math.floor(Math.random() * 900 + 100);
}

function syncAuthFields() {
    fillIncorrectPassword();
}

async function sendAuthAttempt() {
    const username = document.getElementById('auth-target-account').value;
    const password = document.getElementById('auth-test-password').value;

    const formData = new FormData();
    formData.append('action', 'login_attempt');
    formData.append('csrf_token', CSRF_TOKEN);
    formData.append('username', username);
    formData.append('password', password);
    formData.append('scenario', currentScenario);

    try {
        const resp = await fetch(SITE_URL + '/lab-demo/api.php', { method: 'POST', body: formData });
        const data = await resp.json();
        renderAuthResponse(data, resp.status);
        refreshLogs();
        refreshAccountStatuses();
    } catch (e) {
        console.error('Auth request failed:', e);
        alert('Authentication request failed. Check server logs.');
    }
}

async function sendBatchAttempts(count) {
    const username = document.getElementById('auth-target-account').value;
    const password = document.getElementById('auth-test-password').value;

    const formData = new FormData();
    formData.append('action', 'batch_attempts');
    formData.append('csrf_token', CSRF_TOKEN);
    formData.append('username', username);
    formData.append('password', password);
    formData.append('scenario', currentScenario);
    formData.append('count', count);

    try {
        const resp = await fetch(SITE_URL + '/lab-demo/api.php', { method: 'POST', body: formData });
        const data = await resp.json();
        if (data && data.batch_results && data.batch_results.length > 0) {
            const lastResult = data.batch_results[data.batch_results.length - 1];
            renderAuthResponse(lastResult, lastResult.http_code || 200);
        }
        refreshLogs();
        refreshAccountStatuses();
    } catch (e) {
        console.error('Batch request failed:', e);
    }
}

function renderAuthResponse(data, httpStatus) {
    document.getElementById('resp-status').textContent = data.http_code || httpStatus || 200;
    document.getElementById('resp-fails').textContent = (data.failed_attempts || 0) + ' / 5';
    document.getElementById('resp-latency').textContent = (data.response_time_ms || 0) + ' ms';
    document.getElementById('resp-message').textContent = data.message || data.error || 'No message';

    const statusEl = document.getElementById('resp-status');
    if (data.is_locked || data.http_code === 429) {
        statusEl.style.color = '#dc2626';
    } else if (data.success) {
        statusEl.style.color = '#16a34a';
    } else {
        statusEl.style.color = '#d97706';
    }

    // Defensive Controls Badges
    const controlsWrap = document.getElementById('resp-controls-list');
    controlsWrap.innerHTML = '';
    if (data.controls_active) {
        for (const [k, v] of Object.entries(data.controls_active)) {
            const isDefended = v.includes('Enforced') || v.includes('Bcrypt') || v.includes('Generic');
            const span = document.createElement('span');
            span.className = 'defense-badge ' + (isDefended ? 'defense-badge-on' : 'defense-badge-off');
            span.textContent = k + ': ' + v;
            controlsWrap.appendChild(span);
        }
    }

    // MFA Challenge Display
    const mfaBox = document.getElementById('mfa-challenge-box');
    if (data.mfa_required) {
        mfaBox.style.display = 'block';
        document.getElementById('mfa-result-msg').textContent = '';
    } else {
        mfaBox.style.display = 'none';
    }
}

async function submitMfaCode() {
    const username = document.getElementById('auth-target-account').value;
    const otp = document.getElementById('mfa-input-code').value;

    const formData = new FormData();
    formData.append('action', 'mfa_verify');
    formData.append('csrf_token', CSRF_TOKEN);
    formData.append('username', username);
    formData.append('otp', otp);

    const resp = await fetch(SITE_URL + '/lab-demo/api.php', { method: 'POST', body: formData });
    const data = await resp.json();
    const resultEl = document.getElementById('mfa-result-msg');

    if (data.success) {
        resultEl.style.color = '#16a34a';
        resultEl.style.fontWeight = '700';
        resultEl.textContent = '✅ ' + data.message;
    } else {
        resultEl.style.color = '#dc2626';
        resultEl.textContent = '❌ ' + data.message;
    }
}

async function resetDemoLocks() {
    const formData = new FormData();
    formData.append('action', 'reset_demo');
    formData.append('csrf_token', CSRF_TOKEN);

    await fetch(SITE_URL + '/lab-demo/api.php', { method: 'POST', body: formData });
    showToast('All demo accounts unlocked & disposable attempt logs cleared.');
    refreshLogs();
    refreshAccountStatuses();
}

async function refreshLogs() {
    try {
        const resp = await fetch(SITE_URL + '/lab-demo/api.php?action=get_logs');
        const data = await resp.json();
        if (data && data.logs) {
            const tbody = document.getElementById('audit-log-body');
            tbody.innerHTML = '';
            data.logs.forEach(l => {
                const tr = document.createElement('tr');
                tr.innerHTML = `
                    <td>${l.attempted_at ? l.attempted_at.split(' ')[1] : ''}</td>
                    <td>${l.username}</td>
                    <td>${l.scenario_mode}</td>
                    <td>${l.success == 1 ? '<span style="color:#16a34a;font-weight:700;">Success</span>' : '<span style="color:#dc2626;">Failed</span>'}</td>
                    <td>${l.control_triggered}</td>
                    <td>${parseFloat(l.response_time_ms).toFixed(1)} ms</td>
                `;
                tbody.appendChild(tr);
            });
        }
    } catch (e) {
        console.error('Failed to refresh logs:', e);
    }
}

async function refreshAccountStatuses() {
    try {
        const resp = await fetch(SITE_URL + '/lab-demo/api.php?action=status');
        const data = await resp.json();
        if (data && data.accounts) {
            data.accounts.forEach(acc => {
                const el = document.getElementById('lock-status-' + acc.account_code);
                if (el) {
                    if (acc.is_locked == 1) {
                        el.innerHTML = `<span style="color:#dc2626; font-weight:700;">Locked (${acc.lockout_remaining_minutes}m left)</span>`;
                    } else {
                        el.innerHTML = `<span style="color:#16a34a;">Unlocked (${acc.failed_attempts}/5 fails)</span>`;
                    }
                }
            });
        }
    } catch (e) {
        console.error('Failed to refresh account statuses:', e);
    }
}

// ── Global Reset Demo State Button ────────────────────────────────────────
document.getElementById('btn-reset-global').addEventListener('click', async () => {
    if (confirm('Reset demonstration state? This unlocks all demo accounts, clears disposable attempt logs, and resets comparison results. Normal registrations and admin data are preserved.')) {
        await resetDemoLocks();
        clearComparisonResults();
        resetCandidateSearch();
    }
});

// ── Initialization on Page Load ───────────────────────────────────────────
document.addEventListener('DOMContentLoaded', () => {
    renderComparisonTable();
    updateSimTargetInfo();
    // Preload wordlist in background
    setTimeout(loadCandidateWordlist, 500);
});
</script>

</body>
</html>
