<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class addStates extends Seeder {

    /**
     * Run the database seeds.
     */
    public function run(): void {
        //
        DB::table('maint_states')->insert([
            ['full' => 'Alabama', 'abbreviation' => 'AL'],
            ['abbreviation' => "AK", 'full' => "Alaska"],
            ['abbreviation' => "AZ", 'full' => "Arizona"],
            ['abbreviation' => "AR", 'full' => "Arkansas"],
            ['abbreviation' => "CA", 'full' => "California"],
            ['abbreviation' => "CO", 'full' => "Colorado"],
            ['abbreviation' => "CT", 'full' => "Connecticut"],
            ['abbreviation' => "DE", 'full' => "Delaware"],
            ['abbreviation' => "DC", 'full' => "District of Columbia"],
            ['abbreviation' => "FL", 'full' => "Florida"],
            ['abbreviation' => "GA", 'full' => "Georgia"],
            ['abbreviation' => "HI", 'full' => "Hawaii"],
            ['abbreviation' => "ID", 'full' => "Idaho"],
            ['abbreviation' => "IL", 'full' => "Illinois"],
            ['abbreviation' => "IN", 'full' => "Indiana"],
            ['abbreviation' => "IA", 'full' => "Iowa"],
            ['abbreviation' => "KS", 'full' => "Kansas"],
            ['abbreviation' => "KY", 'full' => "Kentucky"],
            ['abbreviation' => "LA", 'full' => "Louisiana"],
            ['abbreviation' => "ME", 'full' => "Maine"],
            ['abbreviation' => "MD", 'full' => "Maryland"],
            ['abbreviation' => "MA", 'full' => "Massachusetts"],
            ['abbreviation' => "MI", 'full' => "Michigan"],
            ['abbreviation' => "MN", 'full' => "Minnesota"],
            ['abbreviation' => "MS", 'full' => "Mississippi"],
            ['abbreviation' => "MO", 'full' => "Missouri"],
            ['abbreviation' => "MT", 'full' => "Montana"],
            ['abbreviation' => "NE", 'full' => "Nebraska"],
            ['abbreviation' => "NV", 'full' => "Nevada"],
            ['abbreviation' => "NH", 'full' => "New Hampshire"],
            ['abbreviation' => "NJ", 'full' => "New Jersey"],
            ['abbreviation' => "NM", 'full' => "New Mexico"],
            ['abbreviation' => "NY", 'full' => "New York"],
            ['abbreviation' => "NC", 'full' => "North Carolina"],
            ['abbreviation' => "ND", 'full' => "North Dakota"],
            ['abbreviation' => "OH", 'full' => "Ohio"],
            ['abbreviation' => "OK", 'full' => "Oklahoma"],
            ['abbreviation' => "OR", 'full' => "Oregon"],
            ['abbreviation' => "PA", 'full' => "Pennsylvania"],
            ['abbreviation' => "RI", 'full' => "Rhode Island"],
            ['abbreviation' => "SC", 'full' => "South Carolina"],
            ['abbreviation' => "SD", 'full' => "South Dakota"],
            ['abbreviation' => "TN", 'full' => "Tennessee"],
            ['abbreviation' => "TX", 'full' => "Texas"],
            ['abbreviation' => "UT", 'full' => "Utah"],
            ['abbreviation' => "VT", 'full' => "Vermont"],
            ['abbreviation' => "VA", 'full' => "Virginia"],
            ['abbreviation' => "WA", 'full' => "Washington"],
            ['abbreviation' => "WV", 'full' => "West Virginia"],
            ['abbreviation' => "WI", 'full' => "Wisconsin"],
            ['abbreviation' => "WY", 'full' => "Wyoming"],
        ]);
    }
}
