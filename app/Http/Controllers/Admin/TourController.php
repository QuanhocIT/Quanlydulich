<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Destination;
use App\Models\Tour;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class TourController extends Controller
{
    public function index()
    {
        $tours = Tour::with('destination')->latest()->paginate(15);
        return view('admin.tours.index', compact('tours'));
    }

    public function create()
    {
        $destinations = Destination::where('is_active', true)->get();
        return view('admin.tours.create', compact('destinations'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'destination_id' => 'required|exists:destinations,id',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'itinerary' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'price_sale' => 'nullable|numeric|min:0',
            'duration_days' => 'required|integer|min:1',
            'max_guests' => 'required|integer|min:1',
            'difficulty' => 'required|in:easy,moderate,hard',
            'tour_type' => 'required|in:domestic,international',
            'is_active' => 'boolean',
            'is_featured' => 'boolean',
        ]);

        $validated['slug'] = Str::slug($validated['name']);
        $validated['is_active'] = $request->boolean('is_active');
        $validated['is_featured'] = $request->boolean('is_featured');

        Tour::create($validated);

        return redirect()->route('admin.tours.index')->with('success', 'Tour đã được tạo thành công.');
    }

    public function show(Tour $tour)
    {
        $tour->load('destination', 'bookings', 'reviews.user');
        return view('admin.tours.show', compact('tour'));
    }

    public function edit(Tour $tour)
    {
        $destinations = Destination::where('is_active', true)->get();
        return view('admin.tours.edit', compact('tour', 'destinations'));
    }

    public function update(Request $request, Tour $tour)
    {
        $validated = $request->validate([
            'destination_id' => 'required|exists:destinations,id',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'itinerary' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'price_sale' => 'nullable|numeric|min:0',
            'duration_days' => 'required|integer|min:1',
            'max_guests' => 'required|integer|min:1',
            'difficulty' => 'required|in:easy,moderate,hard',
            'tour_type' => 'required|in:domestic,international',
            'is_active' => 'boolean',
            'is_featured' => 'boolean',
        ]);

        $validated['is_active'] = $request->boolean('is_active');
        $validated['is_featured'] = $request->boolean('is_featured');

        $tour->update($validated);

        return redirect()->route('admin.tours.index')->with('success', 'Tour đã được cập nhật.');
    }

    public function destroy(Tour $tour)
    {
        $tour->delete();
        return redirect()->route('admin.tours.index')->with('success', 'Tour đã được xóa.');
    }
}
