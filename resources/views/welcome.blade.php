<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name') }} - Khám phá Việt Nam</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased bg-gray-50">

    {{-- Navbar --}}
    <nav class="bg-white shadow-sm sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16 items-center">
                <a href="{{ route('home') }}" class="text-xl font-bold text-blue-600">
                    🌏 {{ config('app.name') }}
                </a>
                <div class="hidden md:flex items-center gap-6">
                    <a href="{{ route('tours.index') }}" class="text-gray-600 hover:text-blue-600 font-medium">Tour Du Lịch</a>
                    <a href="{{ route('destinations.index') }}" class="text-gray-600 hover:text-blue-600 font-medium">Điểm Đến</a>
                    @auth
                        <a href="{{ route('dashboard') }}" class="text-gray-600 hover:text-blue-600 font-medium">Tài khoản</a>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700">Đăng xuất</button>
                        </form>
                    @else
                        <a href="{{ route('login') }}" class="text-gray-600 hover:text-blue-600 font-medium">Đăng nhập</a>
                        <a href="{{ route('register') }}" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700">Đăng ký</a>
                    @endauth
                </div>
            </div>
        </div>
    </nav>

    {{-- Hero --}}
    <section class="bg-gradient-to-r from-blue-700 to-cyan-500 text-white py-20 px-4">
        <div class="max-w-4xl mx-auto text-center">
            <h1 class="text-4xl md:text-6xl font-bold mb-4">Khám Phá Việt Nam Tươi Đẹp</h1>
            <p class="text-xl mb-8 opacity-90">Đặt tour du lịch dễ dàng — trải nghiệm đáng nhớ cùng chúng tôi</p>
            <a href="{{ route('tours.index') }}" class="bg-white text-blue-700 font-semibold px-8 py-3 rounded-full text-lg hover:bg-blue-50 transition">
                Xem tất cả tour
            </a>
        </div>
    </section>

    {{-- Featured Tours --}}
    <section class="max-w-7xl mx-auto px-4 py-16">
        <h2 class="text-3xl font-bold text-gray-800 mb-2">Tour Nổi Bật</h2>
        <p class="text-gray-500 mb-8">Những hành trình được yêu thích nhất</p>

        @if($featuredTours->count())
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach($featuredTours as $tour)
            <a href="{{ route('tours.show', $tour->slug) }}" class="bg-white rounded-xl shadow hover:shadow-lg transition overflow-hidden group">
                <div class="h-48 bg-gradient-to-br from-blue-400 to-cyan-300 flex items-center justify-center text-white text-5xl">
                    🏝️
                </div>
                <div class="p-5">
                    <span class="text-xs text-blue-600 font-semibold uppercase">{{ $tour->destination->name ?? '' }}</span>
                    <h3 class="font-bold text-gray-800 text-lg mt-1 group-hover:text-blue-600">{{ $tour->name }}</h3>
                    <p class="text-gray-500 text-sm mt-1 line-clamp-2">{{ $tour->description }}</p>
                    <div class="flex items-center justify-between mt-4">
                        <div>
                            @if($tour->price_sale)
                                <span class="text-gray-400 line-through text-sm">{{ number_format($tour->price) }}đ</span>
                                <span class="text-blue-600 font-bold text-lg block">{{ number_format($tour->price_sale) }}đ</span>
                            @else
                                <span class="text-blue-600 font-bold text-lg">{{ number_format($tour->price) }}đ</span>
                            @endif
                        </div>
                        <span class="text-gray-400 text-sm">{{ $tour->duration_days }} ngày</span>
                    </div>
                </div>
            </a>
            @endforeach
        </div>
        @else
        <p class="text-gray-500">Chưa có tour nào.</p>
        @endif

        <div class="text-center mt-10">
            <a href="{{ route('tours.index') }}" class="border border-blue-600 text-blue-600 px-8 py-3 rounded-full font-medium hover:bg-blue-600 hover:text-white transition">
                Xem tất cả tour →
            </a>
        </div>
    </section>

    {{-- Destinations --}}
    <section class="bg-gray-100 py-16">
        <div class="max-w-7xl mx-auto px-4">
            <h2 class="text-3xl font-bold text-gray-800 mb-2">Điểm Đến Phổ Biến</h2>
            <p class="text-gray-500 mb-8">Khám phá những địa danh nổi tiếng tại Việt Nam</p>

            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                @foreach($destinations as $dest)
                <a href="{{ route('destinations.show', $dest->slug) }}" class="bg-white rounded-xl p-5 text-center shadow hover:shadow-md transition group">
                    <div class="text-4xl mb-2">🗺️</div>
                    <h3 class="font-semibold text-gray-800 group-hover:text-blue-600">{{ $dest->name }}</h3>
                    <p class="text-gray-400 text-sm">{{ $dest->tours_count }} tour</p>
                </a>
                @endforeach
            </div>
        </div>
    </section>

    {{-- Footer --}}
    <footer class="bg-gray-800 text-gray-300 py-10 px-4">
        <div class="max-w-7xl mx-auto grid grid-cols-1 md:grid-cols-3 gap-8">
            <div>
                <h3 class="text-white font-bold text-lg mb-2">🌏 {{ config('app.name') }}</h3>
                <p class="text-sm">Chuyên cung cấp dịch vụ du lịch chất lượng cao trên toàn quốc.</p>
            </div>
            <div>
                <h4 class="text-white font-semibold mb-2">Liên kết nhanh</h4>
                <ul class="space-y-1 text-sm">
                    <li><a href="{{ route('tours.index') }}" class="hover:text-white">Tour du lịch</a></li>
                    <li><a href="{{ route('destinations.index') }}" class="hover:text-white">Điểm đến</a></li>
                    <li><a href="{{ route('login') }}" class="hover:text-white">Đăng nhập</a></li>
                </ul>
            </div>
            <div>
                <h4 class="text-white font-semibold mb-2">Liên hệ</h4>
                <p class="text-sm">Email: info@quanlydulich.vn</p>
                <p class="text-sm">Hotline: 1900 xxxx</p>
            </div>
        </div>
        <div class="text-center text-sm mt-8 border-t border-gray-700 pt-6">
            &copy; {{ date('Y') }} {{ config('app.name') }}. All rights reserved.
        </div>
    </footer>
</body>
</html>
