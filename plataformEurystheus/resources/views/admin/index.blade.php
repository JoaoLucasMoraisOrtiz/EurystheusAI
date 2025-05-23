<!-- filepath: /home/joao/Documentos/EurystheusAI/plataformEurystheus/resources/views/admin/index.blade.php -->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
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
    </style>
</head>
<body>
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
            <div class="stat-card">
                <h3>Total Users</h3>
                <p>{{ $totalUsers }}</p>
            </div>
            <div class="stat-card">
                <h3>Home Page Visits</h3>
                <p>{{ $homeVisits }}</p>
                <small>(Placeholder - Tracking not implemented)</small>
            </div>
            <div class="stat-card">
                <h3>Prompt Logs (Recent)</h3>
                <p>{{ $promptLogContents->count() }}</p>
                 <small>(Showing latest 50)</small>
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
    </div>
</body>
</html>