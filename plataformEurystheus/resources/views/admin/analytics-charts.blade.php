<!DOCTYPE html>
<html>
<head>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
    <!-- Gráfico de Visitas dos Últimos 7 Dias -->
    <div class="chart-container" style="position: relative; height:400px; width:100%; margin: 20px 0;">
        <canvas id="visitsChart"></canvas>
    </div>
    
    <!-- Gráfico de Distribuição de Usuários -->
    <div class="chart-container" style="position: relative; height:300px; width:100%; margin: 20px 0;">
        <canvas id="usersChart"></canvas>
    </div>

    <script>
        // Gráfico de Visitas
        const visitsCtx = document.getElementById('visitsChart').getContext('2d');
        new Chart(visitsCtx, {
            type: 'line',
            data: {
                labels: @json($last7Days),
                datasets: [{
                    label: 'Home Visits',
                    data: @json($homeVisitsLast7Days),
                    borderColor: '#3498db',
                    tension: 0.1
                }, {
                    label: 'Sales Visits',
                    data: @json($salesVisitsLast7Days),
                    borderColor: '#e74c3c',
                    tension: 0.1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });

        // Gráfico de Distribuição de Usuários
        const usersCtx = document.getElementById('usersChart').getContext('2d');
        new Chart(usersCtx, {
            type: 'doughnut',
            data: {
                labels: ['Free Users', 'Paid Users'],
                datasets: [{
                    data: [{{ $freeUsers }}, {{ $payedUsers }}],
                    backgroundColor: ['#95a5a6', '#27ae60']
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false
            }
        });
    </script>
</body>
</html>
