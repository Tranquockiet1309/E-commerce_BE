<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Topic;
use Illuminate\Validation\Rule;

class TopicController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $topics = Topic::all(); // có thể dùng paginate nếu cần
        return response()->json([
            'success' => true,
            'data' => $topics
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:topic,name',
            'slug' => 'nullable|string|max:255|unique:topic,slug',
            'sort_order' => 'nullable|integer|min:0',
            'description' => 'nullable|string',
            'status' => 'required|boolean'
        ]);

        $topic = Topic::create([
            'name' => $request->name,
            'slug' => $request->slug ?? \Str::slug($request->name),
            'sort_order' => $request->sort_order ?? 0,
            'description' => $request->description,
            'status' => $request->status,
            'created_by' => auth()->id() ?? 1
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Topic created successfully',
            'data' => $topic
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $topic = Topic::find($id);
        if (!$topic) {
            return response()->json([
                'success' => false,
                'message' => 'Topic not found'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $topic
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $topic = Topic::find($id);
        if (!$topic) {
            return response()->json([
                'success' => false,
                'message' => 'Topic not found'
            ], 404);
        }

        $request->validate([
            'name' => ['nullable', 'string', 'max:255', Rule::unique('topic')->ignore($id)],
            'slug' => ['nullable', 'string', 'max:255', Rule::unique('topic')->ignore($id)],
            'sort_order' => 'nullable|integer|min:0',
            'description' => 'nullable|string',
            'status' => 'nullable|boolean'
        ]);

        $topic->update(array_merge(
            $request->only(['name', 'slug', 'sort_order', 'description', 'status']),
            ['updated_by' => auth()->id() ?? 1]
        ));

        return response()->json([
            'success' => true,
            'message' => 'Topic updated successfully',
            'data' => $topic
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $topic = Topic::find($id);
        if (!$topic) {
            return response()->json([
                'success' => false,
                'message' => 'Topic not found'
            ], 404);
        }

        $topic->delete();

        return response()->json([
            'success' => true,
            'message' => 'Topic deleted successfully'
        ]);
    }
}
