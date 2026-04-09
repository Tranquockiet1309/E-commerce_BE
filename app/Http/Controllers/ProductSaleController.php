<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\ProductSale;
use Illuminate\Support\Facades\DB;

class ProductSaleController extends Controller
{
    /**
     * Lấy danh sách sản phẩm kèm thông tin khuyến mãi
     */
    public function index()
    {
        $now = now();

        // Subquery tính tổng số lượng trong kho
        $productStore = \App\Models\ProductStore::query()
            ->select('product_id', DB::raw('SUM(qty) as total_qty'))
            ->groupBy('product_id');

        $products = Product::query()
            ->join('product_sale as psale', 'psale.product_id', '=', 'product.id')
            ->leftJoinSub($productStore, 'ps', function ($join) {
                $join->on('ps.product_id', '=', 'product.id');
            })
            ->select(
                'psale.id as sale_id',
                'product.id as product_id',
                'psale.name as name',
                'product.name as product_name',
                DB::raw("CONCAT('" . url('/images') . "/', product.thumbnail) as thumbnail"),
                'product.price_buy',
                'psale.price_sale',
                DB::raw('COALESCE(ps.total_qty, 0) as quantity'), // tổng số lượng
                'psale.date_begin',
                'psale.date_end',
                'psale.status'
            )
            ->orderBy('product.created_at', 'desc')
            ->get();

        return response()->json($products);
    }
    public function indexClient()
    {
        $now = now();

        // Subquery tính tổng số lượng trong kho
        $productStore = \App\Models\ProductStore::query()
            ->select('product_id', DB::raw('SUM(qty) as total_qty'))
            ->groupBy('product_id');

        $products = Product::query()
            ->join('product_sale as psale', 'psale.product_id', '=', 'product.id')
            ->leftJoinSub($productStore, 'ps', function ($join) {
                $join->on('ps.product_id', '=', 'product.id');
            })
            ->where('psale.status', 1) // chỉ lấy khuyến mãi active
            ->whereDate('psale.date_begin', '<=', $now)
            ->whereDate('psale.date_end', '>=', $now) // còn hạn
            ->select(
                'psale.id as sale_id',
                'product.id as product_id',
                'psale.name as name',
                'product.name as product_name',
                DB::raw("CONCAT('" . url('/images') . "/', product.thumbnail) as thumbnail"),
                'product.price_buy',
                'psale.price_sale',
                DB::raw('COALESCE(ps.total_qty, 0) as quantity'),
                'psale.date_begin',
                'psale.date_end',
                'psale.status'
            )
            ->orderBy('psale.date_end', 'asc') // có thể sắp xếp theo ngày kết thúc
            ->get();

        return response()->json($products);
    }





    /**
     * Thêm mới khuyến mãi cho sản phẩm
     */
    public function store(Request $request)
    {
        // Validate dữ liệu từ form
        $request->validate([
            'name' => 'required|string|max:255',
            'product_id' => 'required|exists:product,id',
            'price_sale' => 'required|numeric',
            'date_begin' => 'required|date',
            'date_end' => 'required|date|after_or_equal:date_begin',
        ]);

        // Lấy thông tin sản phẩm
        $product = Product::findOrFail($request->product_id);

        // Tạo khuyến mãi mới
        $sale = ProductSale::create([
            'name' => $request->name,           // do admin nhập
            'product_id' => $product->id,
            'price_buy' => $product->price_buy, // tự lấy từ sản phẩm
            'price_sale' => $request->price_sale,
            'date_begin' => $request->date_begin,
            'date_end' => $request->date_end,
            'created_at' => now(),
            'created_by' => auth()->id() ?? 1,
            'status' => 1,
        ]);

        return response()->json([
            'message' => '✅ Thêm khuyến mãi thành công',
            'data' => $sale,
        ], 201);
    }




    /**
     * Hiển thị chi tiết 1 sản phẩm và khuyến mãi của nó
     */
    public function show(string $id)
    {
        $sale = ProductSale::with(['product', 'product.images', 'product.attributes'])->findOrFail($id);
        $product = $sale->product;

        return response()->json([
            'id' => $sale->id,
            'name' => $sale->name,                  // tên khuyến mãi
            'product_id' => $product->id,
            'product_name' => $product->name,
            'thumbnail' => url('/images/' . $product->thumbnail),
            'price_buy' => $product->price_buy,
            'price_sale' => $sale->price_sale,
            'description' => $product->description,
            'images' => $product->images->map(fn($img) => url('/images/' . $img->path)),
            'attributes' => $product->attributes->map(fn($attr) => [
                'id' => $attr->id,
                'attribute_name' => $attr->attribute_name,
                'value' => $attr->value,
                'code' => $attr->code ?? null,
                'extra_price' => $attr->extra_price ?? 0,
            ]),
            'date_begin' => $sale->date_begin,
            'date_end' => $sale->date_end,
            'status' => $sale->status,
        ]);
    }





    /**
     * Cập nhật khuyến mãi
     */
    public function update(Request $request, string $id)
    {
        $sale = ProductSale::findOrFail($id);
        $sale->update($request->all());
        return response()->json($sale);
    }

    /**
     * Xóa khuyến mãi
     */
    public function destroy(string $id)
    {
        $sale = ProductSale::findOrFail($id);
        $sale->delete();
        return response()->json(null, 204);
    }
}
