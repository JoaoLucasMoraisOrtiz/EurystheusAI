<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Gerenciar Promoções - Admin Panel</title>
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; margin: 0; padding: 20px; background-color: #f4f7f6; color: #333; }
        .container { max-width: 1400px; margin: 0 auto; }
        .header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px; padding-bottom: 20px; border-bottom: 1px solid #ddd; }
        .header h1 { color: #2c3e50; }
        .back-btn, .create-btn { padding: 10px 18px; text-decoration: none; border-radius: 5px; font-size: 0.9em; transition: all 0.3s; }
        .back-btn { background: #6c757d; color: white; }
        .back-btn:hover { background: #5a6268; }
        .create-btn { background: #28a745; color: white; margin-left: 10px; }
        .create-btn:hover { background: #218838; }
        
        .promotion-card { background: #ffffff; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.05); margin-bottom: 20px; overflow: hidden; }
        .promotion-header { padding: 20px; border-bottom: 1px solid #eee; display: flex; justify-content: space-between; align-items: center; }
        .promotion-header h3 { margin: 0; color: #2c3e50; }
        .promotion-status { padding: 5px 12px; border-radius: 20px; font-size: 0.8em; font-weight: bold; text-transform: uppercase; }
        .promotion-status.active { background: #28a745; color: white; }
        .promotion-status.inactive { background: #dc3545; color: white; }
        
        .promotion-details { padding: 20px; display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; }
        .detail-item { }
        .detail-label { font-weight: bold; color: #666; font-size: 0.9em; margin-bottom: 5px; }
        .detail-value { color: #2c3e50; }
        
        .promotion-actions { padding: 20px; border-top: 1px solid #eee; display: flex; gap: 10px; }
        .btn { padding: 8px 16px; border: none; border-radius: 4px; text-decoration: none; font-size: 0.9em; cursor: pointer; transition: all 0.3s; }
        .btn-edit { background: #007bff; color: white; }
        .btn-edit:hover { background: #0056b3; }
        .btn-toggle { background: #ffc107; color: #212529; }
        .btn-toggle:hover { background: #e0a800; }
        .btn-delete { background: #dc3545; color: white; }
        .btn-delete:hover { background: #c82333; }
        
        .promo-code { background: #f8f9fa; padding: 8px 12px; border-radius: 4px; font-family: monospace; font-weight: bold; display: inline-block; }
        .price-display { display: flex; align-items: center; gap: 10px; }
        .original-price { text-decoration: line-through; color: #666; }
        .discounted-price { color: #28a745; font-weight: bold; font-size: 1.2em; }
        .discount-badge { background: #dc3545; color: white; padding: 2px 6px; border-radius: 4px; font-size: 0.8em; }
        
        .alert { padding: 15px; margin-bottom: 20px; border-radius: 4px; }
        .alert.success { background-color: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .alert.error { background-color: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
        
        .empty-state { text-align: center; padding: 60px 20px; color: #666; }
        .empty-state h3 { margin-bottom: 10px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Gerenciar Promoções</h1>
            <div>
                <a href="{{ route('admin.index') }}" class="back-btn">Voltar ao Admin</a>
                <a href="{{ route('admin.promotions.create') }}" class="create-btn">Nova Promoção</a>
            </div>
        </div>

        @if(session('success'))
            <div class="alert success">{{ session('success') }}</div>
        @endif
        
        @if(session('error'))
            <div class="alert error">{{ session('error') }}</div>
        @endif

        @if($promotions->count() > 0)
            @foreach($promotions as $promotion)
                <div class="promotion-card">
                    <div class="promotion-header">
                        <h3>{{ $promotion->name }}</h3>
                        <span class="promotion-status {{ $promotion->is_active ? 'active' : 'inactive' }}">
                            {{ $promotion->is_active ? 'Ativa' : 'Inativa' }}
                        </span>
                    </div>
                    
                    <div class="promotion-details">
                        <div class="detail-item">
                            <div class="detail-label">Código da Promoção</div>
                            <div class="detail-value">
                                <span class="promo-code">{{ $promotion->code }}</span>
                            </div>
                        </div>
                        
                        <div class="detail-item">
                            <div class="detail-label">Desconto</div>
                            <div class="detail-value">
                                <span class="discount-badge">{{ $promotion->discount_percentage }}% OFF</span>
                            </div>
                        </div>
                        
                        <div class="detail-item">
                            <div class="detail-label">Preço</div>
                            <div class="detail-value">
                                <div class="price-display">
                                    <span class="original-price">{{ $promotion->formatted_original_price }}</span>
                                    <span class="discounted-price">{{ $promotion->formatted_discounted_price }}</span>
                                </div>
                            </div>
                        </div>
                        
                        <div class="detail-item">
                            <div class="detail-label">Validade</div>
                            <div class="detail-value">
                                @if($promotion->valid_from && $promotion->valid_until)
                                    {{ $promotion->valid_from->format('d/m/Y H:i') }} - {{ $promotion->valid_until->format('d/m/Y H:i') }}
                                @elseif($promotion->valid_until)
                                    Até {{ $promotion->valid_until->format('d/m/Y H:i') }}
                                @else
                                    Sem data de expiração
                                @endif
                            </div>
                        </div>
                        
                        <div class="detail-item">
                            <div class="detail-label">Uso</div>
                            <div class="detail-value">
                                {{ $promotion->current_uses }}{{ $promotion->max_uses ? ' / ' . $promotion->max_uses : ' (ilimitado)' }}
                            </div>
                        </div>
                        
                        <div class="detail-item">
                            <div class="detail-label">Configurações</div>
                            <div class="detail-value">
                                @if($promotion->show_urgency)
                                    <span style="color: #dc3545;">🔥 Banner de Urgência</span><br>
                                @endif
                                @if($promotion->show_floating_banner)
                                    <span style="color: #007bff;">💎 Banner Flutuante</span>
                                @endif
                                @if(!$promotion->show_urgency && !$promotion->show_floating_banner)
                                    <span style="color: #666;">Apenas banner principal</span>
                                @endif
                            </div>
                        </div>
                    </div>
                    
                    @if($promotion->description)
                        <div style="padding: 0 20px;">
                            <div class="detail-label">Descrição</div>
                            <div class="detail-value">{{ $promotion->description }}</div>
                        </div>
                    @endif
                    
                    <div class="promotion-actions">
                        <a href="{{ route('admin.promotions.edit', $promotion) }}" class="btn btn-edit">Editar</a>
                        
                        <form method="POST" action="{{ route('admin.promotions.toggle', $promotion) }}" style="display: inline;">
                            @csrf
                            @method('PATCH')
                            <button type="submit" class="btn btn-toggle">
                                {{ $promotion->is_active ? 'Desativar' : 'Ativar' }}
                            </button>
                        </form>
                        
                        <form method="POST" action="{{ route('admin.promotions.destroy', $promotion) }}" 
                              style="display: inline;" 
                              onsubmit="return confirm('Tem certeza que deseja excluir esta promoção?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-delete">Excluir</button>
                        </form>
                    </div>
                </div>
            @endforeach
            
            <div style="margin-top: 30px;">
                {{ $promotions->links() }}
            </div>
        @else
            <div class="empty-state">
                <h3>Nenhuma promoção encontrada</h3>
                <p>Crie sua primeira promoção para começar a impulsionar as vendas!</p>
                <a href="{{ route('admin.promotions.create') }}" class="create-btn">Criar Primeira Promoção</a>
            </div>
        @endif
    </div>
</body>
</html>
