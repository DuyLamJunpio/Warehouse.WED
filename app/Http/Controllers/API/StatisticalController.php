<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Invoice;
use App\Models\Product;
use App\Models\ProductInvoice;
use Carbon\Carbon;

// class StatisticalController extends Controller
// {
//     public function statsToday()
//     {
//         $todayStart = Carbon::today()->startOfDay();
//         $todayEnd = Carbon::today()->endOfDay();
//         return $this->getStatsForPeriod($todayStart, $todayEnd);
//     }

//     public function statsYesterday()
//     {
//         $yesterdayStart = Carbon::yesterday()->startOfDay();
//         $yesterdayEnd = Carbon::yesterday()->endOfDay();
//         return $this->getStatsForPeriod($yesterdayStart, $yesterdayEnd);
//     }

//     public function statsThisMonth()
//     {
//         $startOfMonth = Carbon::now()->startOfMonth();
//         $endOfMonth = Carbon::now()->endOfMonth();
//         return $this->getStatsForPeriod($startOfMonth, $endOfMonth);
//     }

//     public function statsLastMonth()
//     {
//         $startOfLastMonth = Carbon::now()->subMonth()->startOfMonth();
//         $endOfLastMonth = Carbon::now()->subMonth()->endOfMonth();
//         return $this->getStatsForPeriod($startOfLastMonth, $endOfLastMonth);
//     }

//     private function getStatsForPeriod($startDate, $endDate)
//     {
//         $monthlyStats = Invoice::whereBetween('created_at', [$startDate, $endDate])
//             ->where('invoice_type', 0) // Giả sử 0 là nhập kho
//             ->with(['productInvoices.product'])
//             ->get()
//             ->groupBy(function ($date) {
//                 return Carbon::parse($date->created_at)->format('Y-m'); // nhóm theo năm-tháng
//             })
//             ->map(function ($month) {
//                 return [
//                     'total_quantity' => $month->sum(function ($invoice) {
//                         return $invoice->productInvoices->sum('quantity');
//                     }),
//                     'total_value' => $month->sum(function ($invoice) {
//                         return $invoice->productInvoices->sum(function ($productInvoice) {
//                             return $productInvoice->quantity * $productInvoice->product->import_price;
//                         });
//                     })
//                 ];
//             });

//         // Thống kê theo từng ngày trong khoảng thời gian
//         $dailyStats = Invoice::whereBetween('created_at', [$startDate, $endDate])
//             ->where('invoice_type', 0)
//             ->with(['productInvoices.product'])
//             ->get()
//             ->groupBy(function ($date) {
//                 return Carbon::parse($date->created_at)->format('Y-m-d'); // nhóm theo năm-tháng-ngày
//             })
//             ->map(function ($day) {
//                 return [
//                     'total_quantity' => $day->sum(function ($invoice) {
//                         return $invoice->productInvoices->sum('quantity');
//                     }),
//                     'total_value' => $day->sum(function ($invoice) {
//                         return $invoice->productInvoices->sum(function ($productInvoice) {
//                             return $productInvoice->quantity * $productInvoice->product->import_price;
//                         });
//                     }),
//                     'invoices' => $day->map(function ($invoice) {
//                         return [
//                             'invoice_id' => $invoice->id,
//                             'created_at' => $invoice->created_at->format('Y-m-d H:i:s'),
//                             'total_amount' => $invoice->total_amount,
//                             'details' => $invoice->productInvoices->map(function ($productInvoice) {
//                                 return [
//                                     'product_id' => $productInvoice->product->id,
//                                     'quantity' => $productInvoice->quantity,
//                                     'import_price' => $productInvoice->product->import_price,
//                                     'total_price' => $productInvoice->quantity * $productInvoice->product->import_price
//                                 ];
//                             })
//                         ];
//                     })
//                 ];
//             });

//         return response()->json([
//             'monthly_stats' => $monthlyStats,
//             'daily_stats' => $dailyStats
//         ]);
//     }

//     public function showInventoryStats(Request $request)
//     {
//         $startDate = Carbon::createFromFormat('Y-m-d', $request->start_date);
//         $endDate = Carbon::createFromFormat('Y-m-d', $request->end_date);

//         // Thống kê theo từng tháng
//         $monthlyStats = Invoice::whereBetween('created_at', [$startDate, $endDate])
//             ->where('invoice_type', 0) // Giả sử 0 là nhập kho
//             ->with(['productInvoices.product'])
//             ->get()
//             ->groupBy(function ($date) {
//                 return Carbon::parse($date->created_at)->format('Y-m'); // nhóm theo năm-tháng
//             })
//             ->map(function ($month) {
//                 return [
//                     'total_quantity' => $month->sum(function ($invoice) {
//                         return $invoice->productInvoices->sum('quantity');
//                     }),
//                     'total_value' => $month->sum(function ($invoice) {
//                         return $invoice->productInvoices->sum(function ($productInvoice) {
//                             return $productInvoice->quantity * $productInvoice->product->import_price;
//                         });
//                     })
//                 ];
//             });

//         // Thống kê theo từng ngày trong khoảng thời gian
//         $dailyStats = Invoice::whereBetween('created_at', [$startDate, $endDate])
//             ->where('invoice_type', 0)
//             ->with(['productInvoices.product'])
//             ->get()
//             ->groupBy(function ($date) {
//                 return Carbon::parse($date->created_at)->format('Y-m-d'); // nhóm theo năm-tháng-ngày
//             })
//             ->map(function ($day) {
//                 return [
//                     'total_quantity' => $day->sum(function ($invoice) {
//                         return $invoice->productInvoices->sum('quantity');
//                     }),
//                     'total_value' => $day->sum(function ($invoice) {
//                         return $invoice->productInvoices->sum(function ($productInvoice) {
//                             return $productInvoice->quantity * $productInvoice->product->import_price;
//                         });
//                     }),
//                     'invoices' => $day->map(function ($invoice) {
//                         return [
//                             'invoice_id' => $invoice->id,
//                             'created_at' => $invoice->created_at->format('Y-m-d H:i:s'),
//                             'total_amount' => $invoice->total_amount,
//                             'details' => $invoice->productInvoices->map(function ($productInvoice) {
//                                 return [
//                                     'product_id' => $productInvoice->product->id,
//                                     'quantity' => $productInvoice->quantity,
//                                     'import_price' => $productInvoice->product->import_price,
//                                     'total_price' => $productInvoice->quantity * $productInvoice->product->import_price
//                                 ];
//                             })
//                         ];
//                     })
//                 ];
//             });

//         return response()->json([
//             'monthly_stats' => $monthlyStats,
//             'daily_stats' => $dailyStats
//         ]);
//     }



//     public function statsExportToday()
//     {
//         $todayStart = Carbon::today()->startOfDay();
//         $todayEnd = Carbon::today()->endOfDay();
//         return $this->getExportStatsForPeriod($todayStart, $todayEnd);
//     }

//     public function statsExportYesterday()
//     {
//         $yesterdayStart = Carbon::yesterday()->startOfDay();
//         $yesterdayEnd = Carbon::yesterday()->endOfDay();
//         return $this->getExportStatsForPeriod($yesterdayStart, $yesterdayEnd);
//     }

//     public function statsExportThisMonth()
//     {
//         $startOfMonth = Carbon::now()->startOfMonth();
//         $endOfMonth = Carbon::now()->endOfMonth();
//         return $this->getExportStatsForPeriod($startOfMonth, $endOfMonth);
//     }

//     public function statsExportLastMonth()
//     {
//         $startOfLastMonth = Carbon::now()->subMonth()->startOfMonth();
//         $endOfLastMonth = Carbon::now()->subMonth()->endOfMonth();
//         return $this->getExportStatsForPeriod($startOfLastMonth, $endOfLastMonth);
//     }

//     private function getExportStatsForPeriod($startDate, $endDate)
//     {
//         $monthlyStats = Invoice::whereBetween('created_at', [$startDate, $endDate])
//             ->where('invoice_type', 1) // Giả sử 1 là xuất kho
//             ->with(['productInvoices.product'])
//             ->get()
//             ->groupBy(function ($date) {
//                 return Carbon::parse($date->created_at)->format('Y-m'); // nhóm theo năm-tháng
//             })
//             ->map(function ($month) {
//                 return [
//                     'total_quantity' => $month->sum(function ($invoice) {
//                         return $invoice->productInvoices->sum('quantity');
//                     }),
//                     'total_value' => $month->sum(function ($invoice) {
//                         return $invoice->productInvoices->sum(function ($productInvoice) {
//                             return $productInvoice->quantity * $productInvoice->product->sell_price; // Sử dụng giá bán
//                         });
//                     })
//                 ];
//             });

//         // Thống kê theo từng ngày trong khoảng thời gian
//         $dailyStats = Invoice::whereBetween('created_at', [$startDate, $endDate])
//             ->where('invoice_type', 1) // Giả sử 1 là xuất kho
//             ->with(['productInvoices.product'])
//             ->get()
//             ->groupBy(function ($date) {
//                 return Carbon::parse($date->created_at)->format('Y-m-d'); // nhóm theo năm-tháng-ngày
//             })
//             ->map(function ($day) {
//                 return [
//                     'total_quantity' => $day->sum(function ($invoice) {
//                         return $invoice->productInvoices->sum('quantity');
//                     }),
//                     'total_value' => $day->sum(function ($invoice) {
//                         return $invoice->productInvoices->sum(function ($productInvoice) {
//                             return $productInvoice->quantity * $productInvoice->product->sell_price; // Sử dụng giá bán
//                         });
//                     }),
//                     'invoices' => $day->map(function ($invoice) {
//                         return [
//                             'invoice_id' => $invoice->id,
//                             'created_at' => $invoice->created_at->format('Y-m-d H:i:s'),
//                             'total_amount' => $invoice->total_amount,
//                             'details' => $invoice->productInvoices->map(function ($productInvoice) {
//                                 return [
//                                     'product_id' => $productInvoice->product->id,
//                                     'quantity' => $productInvoice->quantity,
//                                     'unit_price' => $productInvoice->product->sell_price, // Giá bán
//                                     'total_price' => $productInvoice->quantity * $productInvoice->product->sell_price
//                                 ];
//                             })
//                         ];
//                     })
//                 ];
//             });

//         return response()->json([
//             'monthly_stats' => $monthlyStats,
//             'daily_stats' => $dailyStats
//         ]);
//     }

//     public function showInventoryExportStats(Request $request)
//     {
//         $startDate = Carbon::createFromFormat('Y-m-d', $request->start_date);
//         $endDate = Carbon::createFromFormat('Y-m-d', $request->end_date);

//         // Thống kê theo từng tháng
//         $monthlyStats = Invoice::whereBetween('created_at', [$startDate, $endDate])
//             ->where('invoice_type', 1) // Giả sử 1 là xuất kho
//             ->with(['productInvoices.product'])
//             ->get()
//             ->groupBy(function ($date) {
//                 return Carbon::parse($date->created_at)->format('Y-m'); // nhóm theo năm-tháng
//             })
//             ->map(function ($month) {
//                 return [
//                     'total_quantity' => $month->sum(function ($invoice) {
//                         return $invoice->productInvoices->sum('quantity');
//                     }),
//                     'total_value' => $month->sum(function ($invoice) {
//                         return $invoice->productInvoices->sum(function ($productInvoice) {
//                             return $productInvoice->quantity * $productInvoice->product->sell_price; // Sử dụng giá bán
//                         });
//                     })
//                 ];
//             });

//         // Thống kê theo từng ngày trong khoảng thời gian
//         $dailyStats = Invoice::whereBetween('created_at', [$startDate, $endDate])
//             ->where('invoice_type', 1) // Giả sử 1 là xuất kho
//             ->with(['productInvoices.product'])
//             ->get()
//             ->groupBy(function ($date) {
//                 return Carbon::parse($date->created_at)->format('Y-m-d'); // nhóm theo năm-tháng-ngày
//             })
//             ->map(function ($day) {
//                 return [
//                     'total_quantity' => $day->sum(function ($invoice) {
//                         return $invoice->productInvoices->sum('quantity');
//                     }),
//                     'total_value' => $day->sum(function ($invoice) {
//                         return $invoice->productInvoices->sum(function ($productInvoice) {
//                             return $productInvoice->quantity * $productInvoice->product->sell_price; // Sử dụng giá bán
//                         });
//                     }),
//                     'invoices' => $day->map(function ($invoice) {
//                         return [
//                             'invoice_id' => $invoice->id,
//                             'created_at' => $invoice->created_at->format('Y-m-d H:i:s'),
//                             'total_amount' => $invoice->total_amount,
//                             'details' => $invoice->productInvoices->map(function ($productInvoice) {
//                                 return [
//                                     'product_id' => $productInvoice->product->id,
//                                     'quantity' => $productInvoice->quantity,
//                                     'unit_price' => $productInvoice->product->sell_price, // Giá bán
//                                     'total_price' => $productInvoice->quantity * $productInvoice->product->sell_price
//                                 ];
//                             })
//                         ];
//                     })
//                 ];
//             });

//         return response()->json([
//             'monthly_stats' => $monthlyStats,
//             'daily_stats' => $dailyStats
//         ]);
//     }

//     public function inventoryStatsByExpiry(Request $request)
//     {
//         $products = Product::with(['expiries'])
//             ->get()
//             ->groupBy('category_id')
//             ->map(function ($productsInCategory) {
//                 return $productsInCategory->map(function ($product) {
//                     if ($product->expiries->isEmpty()) {
//                         return [
//                             'product_id' => $product->id,
//                             'message' => 'Hết hàng'
//                         ];
//                     } else {
//                         return [
//                             'product_id' => $product->id,
//                             'expiries' => $product->expiries->map(function ($expiry) {
//                                 return [
//                                     'expiry_date' => $expiry->expiry_date,
//                                     'total_quantity' => $expiry->quantity_exp
//                                 ];
//                             })
//                         ];
//                     }
//                 });
//             });

//         return response()->json($products);
//     }
// }


class StatisticalController extends Controller
{
    public function statsToday()
    {
        $todayStart = Carbon::today()->startOfDay();
        $todayEnd = Carbon::today()->endOfDay();
        return $this->getStatsForPeriod($todayStart, $todayEnd);
    }

    public function statsYesterday()
    {
        $yesterdayStart = Carbon::yesterday()->startOfDay();
        $yesterdayEnd = Carbon::yesterday()->endOfDay();
        return $this->getStatsForPeriod($yesterdayStart, $yesterdayEnd);
    }

    public function statsThisMonth()
    {
        $startOfMonth = Carbon::now()->startOfMonth();
        $endOfMonth = Carbon::now()->endOfMonth();
        return $this->getStatsForPeriod($startOfMonth, $endOfMonth);
    }

    public function statsLastMonth()
    {
        $startOfLastMonth = Carbon::now()->subMonth()->startOfMonth();
        $endOfLastMonth = Carbon::now()->subMonth()->endOfMonth();
        return $this->getStatsForPeriod($startOfLastMonth, $endOfLastMonth);
    }

    private function getStatsForPeriod($startDate, $endDate)
    {
        $monthlyStats = Invoice::whereBetween('created_at', [$startDate, $endDate])
            ->where('invoice_type', 0) // Giả sử 0 là nhập kho
            ->with(['productInvoices.product' => function ($query) {
                $query->withTrashed();
            }])->withTrashed()
            ->get()
            ->groupBy(function ($date) {
                return Carbon::parse($date->created_at)->format('Y-m'); // nhóm theo năm-tháng
            })
            ->map(function ($month, $key) {
                return [
                    'time' => $key,
                    'total_quantity' => $month->sum(function ($invoice) {
                        return $invoice->productInvoices->sum('quantity');
                    }),
                    'total_value' => $month->sum(function ($invoice) {
                        return $invoice->productInvoices->sum(function ($productInvoice) {
                            return $productInvoice->quantity * $productInvoice->product->import_price;
                        });
                    }),
                    'invoice_count' => $month->count()
                ];
            });

        // Thống kê theo từng ngày trong khoảng thời gian
        $dailyStats = Invoice::whereBetween('created_at', [$startDate, $endDate])
            ->where('invoice_type', 0)
            ->with(['productInvoices.product' => function ($query) {
                $query->withTrashed();
            }])->withTrashed()
            ->get()
            ->groupBy(function ($date) {
                return Carbon::parse($date->created_at)->format('Y-m-d'); // nhóm theo năm-tháng-ngày
            })
            ->map(function ($day, $key) {
                return [
                    'time' => $key,
                    'total_quantity' => $day->sum(function ($invoice) {
                        return $invoice->productInvoices->sum('quantity');
                    }),
                    'total_value' => $day->sum(function ($invoice) {
                        return $invoice->productInvoices->sum(function ($productInvoice) {
                            return $productInvoice->quantity * $productInvoice->product->import_price;
                        });
                    }),
                    'invoices' => $day->map(function ($invoice) {
                        return [
                            'invoice_id' => $invoice->id,
                            'created_at' => $invoice->created_at->format('Y-m-d H:i:s'),
                            'total_amount' => $invoice->total_amount,
                            'details' => $invoice->productInvoices->map(function ($productInvoice) {
                                return [
                                    'product_id' => $productInvoice->product->id,
                                    'quantity' => $productInvoice->quantity,
                                    'import_price' => $productInvoice->product->import_price,
                                    'total_price' => $productInvoice->quantity * $productInvoice->product->import_price
                                ];
                            })
                        ];
                    })
                ];
            });

        return response()->json([
            'monthly_stats' => $monthlyStats,
            'daily_stats' => ['data' => $dailyStats->values()]
        ]);
    }

    public function showInventoryStats(Request $request)
    {
        try {
            if ($request->start_date == $request->end_date) {
                $startDate = Carbon::createFromFormat('Y-m-d', $request->start_date)->startOfDay();
                $endDate = Carbon::createFromFormat('Y-m-d', $request->end_date)->endOfDay();
            } else {
                $startDate = Carbon::createFromFormat('Y-m-d', $request->start_date);
                $endDate = Carbon::createFromFormat('Y-m-d', $request->end_date);
            }
        } catch (\Exception $e) {
            return response()->json(['error' => 'Invalid date format. Please use Y-m-d format.'], 400);
        }

        // Thống kê theo từng tháng
        $monthlyStats = Invoice::whereBetween('created_at', [$startDate, $endDate])
            ->where('invoice_type', 0) // Giả sử 0 là nhập kho
            ->with(['productInvoices.product' => function ($query) {
                $query->withTrashed();
            }])->withTrashed()
            ->get()
            ->groupBy(function ($date) {
                return Carbon::parse($date->created_at)->format('Y-m'); // nhóm theo năm-tháng
            })
            ->map(function ($month, $key) {
                return [
                    'time' => $key,
                    'total_quantity' => $month->sum(function ($invoice) {
                        return $invoice->productInvoices->sum('quantity');
                    }),
                    'total_value' => $month->sum(function ($invoice) {
                        return $invoice->productInvoices->sum(function ($productInvoice) {
                            return $productInvoice->quantity * $productInvoice->product->import_price;
                        });
                    }),
                    'invoice_count' => $month->count()
                ];
            });

        // Thống kê theo từng ngày trong khoảng thời gian
        $dailyStats = Invoice::whereBetween('created_at', [$startDate, $endDate])
            ->where('invoice_type', 0)
            ->with(['productInvoices.product' => function ($query) {
                $query->withTrashed();
            }])->withTrashed()
            ->get()
            ->groupBy(function ($date) {
                return Carbon::parse($date->created_at)->format('Y-m-d'); // nhóm theo năm-tháng-ngày
            })
            ->map(function ($day, $key) {
                return [
                    'time' => $key,
                    'total_quantity' => $day->sum(function ($invoice) {
                        return $invoice->productInvoices->sum('quantity');
                    }),
                    'total_value' => $day->sum(function ($invoice) {
                        return $invoice->productInvoices->sum(function ($productInvoice) {
                            return $productInvoice->quantity * $productInvoice->product->import_price;
                        });
                    }),
                    'invoices' => $day->map(function ($invoice) {
                        return [
                            'invoice_id' => $invoice->id,
                            'created_at' => $invoice->created_at->format('Y-m-d H:i:s'),
                            'total_amount' => $invoice->total_amount,
                            'details' => $invoice->productInvoices->map(function ($productInvoice) {
                                return [
                                    'product_id' => $productInvoice->product->id,
                                    'quantity' => $productInvoice->quantity,
                                    'import_price' => $productInvoice->product->import_price,
                                    'total_price' => $productInvoice->quantity * $productInvoice->product->import_price
                                ];
                            })
                        ];
                    })
                ];
            });

        return response()->json([
            'monthly_stats' => $monthlyStats,
            'daily_stats' => ['data' => $dailyStats->values()]
        ]);
    }



    public function statsExportToday()
    {
        $todayStart = Carbon::today()->startOfDay();
        $todayEnd = Carbon::today()->endOfDay();
        return $this->getExportStatsForPeriod($todayStart, $todayEnd);
    }

    public function statsExportYesterday()
    {
        $yesterdayStart = Carbon::yesterday()->startOfDay();
        $yesterdayEnd = Carbon::yesterday()->endOfDay();
        return $this->getExportStatsForPeriod($yesterdayStart, $yesterdayEnd);
    }

    public function statsExportThisMonth()
    {
        $startOfMonth = Carbon::now()->startOfMonth();
        $endOfMonth = Carbon::now()->endOfMonth();
        return $this->getExportStatsForPeriod($startOfMonth, $endOfMonth);
    }

    public function statsExportLastMonth()
    {
        $startOfLastMonth = Carbon::now()->subMonth()->startOfMonth();
        $endOfLastMonth = Carbon::now()->subMonth()->endOfMonth();
        return $this->getExportStatsForPeriod($startOfLastMonth, $endOfLastMonth);
    }

    private function getExportStatsForPeriod($startDate, $endDate)
    {
        $monthlyStats = Invoice::whereBetween('created_at', [$startDate, $endDate])
            ->where('invoice_type', 1) // Giả sử 1 là xuất kho
            ->with(['productInvoices.product' => function ($query) {
                $query->withTrashed();
            }])->withTrashed()
            ->get()
            ->groupBy(function ($date) {
                return Carbon::parse($date->created_at)->format('Y-m'); // nhóm theo năm-tháng
            })
            ->map(function ($month, $key) {
                return [
                    'time' => $key,
                    'total_quantity' => $month->sum(function ($invoice) {
                        return $invoice->productInvoices->sum('quantity');
                    }),
                    'total_value' => $month->sum(function ($invoice) {
                        return $invoice->productInvoices->sum(function ($productInvoice) {
                            return $productInvoice->quantity * $productInvoice->product->sell_price; // Sử dụng giá bán
                        });
                    }),
                    'invoice_count' => $month->count()
                ];
            });

        // Thống kê theo từng ngày trong khoảng thời gian
        $dailyStats = Invoice::whereBetween('created_at', [$startDate, $endDate])
            ->where('invoice_type', 1) // Giả sử 1 là xuất kho
            ->with(['productInvoices.product' => function ($query) {
                $query->withTrashed();
            }])->withTrashed()
            ->get()
            ->groupBy(function ($date) {
                return Carbon::parse($date->created_at)->format('Y-m-d'); // nhóm theo năm-tháng-ngày
            })
            ->map(function ($day, $key) {
                return [
                    'time' => $key,
                    'total_quantity' => $day->sum(function ($invoice) {
                        return $invoice->productInvoices->sum('quantity');
                    }),
                    'total_value' => $day->sum(function ($invoice) {
                        return $invoice->productInvoices->sum(function ($productInvoice) {
                            return $productInvoice->quantity * $productInvoice->product->sell_price; // Sử dụng giá bán
                        });
                    }),
                    'invoices' => $day->map(function ($invoice) {
                        return [
                            'invoice_id' => $invoice->id,
                            'created_at' => $invoice->created_at->format('Y-m-d H:i:s'),
                            'total_amount' => $invoice->total_amount,
                            'details' => $invoice->productInvoices->map(function ($productInvoice) {
                                return [
                                    'product_id' => $productInvoice->product->id,
                                    'quantity' => $productInvoice->quantity,
                                    'unit_price' => $productInvoice->product->sell_price, // Giá bán
                                    'total_price' => $productInvoice->quantity * $productInvoice->product->sell_price
                                ];
                            })
                        ];
                    })
                ];
            });

        return response()->json([
            'monthly_stats' => $monthlyStats,
            'daily_stats' => ['data' => $dailyStats->values()]
        ]);
    }

    public function showInventoryExportStats(Request $request)
    {
        try {
            if ($request->start_date == $request->end_date) {
                $startDate = Carbon::createFromFormat('Y-m-d', $request->start_date)->startOfDay();
                $endDate = Carbon::createFromFormat('Y-m-d', $request->end_date)->endOfDay();
            } else {
                $startDate = Carbon::createFromFormat('Y-m-d', $request->start_date);
                $endDate = Carbon::createFromFormat('Y-m-d', $request->end_date);
            }
        } catch (\Exception $e) {
            return response()->json(['error' => 'Invalid date format. Please use Y-m-d format.'], 400);
        }

        // Thống kê theo từng tháng
        $monthlyStats = Invoice::whereBetween('created_at', [$startDate, $endDate])
            ->where('invoice_type', 1) // Giả sử 1 là xuất kho
            ->with(['productInvoices.product' => function ($query) {
                $query->withTrashed();
            }])->withTrashed()
            ->get()
            ->groupBy(function ($date) {
                return Carbon::parse($date->created_at)->format('Y-m'); // nhóm theo năm-tháng
            })
            ->map(function ($month, $key) {
                return [
                    'time' => $key,
                    'total_quantity' => $month->sum(function ($invoice) {
                        return $invoice->productInvoices->sum('quantity');
                    }),
                    'total_value' => $month->sum(function ($invoice) {
                        return $invoice->productInvoices->sum(function ($productInvoice) {
                            return $productInvoice->quantity * $productInvoice->product->sell_price; // Sử dụng giá bán
                        });
                    }),
                    'invoice_count' => $month->count()
                ];
            });

        // Thống kê theo từng ngày trong khoảng thời gian
        $dailyStats = Invoice::whereBetween('created_at', [$startDate, $endDate])
            ->where('invoice_type', 1) // Giả sử 1 là xuất kho
            ->with(['productInvoices.product' => function ($query) {
                $query->withTrashed();
            }])->withTrashed()
            ->get()
            ->groupBy(function ($date) {
                return Carbon::parse($date->created_at)->format('Y-m-d'); // nhóm theo năm-tháng-ngày
            })
            ->map(function ($day, $key) {
                return [
                    'time' => $key,
                    'total_quantity' => $day->sum(function ($invoice) {
                        return $invoice->productInvoices->sum('quantity');
                    }),
                    'total_value' => $day->sum(function ($invoice) {
                        return $invoice->productInvoices->sum(function ($productInvoice) {
                            return $productInvoice->quantity * $productInvoice->product->sell_price; // Sử dụng giá bán
                        });
                    }),
                    'invoices' => $day->map(function ($invoice) {
                        return [
                            'invoice_id' => $invoice->id,
                            'created_at' => $invoice->created_at->format('Y-m-d H:i:s'),
                            'total_amount' => $invoice->total_amount,
                            'details' => $invoice->productInvoices->map(function ($productInvoice) {
                                return [
                                    'product_id' => $productInvoice->product->id,
                                    'quantity' => $productInvoice->quantity,
                                    'unit_price' => $productInvoice->product->sell_price, // Giá bán
                                    'total_price' => $productInvoice->quantity * $productInvoice->product->sell_price
                                ];
                            })
                        ];
                    })
                ];
            });

        return response()->json([
            'monthly_stats' => $monthlyStats,
            'daily_stats' => ['data' => $dailyStats->values()]
        ]);
    }

    public function inventoryStatsByExpiry(Request $request)
    {
        // Lấy categories_id từ request nếu có
        $categoryId = $request->input('categories_id');

        // Lấy tất cả các sản phẩm cùng với thông tin ngày hết hạn của chúng, bao gồm cả các sản phẩm đã bị xóa mềm (soft deleted)
        $query = Product::with(['expiries', 'category', 'location'])->withTrashed();

        // Nếu có categories_id trong request, thêm điều kiện lọc
        if ($categoryId) {
            $query->where('categories_id', $categoryId);
        }

        $products = $query->get()
            // Ánh xạ qua từng sản phẩm
            ->map(function ($product) {
                // Nếu sản phẩm không có thông tin ngày hết hạn
                if ($product->expiries->isEmpty()) {
                    return [
                        'product_id' => $product->id,
                        'product_name' => $product->product_name ?? 'N/A',
                        'category' => $product->category->name ?? 'N/A', // Lấy tên danh mục
                        'location' => $product->location->code ?? 'N/A',
                    ];
                } else {
                    // Nếu sản phẩm có thông tin ngày hết hạn
                    return [
                        'product_id' => $product->id,
                        'product_name' => $product->product_name ?? 'N/A',
                        'category' => $product->category->name ?? 'N/A', // Lấy tên danh mục
                        'location' => $product->location->code ?? 'N/A',
                        'expiries' => $product->expiries->map(function ($expiry) {
                            return [
                                'expiry_date' => $expiry->expiry_date, // Ngày hết hạn
                                'total_quantity' => $expiry->quantity_exp // Tổng số lượng
                            ];
                        }),
                        'total_quantity' => $product->expiries->sum('quantity_exp') // Tổng số lượng tồn kho của sản phẩm
                    ];
                }
            });

        // Tính tổng số tồn kho của tất cả sản phẩm
        $totalInventory = $products->sum('total_quantity');

        // Trả về kết quả dưới dạng JSON
        return response()->json([
            'products' => $products,
            'total_inventory' => $totalInventory
        ]);
    }

}
