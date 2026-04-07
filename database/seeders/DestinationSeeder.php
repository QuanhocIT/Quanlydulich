<?php

namespace Database\Seeders;

use App\Models\Destination;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class DestinationSeeder extends Seeder
{
    public function run(): void
    {
        $destinations = [
            [
                'name' => 'Hà Nội',
                'location' => 'Miền Bắc',
                'description' => 'Thủ đô nghìn năm văn hiến với nhiều di tích lịch sử và ẩm thực phong phú.',
            ],
            [
                'name' => 'Hạ Long',
                'location' => 'Quảng Ninh, Miền Bắc',
                'description' => 'Di sản thiên nhiên thế giới với hàng nghìn đảo đá vôi hùng vĩ.',
            ],
            [
                'name' => 'Sapa',
                'location' => 'Lào Cai, Miền Bắc',
                'description' => 'Thị trấn mù sương với ruộng bậc thang ngoạn mục và văn hóa dân tộc đa dạng.',
            ],
            [
                'name' => 'Đà Nẵng',
                'location' => 'Miền Trung',
                'description' => 'Thành phố biển sôi động với bãi biển Mỹ Khê xinh đẹp và Bà Nà Hills.',
            ],
            [
                'name' => 'Hội An',
                'location' => 'Quảng Nam, Miền Trung',
                'description' => 'Phố cổ lãng mạn được UNESCO công nhận là di sản văn hóa thế giới.',
            ],
            [
                'name' => 'Huế',
                'location' => 'Thừa Thiên Huế, Miền Trung',
                'description' => 'Cố đô triều Nguyễn với nhiều cung đình, lăng tẩm và ẩm thực cung đình.',
            ],
            [
                'name' => 'Thành phố Hồ Chí Minh',
                'location' => 'Miền Nam',
                'description' => 'Thành phố năng động nhất Việt Nam với cuộc sống hiện đại và phong phú.',
            ],
            [
                'name' => 'Phú Quốc',
                'location' => 'Kiên Giang, Miền Nam',
                'description' => 'Đảo ngọc với bãi biển trắng tinh, nước biển xanh trong và hải sản tươi ngon.',
            ],
            [
                'name' => 'Đà Lạt',
                'location' => 'Lâm Đồng, Tây Nguyên',
                'description' => 'Thành phố ngàn hoa với khí hậu mát mẻ và cảnh quan đồi núi thơ mộng.',
            ],
            [
                'name' => 'Ninh Bình',
                'location' => 'Miền Bắc',
                'description' => 'Vùng đất cố đô với Tràng An, Tam Cốc và Cúc Phương huyền bí.',
            ],
        ];

        foreach ($destinations as $data) {
            Destination::create(array_merge($data, [
                'slug' => Str::slug($data['name']),
                'country' => 'Việt Nam',
                'is_active' => true,
            ]));
        }
    }
}
