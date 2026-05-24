<?php

namespace Database\Seeders;

use App\Models\Admin\Subscription;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class SubscriptionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $subscriptions = [
            [
                'name' => '10M 25$',
                'price' => 25,
                'description' => 'اشتراك إنترنت بسرعة 10 ميجا بسعر 25 دولار'
            ],
            [
                'name' => '6M 20$',
                'price' => 20,
                'description' => 'اشتراك إنترنت بسرعة 6 ميجا بسعر 20 دولار'
            ],
            [
                'name' => '5 خطوط 110$',
                'price' => 110,
                'description' => 'باقة 5 خطوط إنترنت بسعر 110 دولار'
            ],
            [
                'name' => '3M 15$',
                'price' => 15,
                'description' => 'اشتراك إنترنت بسرعة 3 ميجا بسعر 15 دولار'
            ],
            [
                'name' => '30$ 12M',
                'price' => 30,
                'description' => 'اشتراك إنترنت بسرعة 12 ميجا بسعر 30 دولار'
            ],
            [
                'name' => '35$ 14MB',
                'price' => 35,
                'description' => 'اشتراك إنترنت بسرعة 14 ميجا بسعر 35 دولار'
            ],
            [
                'name' => 'خطين صغار 40$',
                'price' => 40,
                'description' => 'باقة خطين إنترنت صغار بسعر 40 دولار'
            ],
            [
                'name' => '16MB 40$',
                'price' => 40,
                'description' => 'اشتراك إنترنت بسرعة 16 ميجا بسعر 40 دولار'
            ],
            [
                'name' => 'خطين واحد كبير 45$',
                'price' => 45,
                'description' => 'باقة خطين إنترنت (واحد كبير) بسعر 45 دولار'
            ],
            [
                'name' => '4 خطوط 75$',
                'price' => 75,
                'description' => 'باقة 4 خطوط إنترنت بسعر 75 دولار'
            ],
            [
                'name' => '3 خطوط 1200 60$',
                'price' => 60,
                'description' => 'باقة 3 خطوط إنترنت بسعر 60 دولار'
            ],
            [
                'name' => 'خط 50 $',
                'price' => 50,
                'description' => 'خط إنترنت واحد بسعر 50 دولار'
            ],
            [
                'name' => 'خطين كبار 65',
                'price' => 65,
                'description' => 'باقة خطين إنترنت كبار بسعر 65 دولار'
            ],
            [
                'name' => 'خطين 35 $',
                'price' => 35,
                'description' => 'باقة خطين إنترنت بسعر 35 دولار'
            ],
            [
                'name' => 'ستالايت 400',
                'price' => 400,
                'description' => 'اشتراك ستالايت بسعر 400 دولار'
            ],
            [
                'name' => 'ساتلايت 300',
                'price' => 300,
                'description' => 'اشتراك ساتلايت بسعر 300 دولار'
            ],
            [
                'name' => '20',
                'price' => 20,
                'description' => 'Standard 20 dollar subscription',
            ],
            // [
            //     'name' => 'صندوق',
            //     'price' => 0,
            //     'description' => 'Box/Container subscription',
            // ],
            // [
            //     'name' => 'سلفة 2',
            //     'price' => 100,
            //     'description' => 'Advance payment subscription type 2',
            // ],
            // [
            //     'name' => 'شاليهات صور',
            //     'price' => 100,
            //     'description' => 'Sour chalets subscription',
            // ],
            // [
            //     'name' => 'س',
            //     'price' => 50,
            //     'description' => 'Special subscription type S',
            // ],
            // [
            //     'name' => 'دين بلال',
            //     'price' => 200,
            //     'description' => 'Bilal debt subscription',
            // ],
        ];

        foreach ($subscriptions as $subscriptionData) {
            // Subscription::create($subscriptionData);
            Subscription::firstOrCreate(
                [
                    'name' => $subscriptionData['name'],
                    'price' => $subscriptionData['price']
                ],
                $subscriptionData
            );
        }
    }
}
