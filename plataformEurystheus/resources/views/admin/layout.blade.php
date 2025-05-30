<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Admin Panel') - Eurystheus</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        /* Admin-specific styles */
        body { 
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; 
            margin: 0; 
            padding: 0; 
            background-color: #f8fafc; 
            color: #1a202c; 
        }
        
        .admin-container { 
            max-width: 1200px; 
            margin: 0 auto; 
            padding: 20px; 
        }
        
        .admin-header { 
            display: flex; 
            justify-content: space-between; 
            align-items: center; 
            margin-bottom: 30px; 
            padding-bottom: 20px; 
            border-bottom: 2px solid #e2e8f0; 
        }
        
        .admin-header h1 { 
            color: #2d3748; 
            margin: 0;
            font-size: 2rem;
            font-weight: 700;
        }
        
        .back-btn { 
            background: #4a5568; 
            color: white; 
            padding: 10px 18px; 
            text-decoration: none; 
            border-radius: 6px; 
            font-size: 0.9em; 
            transition: all 0.2s; 
            font-weight: 500;
        }
        
        .back-btn:hover { 
            background: #2d3748; 
            transform: translateY(-1px);
        }

        /* Security Dashboard Specific Styles */
        .security-dashboard {
            padding: 20px;
        }

        .dashboard-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 2px solid #e2e8f0;
        }

        .dashboard-header h1 {
            color: #2d3748;
            margin: 0;
            font-size: 2rem;
            font-weight: 700;
        }

        .timeframe-selector select {
            padding: 8px 12px;
            border: 1px solid #d1d5db;
            border-radius: 6px;
            background: white;
            font-size: 0.9rem;
        }

        .overview-cards {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .overview-card {
            background: white;
            padding: 24px;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
            border: 1px solid #e2e8f0;
        }

        .overview-card h3 {
            margin: 0 0 16px 0;
            color: #374151;
            font-size: 1.1rem;
            font-weight: 600;
        }

        .overview-card .metric {
            font-size: 2.5rem;
            font-weight: 700;
            color: #1f2937;
            margin: 0;
        }

        .overview-card .change {
            font-size: 0.875rem;
            margin-top: 8px;
        }

        .change.positive { color: #059669; }
        .change.negative { color: #dc2626; }
        .change.neutral { color: #6b7280; }

        .section {
            background: white;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
            border: 1px solid #e2e8f0;
            margin-bottom: 30px;
            overflow: hidden;
        }

        .section-header {
            padding: 20px 24px;
            border-bottom: 1px solid #e2e8f0;
            background: #f8fafc;
        }

        .section-header h2 {
            margin: 0;
            color: #374151;
            font-size: 1.25rem;
            font-weight: 600;
        }

        .section-content {
            padding: 24px;
        }

        .alert-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 16px;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            margin-bottom: 12px;
        }

        .alert-item:last-child {
            margin-bottom: 0;
        }

        .alert-severity {
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
        }

        .severity-low { background: #dcfce7; color: #166534; }
        .severity-medium { background: #fef3c7; color: #92400e; }
        .severity-high { background: #fecaca; color: #991b1b; }
        .severity-critical { background: #fca5a5; color: #7f1d1d; }

        .btn {
            display: inline-block;
            padding: 8px 16px;
            border: none;
            border-radius: 6px;
            text-decoration: none;
            font-size: 0.875rem;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.2s;
        }

        .btn-primary {
            background: #3b82f6;
            color: white;
        }

        .btn-primary:hover {
            background: #2563eb;
        }

        .btn-secondary {
            background: #6b7280;
            color: white;
        }

        .btn-secondary:hover {
            background: #4b5563;
        }

        .btn-danger {
            background: #ef4444;
            color: white;
        }

        .btn-danger:hover {
            background: #dc2626;
        }

        .table {
            width: 100%;
            border-collapse: collapse;
        }

        .table th,
        .table td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #e5e7eb;
        }

        .table th {
            background: #f9fafb;
            font-weight: 600;
            color: #374151;
        }

        .table tr:hover {
            background: #f9fafb;
        }
    </style>
    @yield('styles')
</head>
<body>
    <div class="admin-container">
        <div class="admin-header">
            <h1>@yield('title', 'Admin Panel')</h1>
            <a href="{{ route('dashboard') }}" class="back-btn">← Back to Dashboard</a>
        </div>

        @if(session('success'))
            <div class="alert alert-success" style="background: #dcfce7; color: #166534; padding: 12px 16px; border-radius: 8px; margin-bottom: 20px; border: 1px solid #bbf7d0;">
                {{ session('success') }}
            </div>
        @endif
        
        @if(session('error'))
            <div class="alert alert-error" style="background: #fecaca; color: #991b1b; padding: 12px 16px; border-radius: 8px; margin-bottom: 20px; border: 1px solid #fca5a5;">
                {{ session('error') }}
            </div>
        @endif

        @yield('content')
    </div>

    @yield('scripts')
</body>
</html>
