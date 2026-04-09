<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Menu;
use Illuminate\Support\Facades\Auth;
class MenuController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function menuClient()
    {
        $menus = Menu::where('status', 1)->orderBy('sort_order')->get();
        return response()->json($menus);
    }

    public function index()
    {
        $menus = Menu::orderBy('sort_order', 'asc')->get();
        return response()->json($menus);
    }

    /**
     * Lưu menu mới
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'link' => 'required|string|max:255',
            'type' => 'nullable|string',
            'parent_id' => 'nullable|integer',
            'sort_order' => 'nullable|integer',
            'table_id' => 'nullable|integer',
            'status' => 'required|boolean',
        ]);

        $menu = new Menu();
        $menu->name = $request->name;
        $menu->link = $request->link;
        $menu->type = $request->type ?? 'default';
        $menu->parent_id = $request->parent_id ?? 0;
        $menu->sort_order = $request->sort_order ?? 0;
        $menu->table_id = $request->table_id;
        $menu->status = $request->status;
        $menu->created_by = Auth::id() ?? 1;
        $menu->created_at = now();

        $menu->save();

        return response()->json([
            'message' => 'Thêm menu thành công',
            'menu' => $menu,
        ], 201);
    }

    /**
     * Xem chi tiết 1 menu
     */
    public function show($id)
    {
        $menu = Menu::find($id);

        if (!$menu) {
            return response()->json(['message' => 'Không tìm thấy menu'], 404);
        }

        return response()->json($menu);
    }

    /**
     * Cập nhật menu
     */
    public function update(Request $request, $id)
    {
        $menu = Menu::find($id);

        if (!$menu) {
            return response()->json(['message' => 'Không tìm thấy menu'], 404);
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'link' => 'required|string|max:255',
            'type' => 'nullable|string',
            'parent_id' => 'nullable|integer',
            'sort_order' => 'nullable|integer',
            'table_id' => 'nullable|integer',
            'status' => 'required|boolean',
        ]);

        $menu->name = $request->name;
        $menu->link = $request->link;
        $menu->type = $request->type ?? $menu->type;
        $menu->parent_id = $request->parent_id ?? $menu->parent_id;
        $menu->sort_order = $request->sort_order ?? $menu->sort_order;
        $menu->table_id = $request->table_id ?? $menu->table_id;
        $menu->status = $request->status;
        $menu->updated_by = Auth::id() ?? 1;
        $menu->updated_at = now();

        $menu->save();

        return response()->json([
            'message' => 'Cập nhật menu thành công',
            'menu' => $menu,
        ]);
    }

    /**
     * Xóa menu
     */
    public function destroy($id)
    {
        $menu = Menu::find($id);

        if (!$menu) {
            return response()->json(['message' => 'Không tìm thấy menu'], 404);
        }

        $menu->delete();

        return response()->json(['message' => 'Xóa menu thành công']);
    }
}
