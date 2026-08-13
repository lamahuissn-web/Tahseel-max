<?php

namespace Tests\Unit;

use App\Http\Controllers\Admin\ClientController;
use App\Models\Clients;
use App\Services\Sas4\Sas4Gateway;
use Illuminate\Http\Request;
use Mockery;
use ReflectionClass;
use ReflectionMethod;
use Tests\TestCase;

class ClientSasCreateSafetyTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_create_flow_performs_no_write_when_username_check_is_ambiguous(): void
    {
        foreach ([
            ['ok' => false, 'code' => 'unavailable'],
            ['ok' => false, 'code' => 'invalid_response'],
            ['ok' => true],
        ] as $ambiguousResult) {
            $gateway = Mockery::mock(Sas4Gateway::class);
            $gateway->shouldReceive('usernameExists')->once()->with('new-user')->andReturn($ambiguousResult);
            $gateway->shouldNotReceive('createAccount');
            $this->app->instance(Sas4Gateway::class, $gateway);

            $client = Mockery::mock(Clients::class)->makePartial();
            $client->name = 'Test Client';
            $client->shouldNotReceive('save');
            $request = Request::create('/', 'POST', [
                'sas4_mode' => 'create',
                'sas4_new_username' => 'new-user',
                'sas4_new_password' => 'secret',
                'sas4_new_profile' => '9',
            ]);

            $controller = (new ReflectionClass(ClientController::class))->newInstanceWithoutConstructor();
            $method = new ReflectionMethod(ClientController::class, 'handleSas4Operations');
            $method->setAccessible(true);
            $method->invoke($controller, $client, $request);
            $this->addToAssertionCount(1);

            Mockery::close();
        }
    }
}
