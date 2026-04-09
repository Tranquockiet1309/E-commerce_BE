<?php

namespace App\Imports;

use App\Models\Product;
use App\Models\ProductStore;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Illuminate\Validation\ValidationException;

class ProductStoreImport implements ToModel, WithHeadingRow
{
    public function model(array $row)
    {
        // Bỏ qua dòng trống hoặc thiếu product_id
        if (empty($row['product_id'])) {
            return null;
        }

        // Kiểm tra product_id có tồn tại trong bảng products
        $productExists = Product::where('id', $row['product_id'])->exists();

        if (!$productExists) {
            // Ném lỗi (ngừng import) nếu không tồn tại
            throw ValidationException::withMessages([
                'product_id' => "❌ Mã sản phẩm {$row['product_id']} không tồn tại trong bảng products.",
            ]);
        }

        // Nếu product_id hợp lệ → tiến hành import
        return new ProductStore([
            'product_id' => $row['product_id'],
            'qty' => $row['qty'] ?? 0,
            'price_root' => $row['price_root'] ?? 0,
            'status' => $row['status'] ?? 1,
            'created_at' => now(),
            'created_by' => 1,
        ]);
    }
}
