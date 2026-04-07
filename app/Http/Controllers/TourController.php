<?php

namespace App\Http\Controllers;

use App\Models\Destination;
use App\Models\Tour;
use Illuminate\Http\Request;

class TourController extends Controller
{
    public function index(Request $request)
    {
        $query = Tour::with('destination')
            ->where('is_active', true);

        if ($request->filled('destination')) {
            $query->whereHas('destination', fn ($q) => $q->where('slug', $request->destination));
        }

        if ($request->filled('type')) {
            $query->where('tour_type', $request->type);
        }

        if ($request->filled('duration')) {
            match ($request->duration) {
                '1-3' => $query->whereBetween('duration_days', [1, 3]),
                '4-7' => $query->whereBetween('duration_days', [4, 7]),
                '8+' => $query->where('duration_days', '>=', 8),
                default => null,
            };
        }

        if ($request->filled('price_max')) {
            $query->where('price', '<=', $request->price_max);
        }

        $tours = $query->latest()->paginate(12)->withQueryString();
        $destinations = Destination::where('is_active', true)->get();

        return view('tours.index', compact('tours', 'destinations'));
    }

    public function show(string $slug)
    {
        $tour = Tour::with(['destination', 'reviews.user'])
            ->where('slug', $slug)
            ->where('is_active', true)
            ->firstOrFail();

        $relatedTours = Tour::with('destination')
            ->where('destination_id', $tour->destination_id)
            ->where('id', '!=', $tour->id)
            ->where('is_active', true)
            ->take(3)
            ->get();

        return view('tours.show', compact('tour', 'relatedTours'));
    }
}
