<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Post;
use Illuminate\Support\Str;
class PostController extends Controller
{
    /**
     * Lấy tất cả bài viết
     */
    public function index()
    {
        $posts = Post::select('content', 'id', 'topic_id', 'title', 'slug', 'image', 'description', 'post_type', 'created_at', 'status')
            ->orderBy('created_at', 'desc')
            ->get();

        // Thêm URL đầy đủ cho ảnh nếu lưu trong public/images
        $posts = $posts->map(function ($post) {
            $post->image_url = $post->image ? asset('images/' . $post->image) : null;
            return $post;
        });

        return response()->json([
            'success' => true,
            'data' => $posts
        ]);
    }
    public function indexClient()
    {
        $posts = Post::select('content', 'id', 'topic_id', 'title', 'slug', 'image', 'description', 'post_type', 'created_at')
            ->where('status', 1)
            ->orderBy('created_at', 'desc')
            ->get();

        // Thêm URL đầy đủ cho ảnh nếu lưu trong public/images
        $posts = $posts->map(function ($post) {
            $post->image_url = $post->image ? asset('images/' . $post->image) : null;
            return $post;
        });

        return response()->json([
            'success' => true,
            'data' => $posts
        ]);
    }

    /**
     * Lấy chi tiết 1 bài viết theo id
     */
    public function show(string $idOrSlug)
    {
        // 🔍 Thử tìm theo ID hoặc slug
        $post = Post::where('id', $idOrSlug)
            ->orWhere('slug', $idOrSlug)
            ->first();

        // ❌ Nếu không tìm thấy
        if (!$post) {
            return response()->json([
                'success' => false,
                'message' => 'Không tìm thấy bài viết.'
            ], 404);
        }

        // ✅ Thêm URL đầy đủ cho ảnh
        $post->image_url = $post->image
            ? asset('images/post/' . $post->image)
            : asset('images/default.jpg');

        return response()->json([
            'success' => true,
            'data' => $post
        ]);
    }

    public function store(Request $request)
    {
        // echo "Chức năng thêm bài viết đang được phát triển.";
        // dd($request->all(), $request->file('image'))
        $request->validate([
            'title' => 'required|string|max:255|unique:post,title',
            'slug' => 'required|string|max:255|unique:post,slug',
            'topic_id' => 'nullable|integer',
            'post_type' => 'nullable|string|max:50',
            'content' => 'nullable|string',
            'description' => 'nullable|string',
            'status' => 'nullable|boolean',
            'image' => 'nullable|image',
        ]);

        $post = new Post();
        $post->title = $request->title;
        $post->slug = $request->slug ?: Str::slug($request->title);
        $post->topic_id = $request->topic_id ?? 0;
        $post->content = $request->content ?? '';
        $post->post_type = $request->post_type;
        $post->description = $request->description ?? '';
        $post->status = $request->status ?? 1;
        // Upload ảnh
        if ($request->hasFile('image')) {
            $file = $request->file('image');
            $filename = time() . '_' . $file->getClientOriginalName();
            $destination = public_path('images/post');
            $file->move($destination, $filename);
            $post->image = $filename;   // ✅ phải gán vào model
        }

        $post->save();

        // Thêm URL đầy đủ cho ảnh khi trả về JSON
        if ($post->image) {
            $post->image = url('images/post/' . $post->image);
        }

        return response()->json([
            'message' => '✅ Thêm bài viết thành công',
            'data' => $post
        ], 201);
    }
    public function update(Request $request, $id)
    {
        $post = Post::find($id);

        if (!$post) {
            return response()->json([
                'success' => false,
                'message' => '❌ Bài viết không tồn tại'
            ], 404);
        }

        try {
            // Validate dữ liệu
            $request->validate([
                'title' => 'sometimes|required|string|max:255|unique:post,title,' . $id,
                'slug' => 'sometimes|nullable|string|max:255|unique:post,slug,' . $id,
                'topic_id' => 'sometimes|nullable|integer',
                'post_type' => 'sometimes|nullable|string|max:50',
                'content' => 'sometimes|nullable|string',
                'description' => 'sometimes|nullable|string',
                'status' => 'sometimes|boolean',
                'image' => 'sometimes|nullable|image|mimes:jpeg,png,jpg,gif,webp|max:5120',
            ]);

            $data = $request->only([
                'title',
                'slug',
                'topic_id',
                'post_type',
                'content',
                'description',
                'status'
            ]);

            // Tạo slug nếu không có
            if (empty($data['slug']) && !empty($data['title'])) {
                $data['slug'] = Str::slug($data['title']);
            }

            // Upload ảnh mới (nếu có)
            if ($request->hasFile('image')) {
                $file = $request->file('image');
                $filename = time() . '_' . $file->getClientOriginalName();

                // Xóa file cũ nếu có
                $oldPath = public_path('images/post/' . $post->image);
                if ($post->image && file_exists($oldPath)) {
                    unlink($oldPath);
                }

                $file->move(public_path('images/post'), $filename);
                $data['image'] = $filename;
            }

            $post->update($data);

            // Thêm URL đầy đủ cho ảnh khi trả về
            if ($post->image) {
                $post->image_url = url('images/post/' . $post->image);
            }

            return response()->json([
                'success' => true,
                'message' => '✏️ Cập nhật bài viết thành công',
                'data' => $post
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => '❌ Lỗi server',
                'error' => $e->getMessage()
            ], 500);
        }
    }

}
