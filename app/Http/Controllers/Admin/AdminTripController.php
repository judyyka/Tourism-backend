<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Transportation;
use Illuminate\Http\Request;
use App\Models\Trip;
use App\Models\Hotel;
use App\Models\Category;
use App\Models\governorates;
use App\Models\TripImage;
use App\Models\TourGuide;
use App\Models\Place;
use App\Models\Restaurant;
use App\Models\Activity;




class AdminTripController extends Controller
{
    public function dashboard() {
    $trips = Trip::with([
        'hotel','category','governorate','TourGuide','transportation','room','images','days.activities','days.restaurants','days.places'
    ])->get();

    return view('admin_trips.dashboard', compact('trips'));
}

public function destroy($id){
    $trip = Trip::findOrFail($id);
    $trip->delete();
    return redirect()->route('dashboard')->with('success','Trip deleted successfully!');
}


public function createStep1() {
        return view('admin_trips.steps.step1');
    }

    public function storeStep1(Request $request) {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'price' => 'required|numeric',
            'count_days' => 'required|integer|min:1',
            'start_date' => 'required|date',
            'image' => 'required|image|max:2048',
            'trip_images.*' => 'nullable|image|max:2048',
        ]);

        $data = $request->only(['name','description','price','count_days','start_date']);

        if ($request->hasFile('image')) {
            $img = $request->file('image');
            $name = time().'_'.$img->getClientOriginalName();
            $img->storeAs('trips', $name, 'public');
            $data['image'] = $name;
        }

        if ($request->hasFile('trip_images')) {
            $additionalImages = [];
            foreach ($request->file('trip_images') as $img) {
                $name = time().'_'.$img->getClientOriginalName();
                $img->storeAs('trips', $name, 'public');
                $additionalImages[] = $name;
            }
            $data['trip_images'] = $additionalImages;
        }

        session(['trip_step1' => $data]);

        return redirect()->route('trips.create.step2');
    }

    // ===== STEP 2 =====
    public function createStep2() {
        $hotels = Hotel::with('rooms')->get();
        $categories = Category::all();
        $governorates = Governorates::all();
        return view('admin_trips.steps.step2', compact('hotels','categories','governorates'));
    }

    public function storeStep2(Request $request) {
        $request->validate([
            'hotel_id' => 'required|exists:hotels,id',
            'room_id' => 'required|exists:hotel__rooms,id',
            'category_id' => 'required|exists:categories,id',
            'governorate_id' => 'required|exists:governorates,id',
        ]);
        session(['trip_step2' => $request->only(['hotel_id','room_id','category_id','governorate_id'])]);
        return redirect()->route('trips.create.step3');
    }

    // ===== STEP 3 =====
    public function createStep3() {
        $tourGuides = TourGuide::all();
        $transports = Transportation::all();
        return view('admin_trips.steps.step3', compact('tourGuides','transports'));
    }

    public function storeStep3(Request $request) {
        $request->validate([
            'guide_id' => 'nullable|exists:tour_guides,id',
            'transportation_id' => 'required|exists:transportations,id',
        ]);
        session(['trip_step3' => $request->only(['guide_id','transportation_id'])]);
        return redirect()->route('trips.create.step4');
    }

    // ===== STEP 4 & FINAL =====
    public function createStep4() {
        $places = Place::all();
        $restaurants = Restaurant::all();
        $activities = Activity::all();
        return view('admin_trips.steps.step4', compact('places','restaurants','activities'));
    }

    public function storeFinal(Request $request) {
        $request->validate([
            'days' => 'required|array|min:1',
            'days.*.name' => 'required|string',
            'days.*.places' => 'nullable|array',
            'days.*.restaurants' => 'nullable|array',
            'days.*.activities' => 'nullable|array',
        ]);

        session(['trip_step4' => $request->only('days')]);

        $step1 = session('trip_step1');
        $step2 = session('trip_step2');
        $step3 = session('trip_step3');
        $step4 = session('trip_step4');

        if (!$step1 || !$step2 || !$step3 || !$step4) {
            return back()->with('error', 'Incomplete trip data!');
        }

        $trip = Trip::create([
            'name' => $step1['name'],
            'description' => $step1['description'],
            'price' => $step1['price'],
            'count_days' => $step1['count_days'],
            'start_date' => $step1['start_date'],
            'image' => $step1['image'] ?? null,
            'hotel_id' => $step2['hotel_id'],
            'category_id' => $step2['category_id'],
            'governorate_id' => $step2['governorate_id'],
            'guide_id' => $step3['guide_id'] ?? null,
            'transportation_id' => $step3['transportation_id'] ?? null,
        ]);

        // الصور الإضافية
        foreach ($step1['trip_images'] ?? [] as $img) {
            TripImage::create(['trip_id'=>$trip->id, 'image'=>$img]);
        }

        // الأيام
        foreach ($step4['days'] as $day) {
            $d = $trip->days()->create([
                'name' => $day['name'],
                'date' => $day['date'] ?? now(),
                'tripable_id' => $trip->id,
                'tripable_type' => Trip::class,
            ]);
            if (!empty($day['activities'])) $d->activities()->attach($day['activities']);
            if (!empty($day['restaurants'])) $d->restaurants()->attach($day['restaurants']);
            if (!empty($day['places'])) $d->places()->attach($day['places']);
        }

        session()->forget(['trip_step1','trip_step2','trip_step3','trip_step4']);

        return redirect()->route('dashboard')->with('success','Trip created successfully!');
    }
}
