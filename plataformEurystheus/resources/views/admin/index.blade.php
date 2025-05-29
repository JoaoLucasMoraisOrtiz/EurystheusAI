<!-- filepath: /home/joao/Documentos/EurystheusAI/plataformEurystheus/resources/views/admin/index.blade.php -->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Admin Panel - Eurystheus</title>
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; margin: 0; padding: 20px; background-color: #f4f7f6; color: #333; }
        .container { max-width: 1200px; margin: 0 auto; }
        .header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px; padding-bottom: 20px; border-bottom: 1px solid #ddd; }
        .header h1 { color: #2c3e50; }
        .back-btn { background: #6c757d; color: white; padding: 10px 18px; text-decoration: none; border-radius: 5px; font-size: 0.9em; transition: background-color 0.3s; }
        .back-btn:hover { background: #5a6268; }
        
        .stats-section { display: flex; justify-content: space-around; margin-bottom: 30px; }
        .stat-card { background: #ffffff; padding: 20px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.05); text-align: center; flex-basis: 30%; }
        .stat-card h3 { margin-top: 0; color: #3498db; font-size: 1.2em; }
        .stat-card p { font-size: 2em; color: #2c3e50; margin: 10px 0 0 0; }

        .content-section { background: #ffffff; padding: 25px; border-radius: 8px; margin-bottom: 30px; box-shadow: 0 2px 10px rgba(0,0,0,0.05); }
        .content-section h2 { color: #3498db; margin-top: 0; border-bottom: 1px solid #eee; padding-bottom: 10px; margin-bottom: 20px; }
        
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { padding: 12px 15px; text-align: left; border-bottom: 1px solid #e0e0e0; }
        th { background-color: #f8f9fa; color: #495057; text-transform: uppercase; font-size: 0.85em; }
        tr:hover { background-color: #f1f1f1; }
        .role { padding: 5px 12px; border-radius: 20px; font-size: 0.8em; font-weight: bold; text-transform: uppercase; letter-spacing: 0.5px; display: inline-block; }
        .role.admin { background: #e74c3c; color: white; }
        .role.payed_user { background: #2ecc71; color: white; }
        .role.free_user { background: #95a5a6; color: white; }
        select { padding: 6px 10px; border-radius: 4px; border: 1px solid #ced4da; font-size: 0.9em; }
        
        .prompt-logs-list { list-style: none; padding: 0; }
        .prompt-logs-list li { background-color: #e9f5ff; padding: 10px; margin-bottom: 8px; border-radius: 4px; border-left: 3px solid #3498db; font-size: 0.9em; white-space: pre-wrap; word-break: break-all; }

        .alert { padding: 15px; margin-bottom: 20px; border-radius: 4px; }
        .alert.success { background-color: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .alert.error { background-color: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
        .pagination { margin-top: 20px; }
        
        .analytics-overview { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 25px; border-radius: 12px; margin: 20px 0; }
        .analytics-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; }
        .analytics-card { background: rgba(255,255,255,0.1); padding: 20px; border-radius: 8px; text-align: center; backdrop-filter: blur(10px); }
        .analytics-card h4 { margin: 0; font-size: 0.9em; text-transform: uppercase; letter-spacing: 1px; opacity: 0.9; }
        .analytics-card .value { font-size: 2.2em; font-weight: bold; margin: 10px 0; }
        .analytics-card .detail { font-size: 0.85em; opacity: 0.8; }
        
        .telemetry-card { background: #fff; border-left: 4px solid #3498db; }
        .telemetry-card.visits { border-color: #e74c3c; }
        .telemetry-card.users { border-color: #27ae60; }
        .telemetry-card.prompts { border-color: #f39c12; }

        .refresh-indicator { 
            position: fixed; 
            top: 20px; 
            right: 20px; 
            background: #3498db; 
            color: white; 
            padding: 10px 15px; 
            border-radius: 20px; 
            font-size: 0.8em; 
            z-index: 1000;
            opacity: 0;
            transition: opacity 0.3s;
        }
        .refresh-indicator.show { opacity: 1; }
        .export-btn { 
            background: #27ae60; 
            color: white; 
            padding: 8px 16px; 
            text-decoration: none; 
            border-radius: 4px; 
            margin: 0 5px; 
        }
        .export-btn:hover { background: #229954; }
    </style>
    <script>
        // Auto-refresh das métricas a cada 60 segundos
        setInterval(function() {
            const indicator = document.querySelector('.refresh-indicator');
            indicator.classList.add('show');
            
            fetch('{{ route("admin.analytics.refresh") }}')
                .then(response => response.json())
                .then(data => {
                    // Atualizar apenas os valores numéricos
                    document.querySelector('.stat-users .value').textContent = data.totalUsers;
                    document.querySelector('.stat-visits .value').textContent = (data.homeVisits + data.salesVisits).toLocaleString();
                    document.querySelector('.stat-prompts .value').textContent = data.freeUserPromptAverage.toFixed(1);
                    
                    setTimeout(() => {
                        indicator.classList.remove('show');
                    }, 2000);
                })
                .catch(error => {
                    console.log('Refresh failed:', error);
                    setTimeout(() => {
                        indicator.classList.remove('show');
                    }, 2000);
                });
        }, 60000); // 60 segundos
    </script>
</head>
<body>
    <div class="refresh-indicator">🔄 Updating metrics...</div>
    
    <div class="container">
        <div class="header">
            <h1>Admin Panel</h1>
            <a href="{{ route('dashboard') }}" class="back-btn">Back to Dashboard</a>
        </div>

        @if(session('success'))
            <div class="alert success">{{ session('success') }}</div>
        @endif
        
        @if(session('error'))
            <div class="alert error">{{ session('error') }}</div>
        @endif

        <div class="stats-section">
            <div class="stat-card telemetry-card users">
                <h3>👥 Total Users</h3>
                <p>{{ $totalUsers }}</p>
                <small>
                    <span style="color: #27ae60; font-weight: bold;">💎 {{ $payedUsers }} paid</span> |
                    <span style="color: #95a5a6; font-weight: bold;">🆓 {{ $freeUsers }} free</span>
                </small>
            </div>
            <div class="stat-card telemetry-card visits">
                <h3>🌐 Page Traffic (30d)</h3>
                <p>{{ number_format($homeVisits + $salesVisits) }}</p>
                <small>
                    🏠 Home: {{ number_format($homeVisits) }} | 💰 Sales: {{ number_format($salesVisits) }}
                </small>
            </div>
            <div class="stat-card telemetry-card prompts">
                <h3>📊 Avg Usage</h3>
                <p>{{ number_format($freeUserPromptAverage, 1) }}</p>
                <small>
                    📝 Free users average
                    @if($payedUserPromptAverage > 0)
                        <br>💎 Paid: {{ number_format($payedUserPromptAverage, 1) }}
                    @endif
                </small>
            </div>
        </div>

        <div class="stats-section">
            <div class="stat-card">
                <h3>🎁 Active Promotions</h3>
                <p>{{ $activePromotions }}</p>
                <small>{{ $totalPromotions }} total promotions</small>
            </div>
            <div class="stat-card">
                <h3>📈 Conversion Rate</h3>
                <p>{{ $salesVisits > 0 && $homeVisits > 0 ? number_format(($salesVisits / $homeVisits) * 100, 1) : 0 }}%</p>
                <small>Home → Sales conversion</small>
            </div>
            <div class="stat-card">
                <h3>📋 Recent Logs</h3>
                <p>{{ $promptLogContents->count() }}</p>
                <small>Last 50 prompt entries</small>
            </div>
        </div>

        @if($currentPromotion)
            <div class="content-section">
                <h2>🎯 Current Active Promotion</h2>
                <div style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 20px; border-radius: 8px; margin-bottom: 20px;">
                    <div style="display: flex; justify-content: space-between; align-items: center;">
                        <div>
                            <h3 style="margin: 0; font-size: 1.4em;">{{ $currentPromotion->name }}</h3>
                            <p style="margin: 5px 0;">Code: <strong>{{ $currentPromotion->code }}</strong></p>
                            <p style="margin: 5px 0;">
                                <span style="text-decoration: line-through; opacity: 0.7;">{{ $currentPromotion->formatted_original_price }}</span>
                                <span style="font-size: 1.2em; font-weight: bold; margin-left: 10px;">{{ $currentPromotion->formatted_discounted_price }}</span>
                                <span style="background: rgba(255,255,255,0.2); padding: 2px 6px; border-radius: 4px; font-size: 0.8em; margin-left: 10px;">{{ $currentPromotion->discount_percentage }}% OFF</span>
                            </p>
                            <p style="margin: 5px 0; font-size: 0.9em;">
                                Uses: {{ $currentPromotion->current_uses }}{{ $currentPromotion->max_uses ? ' / ' . $currentPromotion->max_uses : ' (unlimited)' }}
                            </p>
                        </div>
                        <div style="text-align: right;">
                            <a href="{{ route('admin.promotions.edit', $currentPromotion) }}" 
                               style="background: rgba(255,255,255,0.2); color: white; padding: 8px 16px; text-decoration: none; border-radius: 4px; font-size: 0.9em;">
                                Edit Promotion
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        <div class="content-section">
            <h2>🎁 Promotion Management</h2>
            <p>Create and manage promotional campaigns to boost sales conversions.</p>
            <div style="display: flex; gap: 15px; margin-top: 20px;">
                <a href="{{ route('admin.promotions.index') }}" 
                   class="translation-btn"
                   style="background: #e74c3c; color: white; padding: 12px 20px; text-decoration: none; border-radius: 5px; font-weight: 500; transition: background-color 0.3s;">
                    📊 Manage Promotions
                </a>
                <a href="{{ route('admin.promotions.create') }}" 
                   class="translation-btn"
                   style="background: #27ae60; color: white; padding: 12px 20px; text-decoration: none; border-radius: 5px; font-weight: 500; transition: background-color 0.3s;">
                    ➕ Create New Promotion
                </a>
            </div>
        </div>

        <div class="content-section">
            <h2>🛡️ Security Monitoring</h2>
            <p>Monitor system security, view attack statistics, and manage security threats in real-time.</p>
            <div style="display: flex; gap: 15px; margin-top: 20px;">
                <a href="{{ route('admin.security.dashboard') }}" 
                   class="translation-btn"
                   style="background: #e74c3c; color: white; padding: 12px 20px; text-decoration: none; border-radius: 5px; font-weight: 500; transition: background-color 0.3s;">
                    🛡️ Security Dashboard
                </a>
                <a href="{{ route('admin.security.alerts') }}" 
                   class="translation-btn"
                   style="background: #f39c12; color: white; padding: 12px 20px; text-decoration: none; border-radius: 5px; font-weight: 500; transition: background-color 0.3s;">
                    ⚠️ Security Alerts
                </a>
                <a href="{{ route('admin.security.blocked-ips') }}" 
                   class="translation-btn"
                   style="background: #9b59b6; color: white; padding: 12px 20px; text-decoration: none; border-radius: 5px; font-weight: 500; transition: background-color 0.3s;">
                    🚫 Blocked IPs
                </a>
            </div>
        </div>

        <div class="content-section">
            <h2>⚙️ System Settings</h2>
            <p>Configure system-wide settings including user prompt limits and other parameters.</p>
            <div style="display: flex; gap: 15px; margin-top: 20px;">
                <a href="{{ route('admin.settings.index') }}" 
                   class="translation-btn"
                   style="background: #9b59b6; color: white; padding: 12px 20px; text-decoration: none; border-radius: 5px; font-weight: 500; transition: background-color 0.3s;">
                    🛠️ Manage Settings
                </a>
            </div>
        </div>

        <div class="content-section">
            <h2>Translation Management</h2>
            <p>Manage language translations for the website interface.</p>
            <div style="display: flex; gap: 15px; margin-top: 20px;">
                <a href="{{ route('admin.translations.index', 'marketing') }}" 
                   class="translation-btn"
                   style="background: #3498db; color: white; padding: 12px 20px; text-decoration: none; border-radius: 5px; font-weight: 500; transition: background-color 0.3s;">
                    Marketing Translations
                </a>
                <a href="{{ route('admin.translations.index', 'general') }}" 
                   class="translation-btn"
                   style="background: #2ecc71; color: white; padding: 12px 20px; text-decoration: none; border-radius: 5px; font-weight: 500; transition: background-color 0.3s;">
                    General Translations
                </a>
                <a href="{{ route('admin.translations.index', 'auth') }}" 
                   class="translation-btn"
                   style="background: #e74c3c; color: white; padding: 12px 20px; text-decoration: none; border-radius: 5px; font-weight: 500; transition: background-color 0.3s;">
                    Auth Translations
                </a>
                <a href="{{ route('admin.translations.index', 'dashboard') }}" 
                   class="translation-btn"
                   style="background: #f39c12; color: white; padding: 12px 20px; text-decoration: none; border-radius: 5px; font-weight: 500; transition: background-color 0.3s;">
                    Dashboard Translations
                </a>
            </div>
        </div>

        <div class="content-section">
            <h2>📊 Analytics Overview</h2>
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                <p>View detailed analytics and export reports</p>
                <div>
                    <a href="{{ route('admin.analytics.export', ['type' => 'users']) }}" class="export-btn">📊 Export Users CSV</a>
                    <a href="{{ route('admin.analytics.export', ['type' => 'analytics']) }}" class="export-btn">📈 Export Analytics CSV</a>
                    <a href="{{ route('admin.analytics.export', ['type' => 'prompts']) }}" class="export-btn">💬 Export Prompts CSV</a>
                </div>
            </div>
            
            <!-- Include charts here -->
            @include('admin.analytics-charts')
            
            <div style="background: #f8f9fa; padding: 20px; border-radius: 8px; margin-bottom: 20px;">
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px;">
                    <div style="text-align: center;" class="stat-visits">
                        <h4 style="margin: 0; color: #2c3e50;">Page Traffic (30 days)</h4>
                        <p class="value" style="font-size: 1.5em; margin: 5px 0; color: #e74c3c;">{{ $homeVisits + $salesVisits }}</p>
                        <small style="color: #7f8c8d;">Home: {{ $homeVisits }} | Sales: {{ $salesVisits }}</small>
                    </div>
                    <div style="text-align: center;" class="stat-users">
                        <h4 style="margin: 0; color: #2c3e50;">User Distribution</h4>
                        <p class="value" style="font-size: 1.5em; margin: 5px 0; color: #3498db;">{{ $totalUsers }}</p>
                        <small style="color: #7f8c8d;">
                            @if($totalUsers > 0)
                                {{ number_format(($freeUsers/$totalUsers)*100, 1) }}% Free | 
                                {{ number_format(($payedUsers/$totalUsers)*100, 1) }}% Paid
                            @else
                                No users yet
                            @endif
                        </small>
                    </div>
                    <div style="text-align: center;" class="stat-prompts">
                        <h4 style="margin: 0; color: #2c3e50;">Avg Prompt Usage</h4>
                        <p class="value" style="font-size: 1.5em; margin: 5px 0; color: #27ae60;">{{ number_format($freeUserPromptAverage, 1) }}</p>
                        <small style="color: #7f8c8d;">
                            Free users average
                            @if($payedUserPromptAverage > 0)
                                | Paid: {{ number_format($payedUserPromptAverage, 1) }}
                            @endif
                        </small>
                    </div>
                    <div style="text-align: center;">
                        <h4 style="margin: 0; color: #2c3e50;">Today's Activity</h4>
                        <p style="font-size: 1.5em; margin: 5px 0; color: #f39c12;">{{ $newUsersToday }}</p>
                        <small style="color: #7f8c8d;">New users | {{ $totalPromptsToday }} prompts</small>
                    </div>
                </div>
            </div>
        </div>

        <div class="content-section">
            <h2>User Management</h2>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Joined</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($users as $user)
                        <tr>
                            <td>{{ $user->id }}</td>
                            <td>{{ $user->name }}</td>
                            <td>{{ $user->email }}</td>
                            <td><span class="role {{ str_replace('_', '-', $user->role->value) }}">{{ $user->role->label() }}</span></td>
                            <td>{{ $user->created_at->format('M d, Y') }}</td>
                            <td>
                                <form method="POST" action="{{ route('admin.users.role.update', $user) }}" style="display: inline;">
                                    @csrf
                                    @method('PATCH')
                                    <select name="role" onchange="this.form.submit()">
                                        @foreach(\App\Enums\UserRole::cases() as $role)
                                            <option value="{{ $role->value }}" {{ $user->role === $role ? 'selected' : '' }}>
                                                {{ $role->label() }}
                                            </option>
                                        @endforeach
                                    </select>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            
            <div class="pagination">
                {{ $users->links() }}
            </div>
        </div>

        <div class="content-section">
            <h2>Recent Prompt Log Contents</h2>
            @if($promptLogContents->count() > 0)
                <ul class="prompt-logs-list">
                    @foreach($promptLogContents as $content)
                        <li>{{ $content }}</li>
                    @endforeach
                </ul>
            @else
                <p>No prompt logs found.</p>
            @endif
        </div>
        
    </div>
</body>
</html>