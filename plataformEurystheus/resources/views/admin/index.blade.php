<!-- filepath: /home/joao/Documentos/EurystheusAI/plataformEurystheus/resources/views/admin/index.blade.php -->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel - Eurystheus</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 0; padding: 20px; }
        .header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { padding: 12px; text-align: left; border-bottom: 1px solid #ddd; }
        th { background-color: #f8f9fa; }
        .role { padding: 4px 12px; border-radius: 20px; font-size: 12px; font-weight: bold; }
        .role.admin { background: #dc3545; color: white; }
        .role.payed_user { background: #28a745; color: white; }
        .role.free_user { background: #6c757d; color: white; }
        select { padding: 4px 8px; border-radius: 4px; }
        .back-btn { background: #6c757d; color: white; padding: 8px 16px; text-decoration: none; border-radius: 4px; }
        .alert { padding: 12px; margin-bottom: 20px; border-radius: 4px; }
        .alert.success { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .alert.error { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
    </style>
</head>
<body>
    <div class="header">
        <h1>Admin Panel - User Management</h1>
        <a href="{{ route('dashboard') }}" class="back-btn">Back to Dashboard</a>
    </div>
    
    @if(session('success'))
        <div class="alert success">{{ session('success') }}</div>
    @endif
    
    @if(session('error'))
        <div class="alert error">{{ session('error') }}</div>
    @endif
    
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
                    <td><span class="role {{ $user->role->value }}">{{ $user->role->label() }}</span></td>
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
    
    <div style="margin-top: 20px;">
        {{ $users->links() }}
    </div>
</body>
</html>