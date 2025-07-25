<?php

namespace Database\Seeders;
use Illuminate\Database\Seeder;
// use Database\Seeders\UserSeeder;
// use CategorySeeder;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
//  use BookingSeeder;

// use ContactMessageSeeder;
// use FavoriteSeeder;

// use NotificationSeeder;
 //use RestaurantSeeder;
// use ReviewSeeder;


class DatabaseSeeder extends Seeder {
    /**
    * Seed the application's database.
    */

    public function run() {
        $this->call( [
             //UserSeeder::class,
             CategorySeeder::class,
              GovernoratesSeeder::class,
             TripsSeeder::class,
             DaySeeder::class,
             ActivitySeeder::class,
             HotelSeeder::class,
             RestaurantSeeder::class,
             PlacesSeeder::class,
            // BookingSeeder::class,
            // FavoriteSeeder::class,
            // ReviewSeeder::class,
            // NotificationSeeder::class,
            // ContactMessageSeeder::class,
           
           // UserTripSeeder::class
        ] );
    }

}
