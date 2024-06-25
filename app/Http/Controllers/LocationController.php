<?php

namespace App\Http\Controllers;

use App\Models\ProductLocation;
use Illuminate\Http\Request;

class LocationController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $zone = $request->input('zone');
        $shelf = $request->input('shelf');

        // Lấy danh sách vị trí theo bộ lọc và tải sẵn thông tin sản phẩm
        $query = ProductLocation::with('product.productImage');

        if ($zone) {
            $query->where('zone', $zone);
        } else {
            $query->where('zone', 1);
            $zone = 1; // Giá trị mặc định nếu không có zone được cung cấp
        }

        if ($shelf) {
            $query->where('shelf', $shelf);
        } else {
            $query->where('shelf', 'A'); // Giá trị mặc định nếu không có shelf được cung cấp
        }

        $locations = $query->get();

        // Lấy danh sách tất cả các zone
        $zones = ProductLocation::select('zone')->distinct()->orderBy('zone')->get();

        // Lấy danh sách shelf dựa trên zone đã chọn
        $shelves = ProductLocation::where('zone', $zone)->select('shelf')->distinct()->orderBy('shelf')->get();

        if ($request->ajax()) {
            return response()->json([
                'locations' => $locations,
                'zones' => $zones,
                'shelves' => $shelves
            ]);
        }

        return view('location.index', compact('locations', 'zones', 'shelves'));
    }

    public function getData(Request $request)
    {
        $zone = $request->input('zone');
        $shelf = $request->input('shelf');

        // Lấy danh sách vị trí theo bộ lọc và tải sẵn thông tin sản phẩm
        $query = ProductLocation::with('product.productImage');

        if ($zone) {
            $query->where('zone', $zone);
        }

        if ($shelf) {
            $query->where('shelf', $shelf);
        }

        $locations = $query->get();
        return view('location.data', compact('locations'));
    }


    public function getShelf(Request $request)
    {
        $zone = $request->input('zone');
        $shelves = ProductLocation::where('zone', $zone)
            ->whereNull('product_id')
            ->select('shelf')
            ->distinct()
            ->orderBy('shelf')
            ->get();
        return response()->json(['shelves' => $shelves]);
    }

    public function getLevel(Request $request)
    {
        $shelf = $request->input('shelf');
        $levels = ProductLocation::where('shelf', $shelf)
            ->whereNull('product_id')
            ->select('level')
            ->distinct()
            ->orderBy('level')
            ->get();
        return response()->json(['levels' => $levels]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function addNewLocationAutomatically()
    {
        $lastLocation = ProductLocation::orderBy('zone', 'desc')->orderBy('shelf', 'desc')->orderBy('level', 'desc')->first();

        if (!$lastLocation) {
            // Không có vị trí nào, tạo vị trí đầu tiên
            $newLocation = new ProductLocation(['zone' => 1, 'shelf' => 'A', 'level' => 1]);
        } else {
            // Tạo vị trí dựa trên vị trí cuối cùng
            $newZone = $lastLocation->zone;
            $newShelf = $lastLocation->shelf;
            $newLevel = $lastLocation->level + 1;

            if ($newLevel > 10) {
                $newLevel = 1;
                $newShelf = chr(ord($newShelf) + 1);
                if (ord($newShelf) > ord('Z')) {
                    $newShelf = 'A';
                    $newZone += 1;
                }
            }

            $newLocation = new ProductLocation(['zone' => $newZone, 'shelf' => $newShelf, 'level' => $newLevel]);
        }

        if ($newLocation->save()) {
            return response()->json(['message' => 'Thêm mới vị trí thành công', 'location' => $newLocation]);
        } else {
            return response()->json(['message' => 'Lỗi khi thêm vị trí mới'], 500);
        }
    }


    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
