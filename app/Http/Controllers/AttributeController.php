<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Attribute;
use Illuminate\Support\Facades\Validator;

class AttributeController extends Controller
{
    // Lấy toàn bộ danh sách thuộc tính
    public function index()
    {
        $attribute = Attribute::select('id', 'name')->orderBy('id')->get();
        return response()->json($attribute);
    }

    // Lấy chi tiết 1 thuộc tính
    public function show($id)
    {
        $attribute = Attribute::find($id);
        if (!$attribute) {
            return response()->json(['message' => 'Không tìm thấy thuộc tính'], 404);
        }
        return response()->json($attribute);
    }

    // 👉 Thêm thuộc tính mới
    public function store(Request $request)
    {
        // Validate dữ liệu
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255|unique:attribute,name',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'errors' => $validator->errors()
            ], 422);
        }

        // Lưu vào DB
        $attribute = Attribute::create([
            'name' => $request->name,
        ]);

        return response()->json([
            'message' => '✅ Thêm thuộc tính thành công',
            'attribute' => $attribute
        ], 201);
    }
}
