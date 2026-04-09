<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\Contact; // chắc chắn bạn đã tạo model Contact
use Illuminate\Support\Facades\Auth;


class ContactController extends Controller
{

    public function index()
    {
        try {
            $contacts = Contact::orderBy('created_at', 'desc')->get();

            return response()->json([
                'success' => true,
                'message' => 'Lấy danh sách liên hệ thành công!',
                'data' => $contacts
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Không thể lấy danh sách liên hệ',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Validate dữ liệu
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'nullable|string|max:20',
            'content' => 'required|string',
            'user_id' => 'nullable|integer', // thêm user_id nếu gửi từ frontend
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
                'message' => 'Thông tin không hợp lệ',
            ], 422);
        }

        try {
            $contact = new Contact();
            $contact->name = $request->name;
            $contact->email = $request->email;
            $contact->phone = $request->phone;
            $contact->content = $request->content;
            $contact->status = 0; // mặc định chưa xử lý

            // Gán user_id nếu có
            if ($request->user_id) {
                $contact->user_id = $request->user_id;
            } elseif (Auth::check()) {
                $contact->user_id = Auth::id();
            }

            $contact->save();

            return response()->json([
                'success' => true,
                'message' => 'Gửi liên hệ thành công!',
                'data' => $contact
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Có lỗi xảy ra khi lưu liên hệ',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
    public function show($id)
    {
        try {
            $contact = Contact::find($id);

            if (!$contact) {
                return response()->json([
                    'success' => false,
                    'message' => 'Không tìm thấy liên hệ',
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'Lấy chi tiết liên hệ thành công!',
                'data' => $contact
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Lỗi khi lấy chi tiết liên hệ',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Xoá liên hệ
     */
    public function destroy($id)
    {
        try {
            $contact = Contact::find($id);

            if (!$contact) {
                return response()->json([
                    'success' => false,
                    'message' => 'Không tìm thấy liên hệ để xoá',
                ], 404);
            }

            $contact->delete();

            return response()->json([
                'success' => true,
                'message' => 'Xoá liên hệ thành công!'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Lỗi khi xoá liên hệ',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
    public function update(Request $request, $id)
    {
        try {
            $contact = Contact::find($id);

            if (!$contact) {
                return response()->json([
                    'success' => false,
                    'message' => 'Không tìm thấy liên hệ để cập nhật',
                ], 404);
            }

            // Chỉ cho phép cập nhật status (hoặc có thể mở rộng nếu cần)
            $validator = Validator::make($request->all(), [
                'status' => 'required|integer|in:0,1',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors(),
                    'message' => 'Dữ liệu không hợp lệ',
                ], 422);
            }

            $contact->status = $request->status;
            $contact->save();

            return response()->json([
                'success' => true,
                'message' => 'Cập nhật liên hệ thành công!',
                'data' => $contact
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Lỗi khi cập nhật liên hệ',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

}
