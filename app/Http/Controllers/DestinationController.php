<?php

namespace App\Http\Controllers;

use App\Models\Destination;
use Illuminate\Http\Request;

class DestinationController extends Controller
{
    public function index()
    {
        $destinations = Destination::where('is_active', true)
            ->withCount(['tours' => fn ($q) => $q->where('is_active', true)])
            ->get();

        return view('destinations.index', compact('destinations'));
    }

    public function show(string $slug)
    {
        $destination = Destination::where('slug', $slug)
            ->where('is_active', true)
            ->firstOrFail();

        $tours = $destination->tours()
            ->where('is_active', true)
            ->paginate(9);

        return view('destinations.show', compact('destination', 'tours'));
    }
}
