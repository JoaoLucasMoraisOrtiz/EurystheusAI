@extends('admin.layout')

@section('title', 'Security Dashboard')

@section('content')
<div class="security-dashboard">
    <div class="dashboard-header">
        <h1>Security Dashboard</h1>
        <div class="timeframe-selector">
            <select id="timeframe" onchange="updateTimeframe()">
                <option value="1h" {{ $timeframe === '1h' ? 'selected' : '' }}>Last Hour</option>
                <option value="24h" {{ $timeframe === '24h' ? 'selected' : '' }}>Last 24 Hours</option>
                <option value="7d" {{ $timeframe === '7d' ? 'selected' : '' }}>Last 7 Days</option>
                <option value="30d" {{ $timeframe === '30d' ? 'selected' : '' }}>Last 30 Days</option>
            </select>
        </div>
    </div>

    <!-- Security Overview Cards -->
    <div class="overview-cards">
        <div class="card threat-card">
            <div class="card-header">
                <h3>Attacks Blocked</h3>
                <span class="icon">🛡️</span>
            </div>
            <div class="card-body">
                <div class="metric-value">{{ number_format($data['overview']['total_attacks_blocked']) }}</div>
                <div class="metric-label">Total Attacks Blocked</div>
            </div>
        </div>

        <div class="card attacker-card">
            <div class="card-header">
                <h3>Unique Attackers</h3>
                <span class="icon">🎯</span>
            </div>
            <div class="card-body">
                <div class="metric-value">{{ number_format($data['overview']['unique_attackers']) }}</div>
                <div class="metric-label">Unique IP Addresses</div>
            </div>
        </div>

        <div class="card login-card">
            <div class="card-header">
                <h3>Failed Logins</h3>
                <span class="icon">🔐</span>
            </div>
            <div class="card-body">
                <div class="metric-value">{{ number_format($data['overview']['failed_logins']) }}</div>
                <div class="metric-label">Authentication Failures</div>
            </div>
        </div>

        <div class="card alert-card">
            <div class="card-header">
                <h3>Active Alerts</h3>
                <span class="icon">⚠️</span>
            </div>
            <div class="card-body">
                <div class="metric-value">{{ number_format($data['overview']['active_security_alerts']) }}</div>
                <div class="metric-label">Unresolved Alerts</div>
            </div>
        </div>
    </div>

    <!-- Threat Analysis Section -->
    <div class="dashboard-section">
        <h2>Threat Analysis</h2>
        <div class="threat-analysis">
            <div class="chart-container">
                <canvas id="threatTypesChart"></canvas>
            </div>
            <div class="chart-container">
                <canvas id="attackTimelineChart"></canvas>
            </div>
        </div>
    </div>

    <!-- Top Attackers -->
    <div class="dashboard-section">
        <h2>Top Attacking IPs</h2>
        <div class="table-container">
            <table class="security-table">
                <thead>
                    <tr>
                        <th>IP Address</th>
                        <th>Attack Count</th>
                        <th>Location</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($data['threats']['top_attackers'] as $attacker)
                    <tr>
                        <td>
                            <code>{{ $attacker->ip_address }}</code>
                        </td>
                        <td>
                            <span class="badge badge-danger">{{ $attacker->attack_count }}</span>
                        </td>
                        <td>
                            <span class="location" data-ip="{{ $attacker->ip_address }}">Loading...</span>
                        </td>
                        <td>
                            <span class="status-badge status-blocked">Monitored</span>
                        </td>
                        <td>
                            <button class="btn btn-sm btn-outline-danger" onclick="blockIp('{{ $attacker->ip_address }}')">
                                Block IP
                            </button>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <!-- Attack Statistics -->
    <div class="dashboard-section">
        <h2>Attack Statistics by Type</h2>
        <div class="attack-stats">
            <div class="stat-card">
                <h4>SQL Injection</h4>
                <div class="stat-value">{{ $data['attacks']['sql_injection']['total_attempts'] ?? 0 }}</div>
                <div class="stat-detail">{{ $data['attacks']['sql_injection']['unique_attackers'] ?? 0 }} unique attackers</div>
            </div>

            <div class="stat-card">
                <h4>XSS Attacks</h4>
                <div class="stat-value">{{ $data['attacks']['xss_attacks']['total_attempts'] ?? 0 }}</div>
                <div class="stat-detail">{{ $data['attacks']['xss_attacks']['unique_attackers'] ?? 0 }} unique attackers</div>
            </div>

            <div class="stat-card">
                <h4>DoS Attacks</h4>
                <div class="stat-value">{{ $data['attacks']['dos_attacks']['total_attempts'] ?? 0 }}</div>
                <div class="stat-detail">{{ $data['attacks']['dos_attacks']['unique_attackers'] ?? 0 }} unique attackers</div>
            </div>

            <div class="stat-card">
                <h4>CSRF Attacks</h4>
                <div class="stat-value">{{ $data['attacks']['csrf_attacks']['total_attempts'] ?? 0 }}</div>
                <div class="stat-detail">{{ $data['attacks']['csrf_attacks']['unique_attackers'] ?? 0 }} unique attackers</div>
            </div>
        </div>
    </div>

    <!-- Vulnerabilities Section -->
    @if($data['vulnerabilities']['total_vulnerabilities'] > 0)
    <div class="dashboard-section">
        <h2>Security Vulnerabilities</h2>
        <div class="vulnerability-overview">
            <div class="vuln-summary">
                <div class="vuln-count critical">
                    {{ $data['vulnerabilities']['severity_breakdown']['critical'] ?? 0 }}
                    <span>Critical</span>
                </div>
                <div class="vuln-count high">
                    {{ $data['vulnerabilities']['severity_breakdown']['high'] ?? 0 }}
                    <span>High</span>
                </div>
                <div class="vuln-count medium">
                    {{ $data['vulnerabilities']['severity_breakdown']['medium'] ?? 0 }}
                    <span>Medium</span>
                </div>
                <div class="vuln-count low">
                    {{ $data['vulnerabilities']['severity_breakdown']['low'] ?? 0 }}
                    <span>Low</span>
                </div>
            </div>
        </div>

        <div class="table-container">
            <table class="security-table">
                <thead>
                    <tr>
                        <th>Package</th>
                        <th>Severity</th>
                        <th>Description</th>
                        <th>Detected</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($data['vulnerabilities']['recent_vulnerabilities'] as $vuln)
                    <tr>
                        <td>
                            @php $details = json_decode($vuln->details, true) @endphp
                            <code>{{ $details['package'] ?? 'Unknown' }}</code>
                            <small>v{{ $details['version'] ?? 'Unknown' }}</small>
                        </td>
                        <td>
                            <span class="severity-badge severity-{{ $vuln->severity }}">
                                {{ ucfirst($vuln->severity) }}
                            </span>
                        </td>
                        <td>{{ $details['description'] ?? $vuln->message }}</td>
                        <td>{{ $vuln->created_at }}</td>
                        <td>
                            <button class="btn btn-sm btn-outline-success" onclick="resolveAlert({{ $vuln->id }})">
                                Mark Resolved
                            </button>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif

    <!-- Blocked IPs Section -->
    <div class="dashboard-section">
        <h2>Blocked IP Addresses</h2>
        <div class="blocked-ips-stats">
            <div class="stat-item">
                <span class="stat-number">{{ $data['blockedIps']['total_blocked'] }}</span>
                <span class="stat-label">Currently Blocked</span>
            </div>
            <div class="stat-item">
                <span class="stat-number">{{ $data['blockedIps']['recent_blocks_24h'] }}</span>
                <span class="stat-label">Blocked in 24h</span>
            </div>
        </div>

        <div class="table-container">
            <table class="security-table">
                <thead>
                    <tr>
                        <th>IP Address</th>
                        <th>Reason</th>
                        <th>Blocked At</th>
                        <th>Expires At</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($data['blockedIps']['active_blocks'] as $blockedIp)
                    <tr>
                        <td><code>{{ $blockedIp->ip_address }}</code></td>
                        <td>{{ $blockedIp->reason }}</td>
                        <td>{{ $blockedIp->created_at }}</td>
                        <td>{{ $blockedIp->expires_at }}</td>
                        <td>
                            <button class="btn btn-sm btn-outline-warning" onclick="unblockIp({{ $blockedIp->id }})">
                                Unblock
                            </button>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <!-- System Health -->
    <div class="dashboard-section">
        <h2>System Security Health</h2>
        <div class="health-grid">
            <div class="health-item">
                <h4>Security Middleware</h4>
                <div class="health-status">
                    @foreach($data['systemHealth']['security_middleware_status'] as $middleware => $status)
                    <div class="middleware-status">
                        <span class="status-indicator {{ $status ? 'active' : 'inactive' }}"></span>
                        {{ $middleware }}
                    </div>
                    @endforeach
                </div>
            </div>

            <div class="health-item">
                <h4>Configuration</h4>
                <div class="config-status">
                    @foreach($data['systemHealth']['security_config_status'] as $config => $status)
                    <div class="config-item">
                        <span class="status-indicator {{ $status ? 'active' : 'inactive' }}"></span>
                        {{ ucfirst(str_replace('_', ' ', $config)) }}
                    </div>
                    @endforeach
                </div>
            </div>

            <div class="health-item">
                <h4>System Services</h4>
                <div class="service-status">
                    <div class="service-item">
                        <span class="status-indicator {{ $data['systemHealth']['database_connectivity'] ? 'active' : 'inactive' }}"></span>
                        Database
                    </div>
                    <div class="service-item">
                        <span class="status-indicator {{ $data['systemHealth']['cache_status'] ? 'active' : 'inactive' }}"></span>
                        Cache
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Export Section -->
    <div class="dashboard-section">
        <h2>Export Security Data</h2>
        <div class="export-controls">
            <select id="exportType">
                <option value="audit_log">Audit Log</option>
                <option value="security_alerts">Security Alerts</option>
                <option value="blocked_ips">Blocked IPs</option>
            </select>
            <input type="date" id="startDate" value="{{ now()->subDays(7)->format('Y-m-d') }}">
            <input type="date" id="endDate" value="{{ now()->format('Y-m-d') }}">
            <button class="btn btn-primary" onclick="exportData()">Export CSV</button>
        </div>
    </div>
</div>

<style>
.security-dashboard {
    padding: 20px;
    background: #f8f9fa;
    min-height: 100vh;
}

.dashboard-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 30px;
}

.dashboard-header h1 {
    color: #2c3e50;
    margin: 0;
}

.timeframe-selector select {
    padding: 8px 12px;
    border: 1px solid #ddd;
    border-radius: 4px;
    background: white;
}

.overview-cards {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 20px;
    margin-bottom: 30px;
}

.card {
    background: white;
    border-radius: 8px;
    padding: 20px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    border-left: 4px solid;
}

.threat-card { border-left-color: #e74c3c; }
.attacker-card { border-left-color: #f39c12; }
.login-card { border-left-color: #3498db; }
.alert-card { border-left-color: #9b59b6; }

.card-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 15px;
}

.card-header h3 {
    margin: 0;
    color: #2c3e50;
    font-size: 16px;
}

.card-header .icon {
    font-size: 24px;
}

.metric-value {
    font-size: 36px;
    font-weight: bold;
    color: #2c3e50;
    margin-bottom: 5px;
}

.metric-label {
    color: #7f8c8d;
    font-size: 14px;
}

.dashboard-section {
    background: white;
    border-radius: 8px;
    padding: 25px;
    margin-bottom: 30px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.dashboard-section h2 {
    color: #2c3e50;
    margin: 0 0 20px 0;
    font-size: 24px;
}

.threat-analysis {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 30px;
}

.chart-container {
    position: relative;
    height: 300px;
}

.security-table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 15px;
}

.security-table th,
.security-table td {
    padding: 12px;
    text-align: left;
    border-bottom: 1px solid #eee;
}

.security-table th {
    background: #f8f9fa;
    font-weight: 600;
    color: #2c3e50;
}

.badge {
    padding: 4px 8px;
    border-radius: 4px;
    font-size: 12px;
    font-weight: bold;
}

.badge-danger {
    background: #e74c3c;
    color: white;
}

.status-badge {
    padding: 4px 8px;
    border-radius: 4px;
    font-size: 12px;
    font-weight: bold;
}

.status-blocked {
    background: #f39c12;
    color: white;
}

.attack-stats {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 20px;
}

.stat-card {
    background: #f8f9fa;
    padding: 20px;
    border-radius: 6px;
    text-align: center;
}

.stat-card h4 {
    margin: 0 0 10px 0;
    color: #2c3e50;
}

.stat-value {
    font-size: 28px;
    font-weight: bold;
    color: #e74c3c;
    margin-bottom: 5px;
}

.stat-detail {
    font-size: 12px;
    color: #7f8c8d;
}

.vulnerability-overview {
    margin-bottom: 20px;
}

.vuln-summary {
    display: flex;
    gap: 20px;
    justify-content: center;
}

.vuln-count {
    text-align: center;
    padding: 15px;
    border-radius: 6px;
    color: white;
    font-weight: bold;
    min-width: 80px;
}

.vuln-count.critical { background: #c0392b; }
.vuln-count.high { background: #e74c3c; }
.vuln-count.medium { background: #f39c12; }
.vuln-count.low { background: #f1c40f; color: #2c3e50; }

.vuln-count span {
    display: block;
    font-size: 12px;
    margin-top: 5px;
}

.severity-badge {
    padding: 4px 8px;
    border-radius: 4px;
    font-size: 12px;
    font-weight: bold;
}

.severity-critical { background: #c0392b; color: white; }
.severity-high { background: #e74c3c; color: white; }
.severity-medium { background: #f39c12; color: white; }
.severity-low { background: #f1c40f; color: #2c3e50; }

.blocked-ips-stats {
    display: flex;
    gap: 40px;
    margin-bottom: 20px;
}

.stat-item {
    text-align: center;
}

.stat-number {
    display: block;
    font-size: 32px;
    font-weight: bold;
    color: #e74c3c;
}

.stat-label {
    font-size: 14px;
    color: #7f8c8d;
}

.health-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 20px;
}

.health-item {
    background: #f8f9fa;
    padding: 20px;
    border-radius: 6px;
}

.health-item h4 {
    margin: 0 0 15px 0;
    color: #2c3e50;
}

.middleware-status,
.config-item,
.service-item {
    display: flex;
    align-items: center;
    margin-bottom: 8px;
    font-size: 14px;
}

.status-indicator {
    width: 12px;
    height: 12px;
    border-radius: 50%;
    margin-right: 10px;
}

.status-indicator.active {
    background: #27ae60;
}

.status-indicator.inactive {
    background: #e74c3c;
}

.export-controls {
    display: flex;
    gap: 15px;
    align-items: center;
}

.export-controls select,
.export-controls input {
    padding: 8px 12px;
    border: 1px solid #ddd;
    border-radius: 4px;
}

.btn {
    padding: 8px 16px;
    border: none;
    border-radius: 4px;
    cursor: pointer;
    font-size: 14px;
    text-decoration: none;
    display: inline-block;
}

.btn-primary {
    background: #3498db;
    color: white;
}

.btn-outline-danger {
    background: transparent;
    color: #e74c3c;
    border: 1px solid #e74c3c;
}

.btn-outline-success {
    background: transparent;
    color: #27ae60;
    border: 1px solid #27ae60;
}

.btn-outline-warning {
    background: transparent;
    color: #f39c12;
    border: 1px solid #f39c12;
}

.btn:hover {
    opacity: 0.9;
}

.table-container {
    overflow-x: auto;
}

@media (max-width: 768px) {
    .overview-cards {
        grid-template-columns: 1fr;
    }
    
    .threat-analysis {
        grid-template-columns: 1fr;
    }
    
    .attack-stats {
        grid-template-columns: repeat(2, 1fr);
    }
    
    .health-grid {
        grid-template-columns: 1fr;
    }
    
    .export-controls {
        flex-direction: column;
        align-items: stretch;
    }
}
</style>

<script>
function updateTimeframe() {
    const timeframe = document.getElementById('timeframe').value;
    window.location.href = `{{ route('admin.security.dashboard') }}?timeframe=${timeframe}`;
}

function blockIp(ipAddress) {
    if (confirm(`Are you sure you want to block IP address ${ipAddress}?`)) {
        // Implementation would send request to block IP
        console.log('Blocking IP:', ipAddress);
    }
}

function unblockIp(ipId) {
    if (confirm('Are you sure you want to unblock this IP address?')) {
        fetch(`/admin/security/unblock-ip/${ipId}`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({
                reason: 'Manual unblock from dashboard'
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert('Failed to unblock IP address');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Failed to unblock IP address');
        });
    }
}

function resolveAlert(alertId) {
    const notes = prompt('Enter resolution notes (optional):');
    if (notes !== null) {
        fetch(`/admin/security/resolve-alert/${alertId}`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({
                notes: notes
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert('Failed to resolve alert');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Failed to resolve alert');
        });
    }
}

function exportData() {
    const type = document.getElementById('exportType').value;
    const startDate = document.getElementById('startDate').value;
    const endDate = document.getElementById('endDate').value;
    
    const url = `{{ route('admin.security.export') }}?type=${type}&start_date=${startDate}&end_date=${endDate}`;
    window.open(url, '_blank');
}

// Initialize charts when page loads
document.addEventListener('DOMContentLoaded', function() {
    // Threat Types Chart
    const threatTypesCtx = document.getElementById('threatTypesChart').getContext('2d');
    new Chart(threatTypesCtx, {
        type: 'doughnut',
        data: {
            labels: {!! json_encode($data['threats']['threat_types']->pluck('event_type')) !!},
            datasets: [{
                data: {!! json_encode($data['threats']['threat_types']->pluck('count')) !!},
                backgroundColor: [
                    '#e74c3c',
                    '#f39c12',
                    '#3498db',
                    '#9b59b6',
                    '#27ae60'
                ]
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                title: {
                    display: true,
                    text: 'Threat Types Distribution'
                }
            }
        }
    });

    // Attack Timeline Chart
    const timelineCtx = document.getElementById('attackTimelineChart').getContext('2d');
    new Chart(timelineCtx, {
        type: 'line',
        data: {
            labels: {!! json_encode($data['threats']['timeline']->pluck('hour')) !!},
            datasets: [{
                label: 'Attacks',
                data: {!! json_encode($data['threats']['timeline']->pluck('attacks')) !!},
                borderColor: '#e74c3c',
                backgroundColor: 'rgba(231, 76, 60, 0.1)',
                tension: 0.1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                title: {
                    display: true,
                    text: 'Attack Timeline'
                }
            },
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });

    // Load IP location data
    document.querySelectorAll('.location').forEach(element => {
        const ip = element.dataset.ip;
        // You would implement IP geolocation lookup here
        element.textContent = 'Unknown';
    });
});
</script>

<!-- Include Chart.js for visualizations -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
@endsection
