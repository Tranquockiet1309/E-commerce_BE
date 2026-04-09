<?php


namespace App\Http\Controllers;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\ProductSale;
use App\Models\ProductImage;
use App\Models\ProductStore;
use App\Models\Attribute;
use Illuminate\Support\Str;
use App\Models\ProductAttribute;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $now = now();
        $query = Product::query();

        // Tìm kiếm theo tên hoặc slug
        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('product.name', 'like', "%{$search}%")
                    ->orWhere('product.slug', 'like', "%{$search}%");
            });
        }
        // Lọc theo trạng thái ẩn/hiện
        if (($status = $request->input('status')) !== null && $status !== '') {
            $query->where('product.status', $status);
        }

        // Lọc theo category
        if ($category_id = $request->input('category_id')) {
            $query->where('product.category_id', $category_id);
        }

        // Join bảng category
        $query->leftJoin('category', 'category.id', '=', 'product.category_id');

        // Lấy tổng số lượng từ kho
        $productStore = ProductStore::query()
            ->select('product_id', DB::raw('SUM(qty) as total_qty'))
            ->groupBy('product_id');

        // Lấy giá khuyến mãi hợp lệ
        $productSale = ProductSale::query()
            ->select('product_id', 'price_sale')
            ->where('date_begin', '<=', $now)
            ->where('date_end', '>', $now);

        // Join với kho và khuyến mãi
        $query->leftJoinSub($productStore, 'ps', function ($j) {
            $j->on('ps.product_id', '=', 'product.id');
        })
            ->leftJoinSub($productSale, 'psale', function ($j) {
                $j->on('psale.product_id', '=', 'product.id');
            })
            ->select(
                'product.id',
                'product.name',
                'category.id as category_id',
                'category.name as category_name', // thêm tên category
                DB::raw("CONCAT('" . url('/images') . "/', product.thumbnail) as thumbnail"),
                'product.price_buy',
                'psale.price_sale',
                DB::raw('COALESCE(ps.total_qty, 0) as quantity'),
                'product.status'
            )
            ->orderBy('product.created_at', 'desc');

        // Phân trang
        $perPage = $request->input('per_page', 100);
        $products = $query->paginate($perPage);

        return response()->json($products);
    }

    /**
     * Hiển thị chi tiết sản phẩm
     */
    public function show($id)
    {
        $now = now();

        // Lấy sản phẩm + load quan hệ ảnh phụ
        $product = Product::with('images')->find($id);
        if (!$product) {
            return response()->json(['message' => 'Không tìm thấy sản phẩm'], 404);
        }

        // Lấy giá khuyến mãi
        $productSale = ProductSale::query()
            ->where('product_id', $id)
            ->where('date_begin', '<=', $now)
            ->where('date_end', '>', $now)
            ->orderBy('price_sale', 'asc')
            ->first();

        $product->price_sale = $productSale ? $productSale->price_sale : null;

        // Lấy danh sách attribute
        $attributes = ProductAttribute::query()
            ->join('attribute', 'attribute.id', '=', 'product_attribute.attribute_id')
            ->where('product_attribute.product_id', $id)
            ->select(
                'attribute.id as attribute_id',
                'attribute.name as attribute_name',
                'product_attribute.value'
            )
            ->get();

        $product->attributes = $attributes;

        // Lấy tổng số lượng trong kho
        $totalQty = ProductStore::where('product_id', $id)->sum('qty');
        $product->quantity = $totalQty;
        // Build URL cho thumbnail
        if ($product->thumbnail) {
            $product->thumbnail = url('images/products/' . $product->thumbnail);
        }

        // Build URL cho ảnh phụ
        foreach ($product->images as $img) {
            $img->image = url('images/product_image/' . $img->image);
        }

        return response()->json($product);
    }


    public function product_all(Request $request)
    {
        $now = now();

        // Tổng số lượng trong kho
        $productStore = ProductStore::query()
            ->select('product_id', DB::raw('SUM(qty) as total_qty'))
            ->groupBy('product_id');

        // Giá khuyến mãi hợp lệ
        $productSale = ProductSale::query()
            ->select('product_id', DB::raw('MIN(price_sale) as price_sale'))
            ->where('date_begin', '<=', $now)
            ->where('date_end', '>', $now)
            ->groupBy('product_id');

        // Gom thuộc tính riêng theo product_id
        $productAttr = DB::table('product_attribute as pa')
            ->join('attribute as a', 'a.id', '=', 'pa.attribute_id')
            ->select(
                'pa.product_id',
                DB::raw("GROUP_CONCAT(CONCAT(a.name, ':', pa.value) SEPARATOR ', ') as attributes")
            )
            ->groupBy('pa.product_id');

        // JOIN tất cả subquery lại
        $products = Product::query()
            ->leftJoinSub($productStore, 'ps', function ($join) {
                $join->on('ps.product_id', '=', 'product.id');
            })
            ->leftJoinSub($productSale, 'psale', function ($join) {
                $join->on('psale.product_id', '=', 'product.id');
            })
            ->leftJoinSub($productAttr, 'pattr', function ($join) {
                $join->on('pattr.product_id', '=', 'product.id');
            })
            ->select(
                'product.id',
                'product.name',
                DB::raw("CONCAT('" . url('/images') . "/', product.thumbnail) as thumbnail"),
                'product.price_buy',
                'psale.price_sale',
                DB::raw('COALESCE(ps.total_qty, 0) as quantity'),
                DB::raw('COALESCE(pattr.attributes, "") as attributes')
            )
            ->orderBy('product.created_at', 'desc')
            ->get();

        return response()->json($products);
    }





    public function getFilters()
    {
        // Lấy tất cả attribute + value duy nhất
        $filters = ProductAttribute::query()
            ->join('attribute', 'attribute.id', '=', 'product_attribute.attribute_id')
            ->select('attribute.name', 'product_attribute.value')
            ->distinct()
            ->get()
            ->groupBy('name') // nhóm theo tên attribute
            ->map(function ($values) {
                return $values->pluck('value'); // lấy mảng value
            });

        return response()->json($filters);
    }


    public function product_by_category($categoryId)
    {
        $now = now();

        // Lấy sản phẩm trong kho
        $productStore = ProductStore::query()
            ->select('product_id', DB::raw('SUM(qty) as total_qty'))
            ->groupBy('product_id');

        // Lấy khuyến mãi hợp lệ
        $productSale = ProductSale::query()
            ->select('product_id', 'price_sale')
            ->where('date_begin', '<=', $now)
            ->where('date_end', '>', $now);

        // Lấy sản phẩm theo category
        $products = Product::query()
            ->where('category_id', $categoryId)
            ->leftJoinSub($productStore, 'ps', function ($j) {
                $j->on('ps.product_id', '=', 'product.id');
            })
            ->leftJoinSub($productSale, 'psale', function ($j) {
                $j->on('psale.product_id', '=', 'product.id');
            })
            ->select(
                'product.id',
                'product.name',
                DB::raw("CONCAT('" . url('/images') . "/', product.thumbnail) as thumbnail"),
                'product.price_buy',
                'psale.price_sale',
                DB::raw('COALESCE(ps.total_qty, 0) as quantity')
            )
            ->orderBy('product.created_at', 'desc')
            ->get();

        return response()->json($products);
    }


    /**
     * Thêm mới sản phẩm
     */


    // public function store(Request $request)
    // {
    //     $validator = Validator::make($request->all(), [
    //         'category_id' => 'required|integer',
    //         'name' => 'required|string|max:255',
    //         'thumbnail' => 'nullable|image',
    //         'images.*' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:5120', // nhiều ảnh
    //         'content' => 'nullable|string',
    //         'description' => 'nullable|string',
    //         'price_buy' => 'required|numeric|min:0',
    //         'status' => 'required|in:0,1',
    //     ]);

    //     if ($validator->fails()) {
    //         return response()->json(['errors' => $validator->errors()], 422);
    //     }

    //     $data = $request->all();

    //     // Tạo slug
    //     $baseSlug = Str::slug($request->name);
    //     $slug = $baseSlug;
    //     $count = 1;
    //     while (Product::where('slug', $slug)->exists()) {
    //         $slug = $baseSlug . '-' . $count++;
    //     }
    //     $data['slug'] = $slug;

    //     // Upload ảnh chính
    //     if ($request->hasFile('thumbnail')) {
    //         $file = $request->file('thumbnail');
    //         $filename = time() . '_' . $file->getClientOriginalName();
    //         $file->move(public_path('images/products'), $filename);
    //         $data['thumbnail'] = $filename;
    //     }

    //     // Lưu sản phẩm
    //     $product = Product::create($data);

    //     // Upload nhiều ảnh phụ
    //     if ($request->hasFile('images')) {
    //         foreach ($request->file('images') as $img) {
    //             $imgName = time() . '_' . $img->getClientOriginalName();
    //             $img->move(public_path('images/product_image'), $imgName);

    //             ProductImage::create([
    //                 'product_id' => $product->id,
    //                 'image' => $imgName,
    //                 'alt' => $request->name . ' image',
    //                 'title' => $request->name
    //             ]);
    //         }
    //     }

    //     // Thêm URL đầy đủ cho thumbnail
    //     if ($product->thumbnail) {
    //         $product->thumbnail = url('images/products/' . $product->thumbnail);
    //     }

    //     return response()->json([
    //         'message' => '✅ Thêm sản phẩm thành công',
    //         'product' => $product->load('images') // load kèm ảnh phụ
    //     ], 201);
    // }
    public function store(Request $request)
    {
        try {
            // Validate dữ liệu đầu vào
            $validator = Validator::make($request->all(), [
                'category_id' => 'required|integer|exists:category,id',
                'name' => 'required|string|max:255',
                'thumbnail' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:5120',
                'images.*' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:5120',
                'content' => 'nullable|string',
                'description' => 'nullable|string',
                'price_buy' => 'required|numeric|min:0',
                'status' => 'required|in:0,1',
                'attribute' => 'nullable|string', // JSON chứa thuộc tính
            ]);

            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }

            // Sinh slug duy nhất
            $baseSlug = Str::slug($request->name);
            $slug = $baseSlug;
            $count = 1;
            while (Product::where('slug', $slug)->exists()) {
                $slug = $baseSlug . '-' . $count++;
            }

            //  Chuẩn bị dữ liệu lưu
            $data = $request->only([
                'category_id',
                'name',
                'content',
                'description',
                'price_buy',
                'status'
            ]);
            $data['slug'] = $slug;

            // Upload ảnh chính
            if ($request->hasFile('thumbnail')) {
                $file = $request->file('thumbnail');
                $filename = time() . '_' . $file->getClientOriginalName();
                $file->move(public_path('images/products'), $filename);
                $data['thumbnail'] = $filename;
            }

            // Lưu sản phẩm
            $product = Product::create($data);

            //Upload nhiều ảnh phụ
            if ($request->hasFile('images')) {
                foreach ($request->file('images') as $img) {
                    $imgName = time() . '_' . $img->getClientOriginalName();
                    $img->move(public_path('images/product_image'), $imgName);

                    ProductImage::create([
                        'product_id' => $product->id,
                        'image' => $imgName,
                        'alt' => $request->name . ' image',
                        'title' => $request->name
                    ]);
                }
            }

            // Lưu thuộc tính sản phẩm (attribute) an toàn & tối ưu
            $attrs = json_decode($request->attribute, true) ?: [];

            if (is_array($attrs) && count($attrs) > 0) {
                // Lấy trước tất cả attribute có sẵn để giảm query
                $names = array_filter(array_column($attrs, 'name'));
                $existingAttrs = Attribute::whereIn('name', $names)->get()->keyBy('name');

                $insertData = [];
                foreach ($attrs as $attr) {
                    if (!isset($attr['name'], $attr['value']) || empty($attr['name']))
                        continue;

                    $attributeId = $attr['id'] ?? ($existingAttrs->get($attr['name'])->id ?? null);

                    if (!$attributeId) {
                        $newAttr = Attribute::create(['name' => $attr['name']]);
                        $attributeId = $newAttr->id;
                    }

                    $insertData[] = [
                        'product_id' => $product->id,
                        'attribute_id' => $attributeId,
                        'value' => $attr['value'],

                    ];
                }

                if (!empty($insertData)) {
                    ProductAttribute::insert($insertData); // Bulk insert tối ưu
                }
            }

            // Load quan hệ để trả về đầy đủ
            $product->load(['images', 'attributes.attribute']);

            // Gắn URL đầy đủ cho ảnh
            if ($product->thumbnail) {
                $product->thumbnail = url('images/products/' . $product->thumbnail);
            }

            foreach ($product->images as $img) {
                $img->image = url('images/product_image/' . $img->image);
            }

            return response()->json([
                'message' => '✅ Thêm sản phẩm thành công',
                'product' => $product,
            ], 201);

        } catch (\Exception $e) {
            \Log::error('Store Product Error', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
            ]);
            return response()->json([
                'message' => '❌ Lỗi khi thêm sản phẩm',
                'error' => $e->getMessage(),
            ], 500);
        }

    }






    /**
     * Cập nhật sản phẩm
     */
    public function update(Request $request, $id)
    {
        $product = Product::find($id);

        if (!$product) {
            return response()->json(['message' => '❌ Không tìm thấy sản phẩm'], 404);
        }

        // ====== VALIDATION ======
        $request->validate([
            'category_id' => 'sometimes|integer',
            'name' => 'sometimes|string|max:255',
            'slug' => 'sometimes|string|max:255|unique:product,slug,' . $id,
            'content' => 'nullable|string',
            'description' => 'nullable|string',
            'price_buy' => 'sometimes|numeric|min:0',
            'status' => 'sometimes|boolean',
            'thumbnail' => 'sometimes|nullable|image|mimes:jpeg,png,jpg,gif,webp|max:5120',
            'images.*' => 'sometimes|image|mimes:jpeg,png,jpg,gif,webp|max:5120',
            'attribute' => 'sometimes|string', // JSON
            'deleted_images' => 'sometimes|string', // JSON array
        ]);

        // ====== CẬP NHẬT THÔNG TIN CƠ BẢN ======
        $data = $request->only([
            'category_id',
            'name',
            'slug',
            'content',
            'description',
            'price_buy',
            'status'
        ]);

        // ====== CẬP NHẬT THUMBNAIL ======
        if ($request->hasFile('thumbnail')) {
            $file = $request->file('thumbnail');
            $filename = time() . '_' . $file->getClientOriginalName();

            // Xóa file thumbnail cũ nếu có
            if ($product->thumbnail && file_exists(public_path('images/products/' . $product->thumbnail))) {
                unlink(public_path('images/products/' . $product->thumbnail));
            }

            $file->move(public_path('images/products'), $filename);
            $data['thumbnail'] = $filename;
        }

        $product->update($data);

        // ====== XÓA ẢNH PHỤ ======
        if ($request->has('deleted_images')) {
            $ids = json_decode($request->deleted_images, true);

            // Nếu là mảng rỗng, xóa hết ảnh phụ
            if (is_array($ids)) {
                if (count($ids) === 0) {
                    foreach ($product->images as $img) {
                        $path = public_path('images/product_image/' . $img->image);
                        if (file_exists($path))
                            unlink($path);
                        $img->delete();
                    }
                } else {
                    foreach ($ids as $idImg) {
                        $img = $product->images()->find($idImg);
                        if ($img) {
                            $path = public_path('images/product_image/' . $img->image);
                            if (file_exists($path))
                                unlink($path);
                            $img->delete();
                        }
                    }
                }
            }
        }

        // ====== THÊM ẢNH PHỤ MỚI ======
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $file) {
                $filename = time() . '_' . $file->getClientOriginalName();
                $file->move(public_path('images/product_image'), $filename);

                $product->images()->create([
                    'image' => $filename,
                    'alt' => $product->name,
                ]);
            }
        }

        // ====== CẬP NHẬT ATTRIBUTE ======
        if ($request->has('attribute')) {
            $attrs = json_decode($request->attribute, true);

            // Nếu là mảng rỗng → xóa toàn bộ thuộc tính
            if (is_array($attrs)) {
                $product->attributes()->delete();

                // Nếu có giá trị mới → thêm lại
                if (count($attrs) > 0) {
                    foreach ($attrs as $attr) {
                        $product->attributes()->create([
                            'attribute_id' => $attr['id'] ?? null,
                            'value' => $attr['value'] ?? '',
                        ]);
                    }
                }
            }
        }

        // ====== TRẢ KẾT QUẢ ======
        return response()->json([
            'message' => '✅ Cập nhật sản phẩm thành công',
            'product' => $product->load('images', 'attributes')
        ]);
    }





    /**
     * Xoá sản phẩm
     */
    public function destroy($id)
    {
        $product = Product::find($id);

        if (!$product) {
            return response()->json(['message' => '❌ Không tìm thấy sản phẩm'], 404);
        }

        // Xóa ảnh phụ trên ổ cứng + record
        foreach ($product->images as $img) {
            $path = public_path('images/product_image/' . $img->image);
            if (file_exists($path)) {
                unlink($path);
            }
            $img->delete();
        }

        // Xóa attributes
        $product->attributes()->delete();

        // Xóa product_sale liên quan
        $product->sales()->delete(); // giả sử relationship tên là "sales"
        //xóa kho hàng
        $product->stores()->delete(); // Xóa các bản ghi tồn kho của sản phẩm

        // Xóa product
        $product->delete();

        return response()->json(['message' => '🗑️ Xoá sản phẩm thành công']);
    }


    public function product_new(Request $request)
    {
        $limit = ($request->limit) ? $request->limit : 100;
        $now = now();
        // Lấy ra tất cả sản phẩm có trong kho
        $productStore = ProductStore::query()
            ->select('product_id', DB::raw('SUM(qty) as total_qty'))
            ->groupBy('product_id');

        // Tìm tất các các khuyến mãi hợp lệ
        $productSale = ProductSale::query()
            ->select('product_id', 'price_sale')
            ->where('date_begin', '<=', $now)
            ->where('date_end', '>', $now);

        //Lấy thông tin sản phẩm
        $products = Product::query()
            ->joinSub($productStore, 'ps', function ($j) {
                $j->on('ps.product_id', '=', 'product.id')
                    ->where('ps.total_qty', '>', 0);
            })
            ->leftJoinSub($productSale, 'psale', function ($j) {
                $j->on('psale.product_id', '=', 'product.id');
            })
            ->select('product.id', 'ps.total_qty as quantity', 'product.name', 'product.thumbnail', 'product.price_buy', 'psale.price_sale')
            ->orderBy('product.created_at', 'desc')
            ->limit($limit)
            ->get();

        return response()->json($products);
    }

}
