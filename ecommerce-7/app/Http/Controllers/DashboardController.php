<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Product;
use App\Models\ProductCategory;
use Illuminate\Support\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        $jumlahProduk = Product::count();
        $jumlahKategori = ProductCategory::count();
        $jumlahOrder = Order::count();
        $jumlahStock = Product::sum('stock');
        $jumlahKlik = Product::sum('clicks');
        $latestOrders = Order::latest()->take(5)->get();
        $data = [
            ['label' => 'Produk',
                'value' => $jumlahProduk,
                'icon' => 'inventory_2',
                'color' => '#8B0000',
            ],
            [
                'label' => 'Kategori',
                'value' => $jumlahKategori,
                'icon' => 'category',
                'color' => '#16A34A',
            ],
            [
                'label' => 'Pesanan',
                'value' => $jumlahOrder,
                'icon' => 'order_approve',
                'color' => '#D4AF37',
            ],
            [
                'label' => 'Klik',
                'value' => $jumlahKlik,
                'icon' => 'touch_app',
                'color' => '#2563EB',
            ],
            [
                'label' => 'Stock',
                'value' => $jumlahStock,
                'icon' => 'inventory_2',
                'color' => '#7C3AED',
            ],
        ];
        $chartData = $this->orderData();
        return view('dashboard', compact('data', 'chartData', 'latestOrders'));
    }

    // Array Data dummy untuk grafik penjualan per minggu (jumlah order dan total pendapatan)
    public static function orderData()
    {
        $date_labels = [
            Carbon::now()->subDays(6)->format('Y-m-d'),
            Carbon::now()->subDays(5)->format('Y-m-d'),
            Carbon::now()->subDays(4)->format('Y-m-d'),
            Carbon::now()->subDays(3)->format('Y-m-d'),
            Carbon::now()->subDays(2)->format('Y-m-d'),
            Carbon::now()->subDays(1)->format('Y-m-d'),
            Carbon::now()->format('Y-m-d'),
        ];
        //grab data order dari database, lalu group by tanggal dan hitung jumlah order dan total pendapatan 
        $orders_data = Order::selectRaw('DATE(created_at) as date, COUNT(*) as orders, SUM(total_amount) as revenue')
            ->groupBy('date')
            ->orderBy('date', 'asc')
            ->get();
        
        $total_orders = [];
        $total_revenue = [];
        foreach ($date_labels as $date) {
            $order = $orders_data->firstWhere('date', $date);
            $total_orders[] = $order ? (int) $order->orders : 0;
            $total_revenue[] = $order ? (int) $order->revenue : 0;
        }
        return [
            'labels' => $date_labels,
            'orders' => $total_orders,
            'revenue' => $total_revenue
        ];
    }
}
