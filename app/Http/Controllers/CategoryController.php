<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Category;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class CategoryController extends Controller
{
    /**
     * Lấy tất cả danh mục
     */
    public function index()
    {
        $categories = Category::all();
        return response()->json($categories);
    }
    public function indexClient()
    {
        $categories = Category::Where('status', 1)
            ->get();
        return response()->json($categories);
    }
    /**
     * Thêm mới danh mục
     */
    public function store(Request $request)
    {
        // dd($request->all(), $request->file('image'));

        // Generate slug trước validate
        $request->merge([
            'slug' => $request->slug ?: Str::slug($request->name),
            'parent_id' => $request->parent_id == 0 ? null : $request->parent_id
        ]);

        $request->validate([
            'name' => 'required|string|max:255|unique:category,name',
            'slug' => 'required|string|max:255|unique:category,slug',
            'parent_id' => 'nullable|integer',
            'sort_order' => 'nullable|integer',
            'description' => 'nullable|string',
            'status' => 'nullable|boolean',
            'image' => 'nullable|image',


        ]);

        $category = new Category();
        $category->name = $request->name;
        $category->slug = $request->slug ?: Str::slug($request->name);
        $category->parent_id = $request->parent_id ?? 0;
        $category->sort_order = $request->sort_order ?? 1;
        $category->description = $request->description ?? '';
        $category->status = $request->status ?? 1;
        // Upload ảnh
        if ($request->hasFile('image')) {
            $file = $request->file('image');
            $filename = time() . '_' . $file->getClientOriginalName();
            $destination = public_path('images/category');
            $file->move($destination, $filename);
            $category->image = $filename;   // ✅ phải gán vào model
        }

        $category->save();

        // Thêm URL đầy đủ cho ảnh khi trả về JSON
        if ($category->image) {
            $category->image = url('images/category/' . $category->image);
        }

        return response()->json([
            'message' => '✅ Thêm danh mục thành công',
            'data' => $category
        ], 201);
    }

    /**
     * Xem chi tiết danh mục
     */
    public function show($id)
    {
        $category = Category::find($id);
        if (!$category) {
            return response()->json(['message' => 'Danh mục không tồn tại'], 404);
        }
        return response()->json($category);
    }

    /**
     * Cập nhật danh mục
     */
    public function update(Request $request, $id)
    {
        echo "Update category ID: " . $id;
        $category = Category::find($id);

        if (!$category) {
            return response()->json(['message' => '❌ Danh mục không tồn tại'], 404);
        }

        try {
            // Validate dữ liệu
            $request->validate([
                'name' => 'sometimes|required|string|max:255|unique:category,name,' . $id,
                'slug' => 'sometimes|nullable|string|max:255|unique:category,slug,' . $id,
                'parent_id' => 'sometimes|nullable|integer',
                'sort_order' => 'sometimes|nullable|integer',
                'description' => 'sometimes|nullable|string',
                'status' => 'sometimes|boolean',
                'image' => 'sometimes|nullable|image|mimes:jpeg,png,jpg,gif,webp|max:5120',
            ]);

            $data = $request->only([
                'name',
                'slug',
                'parent_id',
                'sort_order',
                'status',
                'description',
                'image'
            ]);

            // Nếu slug rỗng, tạo tự động
            if (empty($data['slug']) && !empty($data['name'])) {
                $data['slug'] = Str::slug($data['name']);
            }

            // Xử lý file image
            if ($request->hasFile('image')) {
                $file = $request->file('image');
                $filename = time() . '_' . $file->getClientOriginalName();

                // Xóa file cũ
                if ($category->image && file_exists(public_path('images/category/' . $category->image))) {
                    unlink(public_path('images/category/' . $category->image));
                }

                $file->move(public_path('images/category'), $filename);
                $data['image'] = $filename;
            }

            // Cập nhật description (hoặc giữ nguyên nếu không gửi)
            $data['description'] = $request->input('description', $category->description);

            $category->update($data);
            // Thêm URL đầy đủ cho ảnh khi trả về JSON
            return response()->json([
                'message' => '✏️ Cập nhật danh muc thành công',
                'category' => $category
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'message' => '❌ Lỗi xác thực dữ liệu',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'message' => '❌ Lỗi server',
                'error' => $e->getMessage(),
            ], 500);
        }
    }






    /**
     * Xóa danh mục
     */
    public function destroy($id)
    {
        $category = Category::find($id);
        if (!$category) {
            return response()->json(['message' => 'Danh mục không tồn tại'], 404);
        }

        // Xóa ảnh nếu tồn tại
        if ($category->image && Storage::disk('public')->exists($category->image)) {
            Storage::disk('public')->delete($category->image);
        }

        $category->delete();

        return response()->json(['message' => '🗑 Xóa danh mục thành công']);
    }

    /**
     * Lấy danh mục mới (giống product_new)
     */
    public function category_new(Request $request)
    {
        $limit = $request->limit ?? 100;
        $categories = Category::orderBy('created_at', 'desc')->limit($limit)->get();
        return response()->json($categories);
    }
}
