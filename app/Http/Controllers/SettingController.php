<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Setting;

class SettingController extends Controller
{
    /**
     * Lấy tất cả setting (thường chỉ có 1 bản ghi)
     */
    public function indexClient()
    {
        $setting = Setting::where('status', 1)->first(); // lấy record đầu tiên có status = 1

        return response()->json([
            'status' => true,
            'data' => $setting
        ]);
    }
    public function index()
    {
        $settings = Setting::get(); // lấy tất cả
        return response()->json([
            'status' => true,
            'data' => $settings
        ]);
    }

    /**
     * Tạo mới setting
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'site_name' => 'required|string|max:255',
            'email' => 'required|email',
            'phone' => 'nullable|string|max:20',
            'hotline' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:255',
            'status' => 'boolean'
        ]);

        $setting = Setting::create($data);

        return response()->json([
            'status' => true,
            'message' => 'Setting created successfully',
            'data' => $setting
        ]);
    }

    /**
     * Xem chi tiết 1 setting
     */
    public function show(string $id)
    {
        $setting = Setting::findOrFail($id);
        return response()->json([
            'status' => true,
            'data' => $setting
        ]);
    }

    /**
     * Cập nhật setting
     */
    public function update(Request $request, string $id)
    {
        // echo "Update setting ID: " . $id;
        $setting = Setting::findOrFail($id);

        $data = $request->validate([
            'site_name' => 'required|string|max:255',
            'email' => 'required|email',
            'phone' => 'nullable|string|max:20',
            'hotline' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:255',
            'status' => 'boolean'
        ]);

        $setting->update($data);

        return response()->json([
            'status' => true,
            'message' => 'Setting updated successfully',
            'data' => $setting
        ]);
    }

    /**
     * Xóa setting
     */
    public function destroy(string $id)
    {
        $setting = Setting::findOrFail($id);
        $setting->delete();

        return response()->json([
            'status' => true,
            'message' => 'Setting deleted successfully'
        ]);
    }
}
