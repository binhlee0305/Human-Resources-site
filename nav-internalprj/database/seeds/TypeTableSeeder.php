<?php

use Illuminate\Database\Seeder;

class TypeTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        \DB::table('types')->delete();
        
        \DB::table('types')->insert(array (
            0 => 
            array (
                'id' => 1,
                'type' => 'Staff',
            ),
            1 => 
            array (
                'id' => 2,
                'type' => 'Onsite',
            ),
            2 => 
            array (
                'id' => 3,
                'type' => 'Freelancer',
            ),
            3 => 
            array (
                'id' => 4,
                'type' => 'Internship',
            ),
            4 => 
            array (
                'id' => 5,
                'type' => 'Other',
            )
        ));
    }
}
