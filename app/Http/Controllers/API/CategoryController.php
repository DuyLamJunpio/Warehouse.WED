<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Categories;
use Illuminate\Support\Facades\Cache;

class CategoryController extends Controller
{
    public function index(Categories $categories)
    {
        $this->authorize('viewAny', $categories);
        $perPage = 15;
        $categories = Categories::withCount('products')->paginate($perPage); // Thêm withCount để đếm sản phẩm
        return response()->json($categories);
    }

    public function getCategory(string $id, Categories $categories)
    {
        $this->authorize('view', $categories);
        $category = Categories::withCount('products')->findOrFail($id); // Sử dụng withCount để đếm sản phẩm và findOrFail để xử lý không tìm thấy
        return response()->json($category);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, Categories $categories)
    {
        $this->authorize('create', $categories);
        if ($request->isMethod("POST")) {
            $request->validate([
                'name' => 'required|max:50',
            ]);
            $params = $request->except('_token');
            $params['status'] = 1;
            $categories = Categories::create($params);
            if ($categories->id) {
                return response()->json(['success' => 'Thể loại đã được thêm thành công!']);
            } else {
                return response()->json(['error' => 'Có lỗi xảy ra, vui lòng thử lại.'], 500);
            }
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id, Categories $categories)
    {
        $this->authorize('update', $categories);
        $categories = Categories::find($id);
        if ($request->isMethod('POST')) {
            $request->validate([
                'name' => 'required|max:50',
                'status'=> 'required'
            ]);
            $params = $request->except('_token');
            $result = $categories->update($params);
            if ($result) {
                return response()->json(['success' => 'Thể loại đã được sửa thành công!']);
            } else {
                return response()->json(['error' => 'Có lỗi xảy ra, vui lòng thử lại.'], 500);
            }
        }
    }

    public function search(Request $request, Categories $categories)
    {
        $keyword = $request->input('keyword'); // Lấy từ khóa từ request
        if (!empty($keyword)) {
            $key = "search_categories_{$keyword}"; // Tạo một khóa cache duy nhất dựa trên từ khóa
            $categories = Cache::remember($key, 60 * 60, function () use ($keyword) {
                return Categories::where('name', 'like', "%{$keyword}%")
                                 ->withCount('products')
                                 ->get();
            });
        }
        return response()->json($categories);
    }

    public function destroy(string $id, Categories $categories)
    {
        $this->authorize('delete', $categories);
        $this->authorize('delete', $categories);
        $categories = Categories::find($id);
        $categories->delete();
        return response()->json(['success' => 'Thể loại đã được xóa thành công!']);
    }
}
