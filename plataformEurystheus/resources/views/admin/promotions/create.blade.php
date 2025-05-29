<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Nova Promoção - Admin Panel</title>
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; margin: 0; padding: 20px; background-color: #f4f7f6; color: #333; }
        .container { max-width: 800px; margin: 0 auto; }
        .header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px; padding-bottom: 20px; border-bottom: 1px solid #ddd; }
        .header h1 { color: #2c3e50; }
        .back-btn { background: #6c757d; color: white; padding: 10px 18px; text-decoration: none; border-radius: 5px; font-size: 0.9em; transition: background-color 0.3s; }
        .back-btn:hover { background: #5a6268; }
        
        .form-container { background: #ffffff; padding: 30px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.05); }
        .form-group { margin-bottom: 20px; }
        .form-group label { display: block; margin-bottom: 5px; font-weight: bold; color: #555; }
        .form-group input, .form-group select, .form-group textarea { width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px; font-size: 0.9em; box-sizing: border-box; }
        .form-group textarea { height: 80px; resize: vertical; }
        .form-group small { color: #666; font-size: 0.8em; margin-top: 5px; display: block; }
        
        .form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }
        .checkbox-group { display: flex; align-items: center; gap: 10px; margin-bottom: 10px; }
        .checkbox-group input[type="checkbox"] { width: auto; }
        
        .price-preview { background: #f8f9fa; padding: 15px; border-radius: 4px; margin-top: 10px; }
        .price-preview .original { text-decoration: line-through; color: #666; }
        .price-preview .discounted { color: #28a745; font-weight: bold; font-size: 1.2em; }
        .price-preview .discount { background: #dc3545; color: white; padding: 2px 6px; border-radius: 4px; font-size: 0.8em; }
        
        .submit-btn { background: #28a745; color: white; padding: 12px 30px; border: none; border-radius: 5px; font-size: 1em; cursor: pointer; transition: background-color 0.3s; }
        .submit-btn:hover { background: #218838; }
        
        .alert { padding: 15px; margin-bottom: 20px; border-radius: 4px; }
        .alert.error { background-color: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Nova Promoção</h1>
            <a href="{{ route('admin.promotions.index') }}" class="back-btn">Voltar para Promoções</a>
        </div>

        @if($errors->any())
            <div class="alert error">
                <ul style="margin: 0; padding-left: 20px;">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="form-container">
            <form method="POST" action="{{ route('admin.promotions.store') }}">
                @csrf
                
                <div class="form-group">
                    <label for="name">Nome da Promoção</label>
                    <input type="text" id="name" name="name" value="{{ old('name') }}" required>
                    <small>Nome interno para identificar a promoção</small>
                </div>
                
                <div class="form-group">
                    <label for="code">Código da Promoção</label>
                    <input type="text" id="code" name="code" value="{{ old('code') }}" required>
                    <small>Código que será exibido para os usuários (ex: PROMO50, LANCAMENTO2024)</small>
                </div>
                
                <div class="form-group">
                    <label for="description">Descrição</label>
                    <textarea id="description" name="description">{{ old('description') }}</textarea>
                    <small>Descrição opcional da promoção</small>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="discount_percentage">Desconto (%)</label>
                        <input type="number" id="discount_percentage" name="discount_percentage" 
                               value="{{ old('discount_percentage', 50) }}" min="0" max="100" step="0.01" required
                               onchange="updatePricePreview()">
                    </div>
                    
                    <div class="form-group">
                        <label for="currency">Moeda</label>
                        <select id="currency" name="currency" onchange="updatePricePreview()">
                            <option value="BRL" {{ old('currency') === 'BRL' ? 'selected' : '' }}>Real Brasileiro (BRL)</option>
                            <option value="USD" {{ old('currency') === 'USD' ? 'selected' : '' }}>Dólar Americano (USD)</option>
                        </select>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="original_price">Preço Original</label>
                    <input type="number" id="original_price" name="original_price" 
                           value="{{ old('original_price', 59) }}" min="0" step="0.01" required
                           onchange="updatePricePreview()">
                    
                    <div class="price-preview" id="price-preview">
                        <div>
                            <span class="original">R$ 59,00</span>
                            <span class="discount">50% OFF</span>
                        </div>
                        <div>
                            <span class="discounted">R$ 29,50</span>
                        </div>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="valid_from">Válida a partir de</label>
                        <input type="datetime-local" id="valid_from" name="valid_from" value="{{ old('valid_from') }}">
                        <small>Deixe em branco para ativar imediatamente</small>
                    </div>
                    
                    <div class="form-group">
                        <label for="valid_until">Válida até</label>
                        <input type="datetime-local" id="valid_until" name="valid_until" value="{{ old('valid_until') }}">
                        <small>Deixe em branco para promoção sem data de expiração</small>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="max_uses">Limite de Uso</label>
                    <input type="number" id="max_uses" name="max_uses" value="{{ old('max_uses') }}" min="1">
                    <small>Deixe em branco para uso ilimitado</small>
                </div>
                
                <div class="form-group">
                    <label>Configurações de Exibição</label>
                    
                    <div class="checkbox-group">
                        <input type="checkbox" id="is_active" name="is_active" value="1" 
                               {{ old('is_active', true) ? 'checked' : '' }}>
                        <label for="is_active">Ativar promoção</label>
                    </div>
                    
                    <div class="checkbox-group">
                        <input type="checkbox" id="show_urgency" name="show_urgency" value="1" 
                               {{ old('show_urgency') ? 'checked' : '' }}>
                        <label for="show_urgency">Exibir banner de urgência</label>
                    </div>
                    
                    <div class="checkbox-group">
                        <input type="checkbox" id="show_floating_banner" name="show_floating_banner" value="1" 
                               {{ old('show_floating_banner') ? 'checked' : '' }}>
                        <label for="show_floating_banner">Exibir banner flutuante</label>
                    </div>
                </div>
                
                <button type="submit" class="submit-btn">Criar Promoção</button>
            </form>
        </div>
    </div>

    <script>
        function updatePricePreview() {
            const originalPrice = parseFloat(document.getElementById('original_price').value) || 0;
            const discountPercentage = parseFloat(document.getElementById('discount_percentage').value) || 0;
            const currency = document.getElementById('currency').value;
            
            const discountAmount = (originalPrice * discountPercentage) / 100;
            const discountedPrice = originalPrice - discountAmount;
            
            const preview = document.getElementById('price-preview');
            
            if (currency === 'BRL') {
                preview.innerHTML = `
                    <div>
                        <span class="original">R$ ${originalPrice.toLocaleString('pt-BR', {minimumFractionDigits: 2})}</span>
                        <span class="discount">${discountPercentage}% OFF</span>
                    </div>
                    <div>
                        <span class="discounted">R$ ${discountedPrice.toLocaleString('pt-BR', {minimumFractionDigits: 2})}</span>
                    </div>
                `;
            } else {
                preview.innerHTML = `
                    <div>
                        <span class="original">$${originalPrice.toFixed(2)}</span>
                        <span class="discount">${discountPercentage}% OFF</span>
                    </div>
                    <div>
                        <span class="discounted">$${discountedPrice.toFixed(2)}</span>
                    </div>
                `;
            }
        }
        
        // Initialize preview
        updatePricePreview();
    </script>
</body>
</html>
