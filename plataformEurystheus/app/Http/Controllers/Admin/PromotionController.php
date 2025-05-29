<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Promotion;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class PromotionController extends Controller
{
    public function index()
    {
        $promotions = Promotion::orderBy('created_at', 'desc')->paginate(10);
        return view('admin.promotions.index', compact('promotions'));
    }

    public function create()
    {
        return view('admin.promotions.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:promotions,code',
            'description' => 'nullable|string',
            'discount_percentage' => 'required|numeric|min:0|max:100',
            'original_price' => 'required|numeric|min:0',
            'currency' => 'required|in:BRL,USD',
            'is_active' => 'boolean',
            'show_urgency' => 'boolean',
            'show_floating_banner' => 'boolean',
            'valid_from' => 'nullable|date',
            'valid_until' => 'nullable|date|after:valid_from',
            'max_uses' => 'nullable|integer|min:1',
        ]);

        // Calculate discounted price
        $discountAmount = ($validated['original_price'] * $validated['discount_percentage']) / 100;
        $validated['discounted_price'] = $validated['original_price'] - $discountAmount;

        // Generate code if not provided
        if (empty($validated['code'])) {
            $validated['code'] = 'PROMO' . strtoupper(Str::random(6));
        }

        Promotion::create($validated);

        return redirect()->route('admin.promotions.index')
            ->with('success', 'Promoção criada com sucesso!');
    }

    public function show(Promotion $promotion)
    {
        return view('admin.promotions.show', compact('promotion'));
    }

    public function edit(Promotion $promotion)
    {
        return view('admin.promotions.edit', compact('promotion'));
    }

    public function update(Request $request, Promotion $promotion)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:promotions,code,' . $promotion->id,
            'description' => 'nullable|string',
            'discount_percentage' => 'required|numeric|min:0|max:100',
            'original_price' => 'required|numeric|min:0',
            'currency' => 'required|in:BRL,USD',
            'is_active' => 'boolean',
            'show_urgency' => 'boolean',
            'show_floating_banner' => 'boolean',
            'valid_from' => 'nullable|date',
            'valid_until' => 'nullable|date|after:valid_from',
            'max_uses' => 'nullable|integer|min:1',
        ]);

        // Calculate discounted price
        $discountAmount = ($validated['original_price'] * $validated['discount_percentage']) / 100;
        $validated['discounted_price'] = $validated['original_price'] - $discountAmount;

        $promotion->update($validated);

        return redirect()->route('admin.promotions.index')
            ->with('success', 'Promoção atualizada com sucesso!');
    }

    public function destroy(Promotion $promotion)
    {
        $promotion->delete();

        return redirect()->route('admin.promotions.index')
            ->with('success', 'Promoção excluída com sucesso!');
    }

    public function toggle(Promotion $promotion)
    {
        $promotion->update(['is_active' => !$promotion->is_active]);

        $status = $promotion->is_active ? 'ativada' : 'desativada';
        return redirect()->back()
            ->with('success', "Promoção {$status} com sucesso!");
    }
}
