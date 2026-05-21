<?php

namespace Database\Seeders;

use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class OrderDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $userIds = User::pluck('id')->all();
        $products = Product::select('id', 'price')->get();

        if (empty($userIds) || $products->isEmpty()) {
            return;
        }

        $statuses = ['pending', 'processing', 'completed', 'cancelled'];

        for ($index = 1; $index <= 50; $index++) {
            $createdAt = Carbon::now()->subDays(random_int(0, 6))
                ->subHours(random_int(0, 23))
                ->subMinutes(random_int(0, 59));

            $selectedProducts = $products->shuffle()->take(random_int(1, 4));
            $items = [];
            $totalAmount = 0;

            foreach ($selectedProducts as $product) {
                $quantity = random_int(1, 3);
                $price = (int) $product->price;

                $items[] = [
                    'product_id' => $product->id,
                    'quantity' => $quantity,
                    'price' => $price,
                    'created_at' => $createdAt,
                    'updated_at' => $createdAt,
                ];

                $totalAmount += $price * $quantity;
            }

            DB::transaction(function () use ($index, $createdAt, $items, $totalAmount, $userIds, $statuses): void {
                $order = Order::create([
                    'order_number' => 'ORD-' . $createdAt->format('Ymd') . '-' . Str::upper(Str::random(6)) . '-' . str_pad((string) $index, 2, '0', STR_PAD_LEFT),
                    'status' => $statuses[array_rand($statuses)],
                    'shipping_address' => fake()->address(),
                    'total_amount' => $totalAmount,
                    'user_id' => $userIds[array_rand($userIds)],
                    'created_at' => $createdAt,
                    'updated_at' => $createdAt,
                ]);

                $order->items()->createMany($items);
            });
        }
    }
}
