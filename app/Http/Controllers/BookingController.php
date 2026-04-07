<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Tour;
use Illuminate\Http\Request;

class BookingController extends Controller
{
    public function index()
    {
        $bookings = Booking::with('tour')
            ->where('user_id', auth()->id())
            ->latest()
            ->paginate(10);

        return view('bookings.index', compact('bookings'));
    }

    public function create(string $slug)
    {
        $tour = Tour::where('slug', $slug)->where('is_active', true)->firstOrFail();

        return view('bookings.create', compact('tour'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'tour_id' => 'required|exists:tours,id',
            'departure_date' => 'required|date|after:today',
            'num_adults' => 'required|integer|min:1|max:20',
            'num_children' => 'required|integer|min:0|max:10',
            'contact_name' => 'required|string|max:255',
            'contact_email' => 'required|email|max:255',
            'contact_phone' => 'required|string|max:20',
            'special_requests' => 'nullable|string|max:1000',
        ]);

        $tour = Tour::findOrFail($validated['tour_id']);
        $price = $tour->price_sale ?? $tour->price;
        $totalPrice = $price * $validated['num_adults'] + ($price * 0.7 * $validated['num_children']);

        $booking = Booking::create(array_merge($validated, [
            'user_id' => auth()->id(),
            'total_price' => $totalPrice,
            'status' => 'pending',
            'payment_status' => 'unpaid',
        ]));

        return redirect()->route('bookings.show', $booking)
            ->with('success', 'Đặt tour thành công! Mã đặt tour: ' . $booking->booking_code);
    }

    public function show(Booking $booking)
    {
        abort_unless($booking->user_id === auth()->id(), 403);

        $booking->load('tour.destination');

        return view('bookings.show', compact('booking'));
    }

    public function cancel(Booking $booking)
    {
        abort_unless($booking->user_id === auth()->id(), 403);
        abort_unless(in_array($booking->status, ['pending', 'confirmed']), 422, 'Không thể hủy tour này.');

        $booking->update(['status' => 'cancelled']);

        return back()->with('success', 'Đã hủy đặt tour thành công.');
    }
}
