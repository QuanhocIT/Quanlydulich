<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold text-gray-800">Tour Du Lịch</h2>
    </x-slot>
    <div class="max-w-7xl mx-auto py-8 px-4">
        {{-- Filters --}}
        <form method="GET" class="flex flex-wrap gap-3 mb-8">
            <select name="destination" class="border rounded-lg px-3 py-2 text-sm">
                <option value="">Tất cả điểm đến</option>
                @foreach($destinations as $d)
                    <option value="{{ $d->slug }}" @selected(request('destination')==$d->slug)>{{ $d->name }}</option>
                @endforeach
            </select>
            <select name="type" class="border rounded-lg px-3 py-2 text-sm">
                <option value="">Loại tour</option>
                <option value="domestic" @selected(request('type')=='domestic')>Trong nước</option>
                <option value="international" @selected(request('type')=='international')>Quốc tế</option>
            </select>
            <select name="duration" class="border rounded-lg px-3 py-2 text-sm">
                <option value="">Số ngày</option>
                <option value="1-3" @selected(request('duration')=='1-3')>1-3 ngày</option>
                <option value="4-7" @selected(request('duration')=='4-7')>4-7 ngày</option>
                <option value="8+" @selected(request('duration')=='8+')>8+ ngày</option>
            </select>
            <button type="submit" class="bg-blue-600 text-white px-5 py-2 rounded-lg text-sm hover:bg-blue-700">Lọc</button>
            <a href="{{ route('tours.index') }}" class="border px-5 py-2 rounded-lg text-sm hover:bg-gray-50">Xóa lọc</a>
        </form>

        @if($tours->count())
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            @foreach($tours as $tour)
            <a href="{{ route('tours.show', $tour->slug) }}" class="bg-white rounded-xl shadow hover:shadow-lg transition overflow-hidden group">
                <div class="h-44 bg-gradient-to-br from-blue-400 to-cyan-300 flex items-center justify-center text-5xl">🏝️</div>
                <div class="p-5">
                    <span class="text-xs text-blue-600 font-semibold">{{ $tour->destination->name }}</span>
                    <h3 class="font-bold text-gray-800 mt-1 group-hover:text-blue-600">{{ $tour->name }}</h3>
                    <p class="text-gray-500 text-sm mt-1 line-clamp-2">{{ $tour->description }}</p>
                    <div class="flex justify-between items-center mt-4">
                        <span class="text-blue-600 font-bold">{{ number_format($tour->price_sale ?? $tour->price) }}đ</span>
                        <span class="text-gray-400 text-sm">{{ $tour->duration_days }} ngày</span>
                    </div>
                </div>
            </a>
            @endforeach
        </div>
        <div class="mt-8">{{ $tours->links() }}</div>
        @else
        <p class="text-gray-500 text-center py-16">Không tìm thấy tour nào.</p>
        @endif
    </div>
</x-app-layout>
