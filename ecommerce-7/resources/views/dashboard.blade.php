<x-app-layout :title="'Dashboard'">
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-[#7A0C0C] leading-tight">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-12 bg-slate-50 min-h-[70vh]">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 grid gap-4">
            <div class="grid grid-cols-2 md:grid-cols-2 lg:grid-cols-4 px-2 lg:gap-4 gap-2">
                @foreach ($data as $index => $item)
                    <div class="w-full">
                        <div class="overflow-hidden shadow-sm rounded-lg p-6 {{ $item['color'] }} text-white">
                            <div class="flex items-center justify-between">
                                <p class="mt-2 text-3xl font-bold">{{ $item['value'] }}</p>
                                <span class="material-symbols-outlined text-[40px]">
                                    {{ $item['icon'] }}
                                </span>
                            </div>
                            <hr class="my-2">
                            <h3 class="text-lg font-medium">{{ $item['label'] }}</h3>

                        </div>
                    </div>
                @endforeach
            </div>
            <div class="p-6 bg-white overflow-hidden shadow-sm rounded-lg border border-[#E53935]/20">
                <h3 class="text-lg font-semibold text-gray-800 mb-4">Penjualan 7 hari</h3>
                <div style="max-width: 100%; height: 360px;">
                    <canvas id="salesChart"></canvas>
                </div>
            </div>
            <div class="p-6 bg-white overflow-hidden shadow-sm rounded-lg border border-[#E53935]/20">
                <h3 class="text-lg font-semibold text-gray-800 mb-4">Order Terbaru</h3>
                {{-- table for latest orders --}}
                <div class="overflow-x-auto">
                    <table class="min-w-[640px] w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    ID</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Order Number</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Total</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Status</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse ($latestOrders as $order)
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $order->id }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $order->order_number }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">Rp
                                        {{ number_format($order->total_amount, 0, ',', '.') }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                                        @if ($order->status == 'pending')
                                            <span
                                                class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">
                                                Pending
                                            </span>
                                        @elseif ($order->status == 'completed')
                                            <span
                                                class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                                Completed
                                            </span>
                                        @elseif ($order->status == 'cancelled')
                                            <span
                                                class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">
                                                Cancelled
                                            </span>
                                        @else
                                            <span
                                                class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800">
                                                {{ ucfirst($order->status) }}
                                            </span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4"
                                        class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-center">
                                        Tidak ada pesanan baru.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Data penjualan 7 hari dari controller
        document.addEventListener('DOMContentLoaded', function() {
            const chartData = @json($chartData);
            const canvas = document.getElementById('salesChart');

            if (!canvas || typeof Chart === 'undefined') {
                return;
            }

            new Chart(canvas.getContext('2d'), {
                type: 'line',
                data: {
                    labels: chartData.labels,
                    datasets: [{
                            label: 'Jumlah Order',
                            data: chartData.orders,
                            borderColor: 'rgba(245, 173, 78, 1)',
                            backgroundColor: 'rgba(245, 173, 78, 0.15)',
                            borderWidth: 2,
                            fill: true,
                            tension: 0.3,
                            pointRadius: 5,
                            pointBackgroundColor: 'rgba(245, 173, 78, 1)',
                            yAxisID: 'y'
                        },
                        {
                            label: 'Total Pendapatan (Ribu Rupiah)',
                            data: chartData.revenue,
                            borderColor: 'rgba(132, 180, 255, 1)',
                            backgroundColor: 'rgba(132, 180, 255, 0.15)',
                            borderWidth: 2,
                            fill: true,
                            tension: 0.3,
                            pointRadius: 5,
                            pointBackgroundColor: 'rgba(132, 180, 255, 1)',
                            yAxisID: 'y1'
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    interaction: {
                        mode: 'index',
                        intersect: false
                    },
                    plugins: {
                        legend: {
                            display: true,
                            position: 'top'
                        },
                        tooltip: {
                            enabled: true,
                            mode: 'index'
                        }
                    },
                    scales: {
                        x: {
                            display: true
                        },
                        y: {
                            type: 'linear',
                            display: true,
                            position: 'left',
                            title: {
                                display: true,
                                text: 'Jumlah Order'
                            }
                        },
                        y1: {
                            type: 'linear',
                            display: true,
                            position: 'right',
                            title: {
                                display: true,
                                text: 'Pendapatan (Ribu)'
                            },
                            grid: {
                                drawOnChartArea: false
                            }
                        }
                    }
                }
            });
        });
    </script>
</x-app-layout>
