<!-- filepath: /home/joao/Documentos/EurystheusAI/plataformEurystheus/resources/views/free/dashboard.blade.php -->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Free Dashboard - Novos Agentes</title>
</head>
<body>
    <h1>Criação de Novo Agente (Free)</h1>
    
    <form id="agentForm" method="POST" action="{{ route('free.dashboard.prompt') }}">
        @csrf
        <label>Objetivo Principal:</label>
        <input type="text" name="scope[objective]" required>
        <br/><br/>
        
        <label>Restrições:</label>
        <input type="text" name="scope[constraints]" required>
        <br/><br/>
        
        <label>Dados Disponíveis:</label>
        <input type="text" name="scope[data]" required>
        <br/><br/>
        
        <label>Público-Alvo:</label>
        <input type="text" name="scope[audience]" required>
        <br/><br/>
        
        <label>Formato de Resposta:</label>
        <input type="text" name="scope[output_format]" required>
        <br/><br/>
        
        <label>Prazos / Métricas:</label>
        <input type="text" name="scope[deadlines]" required>
        <br/><br/>
        
        <button type="submit">Gerar Prompt</button>
    </form>
    
    <hr/>
    
    <h2>Últimos 5 Prompts</h2>
    <ul>
        @foreach($recentPrompts as $prompt)
            <li>{{ $prompt->content }}</li>
        @endforeach
    </ul>

    <div>
        <h2>Informações do Usuário</h2>
        <p>{{ Auth::user()->name }}</p>
        <p>{{ Auth::user()->email }}</p>
        @if (Auth::user()->isFree())
            <p>Free User</p>
        @endif
    </div>
</body>
</html>