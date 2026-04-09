<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ProductStore;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\ProductStoreImport;

class ProductStoreController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $productStores = DB::table('product_store')
            ->join('product', 'product_store.product_id', '=', 'product.id')
            ->select(
                'product_store.*',
                'product.name as product_name'
            )
            ->get();

        return response()->json([
            'success' => true,
            'data' => $productStores
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|integer|exists:product,id',
            'items.*.price_root' => 'required|numeric|min:0',
            'items.*.qty' => 'required|integer|min:0',
            'items.*.status' => 'required|boolean',
        ]);

        $createdStores = [];
        foreach ($request->items as $item) {
            $createdStores[] = ProductStore::create([
                'product_id' => $item['product_id'],
                'price_root' => $item['price_root'],
                'qty' => $item['qty'],
                'status' => $item['status'],
                'created_by' => auth()->id() ?? 1
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Product store(s) created successfully',
            'data' => $createdStores
        ], 201);

    }

    public function import(Request $request)
    {
        if (!$request->hasFile('file')) {
            return response()->json(['message' => 'Không có file tải lên'], 400);
        }

        $file = $request->file('file');
        $ext = strtolower($file->getClientOriginalExtension());
        if (!in_array($ext, ['xlsx', 'csv'])) {
            return response()->json(['message' => 'Sai định dạng file (chỉ chấp nhận XLSX/CSV)'], 400);
        }

        try {
            Excel::import(new ProductStoreImport, $file);

            return response()->json(['message' => '✅ Import thành công'], 200);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'message' => '❌ Lỗi dữ liệu',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json(['message' => '❌ Lỗi: ' . $e->getMessage()], 500);
        }
    }



    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $productStore = ProductStore::with('product')->find($id);

        if (!$productStore) {
            return response()->json([
                'success' => false,
                'message' => 'Product store not found'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $productStore
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $productStore = ProductStore::find($id);

        if (!$productStore) {
            return response()->json([
                'success' => false,
                'message' => 'Product store not found'
            ], 404);
        }

        // Validate dữ liệu (có thể chỉ update các trường gửi lên)
        $request->validate([
            'price_root' => 'nullable|numeric|min:0',
            'qty' => 'nullable|integer|min:0',
            'status' => 'nullable|boolean'
        ]);

        $productStore->update(array_merge(
            $request->only(['price_root', 'qty', 'status']),
            ['updated_by' => auth()->id() ?? 1]
        ));

        return response()->json([
            'success' => true,
            'message' => 'Product store updated successfully',
            'data' => $productStore
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $productStore = ProductStore::find($id);

        if (!$productStore) {
            return response()->json([
                'success' => false,
                'message' => 'Product store not found'
            ], 404);
        }

        $productStore->delete();

        return response()->json([
            'success' => true,
            'message' => 'Product store deleted successfully'
        ]);
    }
}
