<x-app-layout :title="'Daftar Order'">
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-[#7A0C0C] leading-tight">
            {{ __('Daftar Order') }}
        </h2>
    </x-slot>
    <div class="py-5 bg-slate-50 min-h-[70vh]">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 grid gap-1">
            <div class="mb-[15px]">
                @include('layouts.success-error-msg')
            </div>
            <div class="p-6 bg-white overflow-hidden shadow-sm rounded-lg border border-[#E53935]/20">
                <h3 class="text-lg font-semibold text-gray-800 mb-4">Order Terbaru</h3>
                {{-- table for latest orders --}}
                <div class="overflow-x-auto">
                    <table class="min-w-[640px] w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Order Number</th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Total</th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Status</th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Tanggal</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse ($orders as $order)
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        <a href="{{ route('order.show', $order->order_number) }}"
                                            class="text-blue-500 hover:text-blue-700">
                                            {{ $order->order_number }}
                                        </a>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">Rp
                                        {{ number_format($order->total_amount, 0, ',', '.') }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm">

                                        <a href="#"
                                            @if (Auth::user()->role == 'admin') x-data=""
                                                x-on:click.prevent="$dispatch('open-modal', 'edit-order-status-{{ $order->id }}')" @endif
                                            class="inline-flex items-center px-2 py-1 rounded text-xs font-medium {{ $order->status == 'pending' ? 'bg-yellow-100 text-yellow-800' : ($order->status == 'processing' ? 'bg-blue-100 text-blue-800' : ($order->status == 'completed' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800')) }}">
                                            {{ ucfirst($order->status) }}
                                        </a>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        {{ $order->created_at->format('d M Y, H:i') }}</td>
                                </tr>
                                @if (Auth::user()->role == 'admin')
                                    @push('scripts')
                                        <x-modal name="edit-order-status-{{ $order->id }}" :title="'Update Status Order ' . $order->order_number">
                                            <form method="POST" action="{{ route('orders.updateStatus', $order) }}"
                                                class="p-4">
                                                @csrf
                                                <div class="mb-4">
                                                    <label for="status"
                                                        class="block text-sm font-medium text-gray-700">Status</label>
                                                    <select id="status" name="status" required
                                                        class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm focus:ring-[#E53935] focus:border-[#E53935] sm:text-sm">
                                                        <option value="pending"
                                                            {{ $order->status == 'pending' ? 'selected' : '' }}>Pending
                                                        </option>
                                                        <option value="processing"
                                                            {{ $order->status == 'processing' ? 'selected' : '' }}>
                                                            Processing
                                                        </option>
                                                        <option value="completed"
                                                            {{ $order->status == 'completed' ? 'selected' : '' }}>Completed
                                                        </option>
                                                        <option value="cancelled"
                                                            {{ $order->status == 'cancelled' ? 'selected' : '' }}>Cancelled
                                                        </option>
                                                    </select>
                                                </div>
                                                <div class="flex justify-end gap-2">
                                                    <button type="button"
                                                        class="px-4 py-2 bg-gray-300 text-gray-700 rounded hover:bg-gray-400"
                                                        x-on:click="$dispatch('close-modal', 'edit-order-status-{{ $order->id }}')">Batal</button>
                                                    <button type="submit"
                                                        class="px-4 py-2 bg-[#E53935] text-white rounded hover:bg-[#7A0C0C]">Simpan</button>
                                                </div>
                                            </form>
                                        </x-modal>
                                    @endpush
                                @endif
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
                    <div class="mt-4">
                        {{ $orders->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
