<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Categories;
use Illuminate\Support\Facades\Cache;

class categoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Categories $categories)
    {
        $perPage = 10;
        $categories = Categories::paginate($perPage);
        return view("categories.index", compact("categories"));
    }

    public function getData()
    {
        $perPage = 10;
        $categories = Categories::paginate($perPage);
        return view("categories.data", compact("categories"));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        if ($request->ajax() || $request->wantsJson()) {
            $request->validate([
                'name' => 'required',
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
    public function edit(Request $request, string $id)
    {
        $categories = Categories::find($id);
        if ($request->ajax() || $request->wantsJson()) {
            $request->validate([
                'name' => 'required',
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

    public function search(Request $request)
    {
        $keyword = $request->input('keyword'); // Lấy từ khóa từ request
        if ($keyword > 0) {
            $key = "search_{$keyword}"; // Tạo một khóa cache duy nhất dựa trên từ khóa
            $categories = Cache::remember($key, 60 * 60, function () use ($keyword) {
                return Categories::where('name', 'like', "%{$keyword}%")->get();
            });
        } else {
            $perPage = 5;
            $categories = Categories::paginate($perPage);
        }
        return view("categories.data", compact("categories"));
    }
    public function destroy(string $id, Categories $categories)
    {
        $this->authorize('delete', $categories);
        $categories = Categories::find($id);
        $categories->delete();
        return response()->json(['success' => 'Thể loại đã được xóa thành công!']);
    }
}
