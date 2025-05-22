<!-- filepath: /home/joao/Documentos/EurystheusAI/plataformEurystheus/resources/views/dashboard.blade.php -->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Eurystheus</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 0; padding: 20px; }
        .header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px; }
        .user-info { background: #f8f9fa; padding: 20px; border-radius: 8px; margin-bottom: 20px; }
        .role { padding: 4px 12px; border-radius: 20px; font-size: 12px; font-weight: bold; }
        .role.admin { background: #dc3545; color: white; }
        .role.payed_user { background: #28a745; color: white; }
        .role.free_user { background: #6c757d; color: white; }
        .logout-btn { background: #dc3545; color: white; padding: 8px 16px; border: none; border-radius: 4px; cursor: pointer; }
        .admin-link { background: #007bff; color: white; padding: 8px 16px; text-decoration: none; border-radius: 4px; margin-right: 10px; }
    </style>
</head>
<body>
    <div class="header">
        <h1>Dashboard</h1>
        <div>
            @if($user->isAdmin())
                <a href="{{ route('admin.index') }}" class="admin-link">Admin Panel</a>
            @endif
            <form method="POST" action="{{ route('logout') }}" style="display: inline;">
                @csrf
                <button type="submit" class="logout-btn">Logout</button>
            </form>
        </div>
    </div>
    
    <div class="user-info">
        <h3>Welcome, {{ $user->name }}!</h3>
        <p><strong>Email:</strong> {{ $user->email }}</p>
        <p><strong>Role:</strong> <span class="role {{ $user->role->value }}">{{ $user->role->label() }}</span></p>
        <p><strong>Member since:</strong> {{ $user->created_at->format('M d, Y') }}</p>
    </div>
    
    <div class="content">
        @if($user->isAdmin())
            <h3>Admin Features</h3>
            <p>You have administrative privileges. You can manage users and system settings.</p>
        @elseif($user->isPayed())
            <h3>Premium Features</h3>
            <p>You have access to premium features as a paying user.</p>
        @else
            <h3>Free User</h3>
            <p>You're currently using the free tier. Upgrade to access premium features!</p>
        @endif
    </div>
</body>
</html>