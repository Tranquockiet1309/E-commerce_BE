<?php

namespace App\Http\Controllers;

use App\Models\ProductAttribute;
use Illuminate\Http\Request;

class ProductAttributeController extends Controller
{
    /**
     * Hiển thị tất cả ProductAttribute chỉ với attribute
     */
    public function index()
    {
        // Lấy tất cả ProductAttribute kèm quan hệ attribute
        $attributes = ProductAttribute::with('attribute')->get();

        return response()->json([
            'data' => $attributes,
        ]);
    }
}
