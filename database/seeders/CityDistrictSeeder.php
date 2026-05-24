<?php

namespace Database\Seeders;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CityDistrictSeeder extends Seeder
{
    public function run()
    {
        // Insert cities
        $cities = [
            [
                'city_name' => json_encode([
                    'en' => 'Cairo',
                    'ar' => 'القاهرة',
                ])
            ],
            [
                'city_name' => json_encode([
                    'en' => 'Alexandria',
                    'ar' => 'الإسكندرية',
                ])
            ],
            [
                'city_name' => json_encode([
                    'en' => 'Giza',
                    'ar' => 'الجيزة',
                ])
            ],
        ];

        foreach ($cities as $city) {
            $cityId = DB::table('city')->insertGetId($city);

            // Insert districts for each city
            $districts = [];
            if (json_decode($city['city_name'])->en == 'Cairo') {
                $districts = [
                    [
                        'city_id' => $cityId,
                        'name' => json_encode([
                            'en' => 'Maadi',
                            'ar' => 'المعادي',
                        ])
                    ],
                    [
                        'city_id' => $cityId,
                        'name' => json_encode([
                            'en' => 'Nasr City',
                            'ar' => 'مدينة نصر',
                        ])
                    ],
                ];
            } elseif (json_decode($city['city_name'])->en == 'Alexandria') {
                $districts = [
                    [
                        'city_id' => $cityId,
                        'name' => json_encode([
                            'en' => 'Sidi Gaber',
                            'ar' => 'سيدي جابر',
                        ])
                    ],
                    [
                        'city_id' => $cityId,
                        'name' => json_encode([
                            'en' => 'Montaza',
                            'ar' => 'المنتزه',
                        ])
                    ],
                ];
            } elseif (json_decode($city['city_name'])->en == 'Giza') {
                $districts = [
                    [
                        'city_id' => $cityId,
                        'name' => json_encode([
                            'en' => 'Dokki',
                            'ar' => 'الدقي',
                        ])
                    ],
                    [
                        'city_id' => $cityId,
                        'name' => json_encode([
                            'en' => 'Mohandessin',
                            'ar' => 'المهندسين',
                        ])
                    ],
                ];
            }

            DB::table('district')->insert($districts);
        }
    }
}

