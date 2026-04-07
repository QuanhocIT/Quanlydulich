<?php

namespace Database\Seeders;

use App\Models\Destination;
use App\Models\Tour;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class TourSeeder extends Seeder
{
    public function run(): void
    {
        $halong = Destination::where('slug', 'ha-long')->first();
        $sapa = Destination::where('slug', 'sapa')->first();
        $hoian = Destination::where('slug', 'hoi-an')->first();
        $phuquoc = Destination::where('slug', 'phu-quoc')->first();
        $dalat = Destination::where('slug', 'da-lat')->first();

        $tours = [
            [
                'destination_id' => $halong?->id ?? 1,
                'name' => 'Tour Hạ Long 2 ngày 1 đêm',
                'description' => 'Khám phá vịnh Hạ Long kỳ vĩ với chuyến du thuyền 2 ngày 1 đêm, tham quan hang động, kayaking và ngắm hoàng hôn trên biển.',
                'price' => 2500000,
                'price_sale' => 2200000,
                'duration_days' => 2,
                'max_guests' => 30,
                'difficulty' => 'easy',
                'tour_type' => 'domestic',
                'is_featured' => true,
            ],
            [
                'destination_id' => $halong?->id ?? 1,
                'name' => 'Tour Hạ Long 3 ngày 2 đêm Cao Cấp',
                'description' => 'Trải nghiệm đẳng cấp trên du thuyền 5 sao tại vịnh Hạ Long, bao gồm bữa ăn theo phong cách hải sản cao cấp và spa trên biển.',
                'price' => 5500000,
                'duration_days' => 3,
                'max_guests' => 20,
                'difficulty' => 'easy',
                'tour_type' => 'domestic',
                'is_featured' => true,
            ],
            [
                'destination_id' => $sapa?->id ?? 2,
                'name' => 'Tour Sapa Trekking 3 ngày 2 đêm',
                'description' => 'Chinh phục ruộng bậc thang Mù Cang Chải, thăm bản làng dân tộc H\'Mông và Dao, leo đỉnh Fansipan huyền thoại.',
                'price' => 3200000,
                'price_sale' => 2900000,
                'duration_days' => 3,
                'max_guests' => 15,
                'difficulty' => 'hard',
                'tour_type' => 'domestic',
                'is_featured' => true,
            ],
            [
                'destination_id' => $hoian?->id ?? 3,
                'name' => 'Tour Đà Nẵng - Hội An 4 ngày 3 đêm',
                'description' => 'Khám phá vẻ đẹp miền Trung: bán đảo Sơn Trà, Ngũ Hành Sơn, phố cổ Hội An, làng gốm Thanh Hà và tắm biển Cửa Đại.',
                'price' => 4200000,
                'duration_days' => 4,
                'max_guests' => 25,
                'difficulty' => 'easy',
                'tour_type' => 'domestic',
            ],
            [
                'destination_id' => $phuquoc?->id ?? 4,
                'name' => 'Tour Phú Quốc 3 ngày 2 đêm',
                'description' => 'Khám phá đảo ngọc Phú Quốc: bãi Sao, vườn tiêu, làng chài Hàm Ninh, snorkeling ngắm san hô và hoàng hôn tuyệt đẹp.',
                'price' => 4800000,
                'price_sale' => 4500000,
                'duration_days' => 3,
                'max_guests' => 20,
                'difficulty' => 'easy',
                'tour_type' => 'domestic',
                'is_featured' => true,
            ],
            [
                'destination_id' => $dalat?->id ?? 5,
                'name' => 'Tour Đà Lạt Lãng Mạn 3 ngày 2 đêm',
                'description' => 'Dạo chơi thành phố ngàn hoa: vườn hoa, hồ Xuân Hương, thác Datanla, làng hoa Vạn Thành và thưởng thức cà phê Đà Lạt.',
                'price' => 2800000,
                'duration_days' => 3,
                'max_guests' => 25,
                'difficulty' => 'easy',
                'tour_type' => 'domestic',
            ],
        ];

        foreach ($tours as $data) {
            Tour::create(array_merge($data, [
                'slug' => Str::slug($data['name']),
                'is_active' => true,
                'is_featured' => $data['is_featured'] ?? false,
            ]));
        }
    }
}
