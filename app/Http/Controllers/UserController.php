<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Hash;
class UserController extends Controller
{
    // Lấy danh sách user
    public function index()
    {
        $users = User::all();
        return response()->json([
            'success' => true,
            'data' => $users
        ]);
    }

    // Tạo mới user
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:user,email',
            'address' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:20',
            'username' => 'required|string|max:50|unique:user,username',
            'password' => 'required|string|min:6',
            'roles' => 'required|string|in:admin,customer,staff',
            'status' => 'nullable|integer|in:0,1',
            'created_by' => 'nullable|integer',
            'updated_by' => 'nullable|integer',
            'avatar' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        // Mã hóa mật khẩu
        $validated['password'] = bcrypt($validated['password']);

        // Nếu có ảnh avatar
        if ($request->hasFile('avatar')) {
            $file = $request->file('avatar');
            $fileName = time() . '_' . $file->getClientOriginalName();
            $file->move(public_path('images/avt'), $fileName);
            $validated['avatar'] = $fileName;
        }

        // Gán created_at, updated_at nếu model không auto
        $validated['created_at'] = now();
        $validated['updated_at'] = now();

        // Tạo người dùng
        $user = User::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Thêm người dùng thành công!',
            'data' => $user
        ], 201);
    }


    public function login(Request $request)
    {
        $request->validate([
            'username' => 'required|string',
            'password' => 'required|string',
        ]);

        $user = User::where('username', $request->username)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Thông tin đăng nhập không chính xác'
            ], 401);
        }

        // Xóa hết token cũ
        $user->tokens()->delete();

        // Tạo token mới
        $token = $user->createToken('api-token')->plainTextToken;

        return response()->json([
            'success' => true,
            'user' => $user,
            'roles' => $user->roles,
            'token' => $token,
        ]);
    }


    public function register(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:user,email',
            'phone' => 'required|string|max:20',
            'username' => 'required|string|max:50|unique:user,username',
            'password' => 'required|string|min:6|confirmed',
            'address' => 'nullable|string|max:255',
            'avatar' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $validated['password'] = bcrypt($validated['password']);
        $validated['roles'] = 'customer';
        $validated['status'] = 1;
        $validated['created_by'] = 1;
        $validated['created_at'] = now();
        $validated['updated_at'] = now();

        // Xử lý ảnh đại diện (avatar)
        if ($request->hasFile('avatar')) {
            $file = $request->file('avatar');
            $fileName = time() . '_' . $file->getClientOriginalName();
            $file->move(public_path('images/avt'), $fileName);
            $validated['avatar'] = $fileName;
        }

        $user = User::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Đăng ký tài khoản thành công!',
            'data' => $user
        ], 201);
    }

    public function changePassword(Request $request, $id)
    {
        $user = User::find($id);

        if (!$user) {
            return response()->json(['success' => false, 'message' => 'Người dùng không tồn tại!'], 404);
        }

        $validated = $request->validate([
            'current_password' => 'required|string',
            'new_password' => 'required|string|min:6|confirmed',
        ]);

        // Kiểm tra mật khẩu cũ
        if (!Hash::check($validated['current_password'], $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Mật khẩu hiện tại không chính xác!'
            ], 422);
        }

        // Cập nhật mật khẩu mới
        $user->password = bcrypt($validated['new_password']);
        $user->updated_at = now();
        $user->save();

        return response()->json([
            'success' => true,
            'message' => 'Đổi mật khẩu thành công!'
        ]);
    }

    public function logout(Request $request)
    {
        $user = $request->user();
        if ($user) {
            $user->tokens()->delete(); // Xóa token đang dùng
        }

        return response()->json(['success' => true]);
    }

    // Lấy chi tiết 1 user
    public function show(string $id)
    {
        $user = User::find($id);
        if (!$user) {
            return response()->json(['success' => false, 'message' => 'User not found'], 404);
        }

        return response()->json(['success' => true, 'data' => $user]);
    }

    // Cập nhật user
    public function update(Request $request, string $id)
    {
        $user = User::find($id);
        if (!$user) {
            return response()->json(['success' => false, 'message' => 'User not found'], 404);
        }

        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'email' => 'sometimes|email|unique:user,email,' . $id,
            'phone' => 'nullable|string|max:20',
            'username' => 'sometimes|string|unique:user,username,' . $id,
            'password' => 'nullable|string|min:6',
            'roles' => 'sometimes|string|in:admin,staff,customer',
            'status' => 'nullable|integer|in:0,1',
            'avatar' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        // ✅ Nếu có upload ảnh mới
        if ($request->hasFile('avatar')) {
            $file = $request->file('avatar');
            $fileName = time() . '_' . $file->getClientOriginalName();
            $file->move(public_path('images/avt'), $fileName);
            $validated['avatar'] = $fileName;

            // Xóa ảnh cũ (nếu có và tồn tại)
            if ($user->avatar && file_exists(public_path('images/avt/' . $user->avatar))) {
                @unlink(public_path('images/avt/' . $user->avatar));
            }
        }

        // ✅ Nếu có đổi mật khẩu
        if (!empty($validated['password'])) {
            $validated['password'] = bcrypt($validated['password']);
        } else {
            unset($validated['password']);
        }

        // ✅ Cập nhật thời gian
        $validated['updated_at'] = now();

        // ✅ Cập nhật user
        $user->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Cập nhật người dùng thành công!',
            'data' => $user,
        ]);
    }


    // Xóa user
    public function destroy(string $id)
    {
        $user = User::find($id);
        if (!$user) {
            return response()->json(['success' => false, 'message' => 'User not found'], 404);
        }

        $user->delete();

        return response()->json(['success' => true, 'message' => 'User deleted']);
    }
    public function updateClient(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:user,email,' . $id,
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:255',
            'avatar' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:5120',
        ]);

        if ($request->hasFile('avatar')) {
            $file = $request->file('avatar');
            $filename = time() . '_' . $file->getClientOriginalName();
            $file->move(public_path('images/avt'), $filename);
            $validated['avatar'] = $filename;
        }

        $user->update($validated);

        return response()->json([
            'success' => true,
            'data' => $user,
        ]);
    }

}
