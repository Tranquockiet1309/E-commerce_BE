<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Banner;
use Illuminate\Support\Facades\Storage;

class BannerController extends Controller
{
    /**
     * Lấy tất cả banner (status = 1)
     */
    public function index()
    {
        $banners = Banner::orderBy('sort_order', 'asc')
            ->get(['id', 'name as title', 'description as subtitle', 'image as src', 'link', 'status']);

        // Thêm URL đầy đủ cho ảnh nếu tồn tại
        foreach ($banners as $banner) {
            if (!empty($banner->src)) {
                $banner->src = url('images/banner/' . $banner->src);
            }
        }

        return response()->json($banners);
    }


    public function indexClient()
    {
        $banners = Banner::Where('status', 1)
            ->orderBy('sort_order', 'asc')
            ->get(['id', 'name as title', 'description as subtitle', 'image as src', 'link']);

        // Thêm URL đầy đủ cho ảnh nếu tồn tại
        foreach ($banners as $banner) {
            if (!empty($banner->src)) {
                $banner->src = url('images/banner/' . $banner->src);
            }
        }

        return response()->json($banners);
    }
    /**
     * Lấy chi tiết banner theo ID
     */
    public function show($id)
    {
        $banner = Banner::find($id);
        if (!$banner) {
            return response()->json(['message' => 'Banner không tồn tại'], 404);
        }

        if (!empty($banner->image)) {
            $banner->image = url('images/banner/' . $banner->image);
        }

        // Map dữ liệu giống API mẫu
        $bannerData = [
            'id' => $banner->id,
            'title' => $banner->name,
            'subtitle' => $banner->description,
            'src' => $banner->image,
            'link' => $banner->link,
        ];

        return response()->json($bannerData);
    }

    /**
     * Thêm banner mới
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'link' => 'nullable|url',
            'status' => 'nullable|boolean',
            'sort_order' => 'nullable|integer',
            'image' => 'nullable|image|mimes:jpg,jpeg,png,gif,webp|max:5120',
        ]);

        $banner = new Banner();
        $banner->name = $request->name;
        $banner->description = $request->description ?? '';
        $banner->link = $request->link ?? '';
        $banner->status = $request->status ?? 1;
        $banner->sort_order = $request->sort_order ?? 1;

        if ($request->hasFile('image')) {
            $file = $request->file('image');
            $filename = time() . '_' . $file->getClientOriginalName();
            $file->move(public_path('images/banner'), $filename);
            $banner->image = $filename;
        }

        $banner->save();

        // Trả về dữ liệu giống API mẫu
        $bannerData = [
            'id' => $banner->id,
            'title' => $banner->name,
            'subtitle' => $banner->description,
            'src' => !empty($banner->image) ? url('images/banner/' . $banner->image) : null,
            'link' => $banner->link,
        ];

        return response()->json($bannerData, 201);
    }

    /**
     * Cập nhật banner
     */
    public function update(Request $request, $id)
    {
        $banner = Banner::find($id);
        if (!$banner) {
            return response()->json(['message' => 'Banner không tồn tại'], 404);
        }

        $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'description' => 'sometimes|nullable|string',
            'link' => 'sometimes|nullable|url',
            'status' => 'sometimes|boolean',
            'sort_order' => 'sometimes|integer',
            'image' => 'sometimes|nullable|image|mimes:jpg,jpeg,png,gif,webp|max:5120',
        ]);

        if ($request->filled('name'))
            $banner->name = $request->name;
        if ($request->filled('description'))
            $banner->description = $request->description;
        if ($request->filled('link'))
            $banner->link = $request->link;
        if ($request->has('status'))
            $banner->status = $request->status;
        if ($request->has('sort_order'))
            $banner->sort_order = $request->sort_order;

        // Upload ảnh mới
        if ($request->hasFile('image')) {
            // Xóa file cũ nếu có
            if ($banner->image && file_exists(public_path('images/banner/' . $banner->image))) {
                unlink(public_path('images/banner/' . $banner->image));
            }

            $file = $request->file('image');
            $filename = time() . '_' . $file->getClientOriginalName();
            $file->move(public_path('images/banner'), $filename);
            $banner->image = $filename;
        }

        $banner->save();

        $bannerData = [
            'id' => $banner->id,
            'title' => $banner->name,
            'subtitle' => $banner->description,
            'src' => !empty($banner->image) ? url('images/banner/' . $banner->image) : null,
            'link' => $banner->link,
        ];

        return response()->json($bannerData);
    }

    /**
     * Xóa banner
     */
    public function destroy($id)
    {
        $banner = Banner::find($id);
        if (!$banner) {
            return response()->json(['message' => 'Banner không tồn tại'], 404);
        }

        // Xóa ảnh nếu tồn tại
        if ($banner->image && file_exists(public_path('images/banner/' . $banner->image))) {
            unlink(public_path('images/banner/' . $banner->image));
        }

        $banner->delete();

        return response()->json(['message' => 'Xóa banner thành công']);
    }
}
