<?php

namespace Tests\Unit;

use App\Models\Clients;
use App\Services\Sas4\ClientSasStatusService;
use App\Services\Sas4\Sas4ApiService;
use App\Services\Sas4\Sas4Gateway;
use Mockery;
use Tests\TestCase;

class Sas4GatewayTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_statuses_uses_configuration_without_an_extra_transport_interaction(): void
    {
        $this->setConfiguredValues();
        $clients = [$this->client(1, 'one')];
        $expected = [1 => ['client_id' => 1, 'sas_username' => 'one', 'status' => 'online']];
        $transport = Mockery::mock(Sas4ApiService::class);
        $transport->shouldNotReceive('isConfigured');
        $statuses = Mockery::mock(ClientSasStatusService::class);
        $statuses->shouldReceive('resolve')->once()->with($clients)->andReturn($expected);

        $this->assertSame(['ok' => true, 'data' => $expected], (new Sas4Gateway($transport, $statuses))->statuses($clients));
    }

    public function test_statuses_delegates_once(): void
    {
        $this->setConfiguredValues();
        $clients = [$this->client(1, 'one'), $this->client(2, null)];
        $expected = [1 => ['client_id' => 1, 'sas_username' => 'one', 'status' => 'online']];
        $transport = Mockery::mock(Sas4ApiService::class);
        $transport->shouldNotReceive('isConfigured');
        $statuses = Mockery::mock(ClientSasStatusService::class);
        $statuses->shouldReceive('resolve')->once()->with($clients)->andReturn($expected);

        $this->assertSame(['ok' => true, 'data' => $expected], (new Sas4Gateway($transport, $statuses))->statuses($clients));
    }

    public function test_unlinked_reads_fail_closed_without_provider_reads(): void
    {
        $transport = $this->configuredTransport(3);
        $transport->shouldNotReceive('getUserFullInfo');
        $transport->shouldNotReceive('getTrafficAndSessions');
        $transport->shouldNotReceive('getDailyTrafficReport');
        $gateway = $this->gateway($transport);
        $client = $this->client(7, '   ');

        $this->assertSame(['ok' => false, 'code' => 'unlinked'], $gateway->clientInfo($client));
        $this->assertSame(['ok' => false, 'code' => 'unlinked'], $gateway->clientTraffic($client));
        $this->assertSame(['ok' => false, 'code' => 'unlinked'], $gateway->clientDailyTraffic($client, 8, 2026));
    }

    public function test_unavailable_linked_reads_fail_closed_without_becoming_offline_or_not_found(): void
    {
        $transport = $this->configuredTransport(3);
        $transport->shouldReceive('getUserFullInfo')->once()->andReturn(null);
        $transport->shouldReceive('getTrafficAndSessions')->once()->andReturn(null);
        $transport->shouldReceive('getDailyTrafficReport')->once()->andReturn(null);
        $gateway = $this->gateway($transport);
        $client = $this->client(7, 'exact');

        $this->assertSame(['ok' => false, 'code' => 'unavailable'], $gateway->clientInfo($client));
        $this->assertSame(['ok' => false, 'code' => 'unavailable'], $gateway->clientTraffic($client));
        $this->assertSame(['ok' => false, 'code' => 'unavailable'], $gateway->clientDailyTraffic($client, 8, 2026));
    }

    public function test_missing_configuration_precedes_unlinked_and_prevents_all_transport_operations(): void
    {
        $transport = Mockery::mock(Sas4ApiService::class);
        $transport->shouldReceive('isConfigured')->times(9)->andReturn(false);
        foreach (['searchUsers', 'getProfiles', 'getUserFullInfo', 'getTrafficAndSessions', 'getDailyTrafficReport', 'usernameExists', 'createUser', 'getUserByUsername', 'enableUser'] as $method) {
            $transport->shouldNotReceive($method);
        }
        $statuses = Mockery::mock(ClientSasStatusService::class);
        $statuses->shouldNotReceive('resolve');
        $gateway = new Sas4Gateway($transport, $statuses);
        $client = $this->client(8, null);

        config()->set('sas4.password', null);
        $this->assertNotConfigured($gateway->statuses([$client]));
        $this->assertNotConfigured($gateway->searchUsers('x'));
        $this->assertNotConfigured($gateway->profiles());
        $this->assertNotConfigured($gateway->clientInfo($client));
        $this->assertNotConfigured($gateway->clientTraffic($client));
        $this->assertNotConfigured($gateway->clientDailyTraffic($client, 8, 2026));
        $this->assertNotConfigured($gateway->usernameExists('x'));
        $this->assertNotConfigured($gateway->createAccount('x', 'p', 1));
        $this->assertNotConfigured($gateway->control($client, 'enable'));
        $this->assertFalse($gateway->configured());
    }

    public function test_invalid_control_performs_no_provider_calls(): void
    {
        $transport = Mockery::mock(Sas4ApiService::class);
        $transport->shouldNotReceive('isConfigured');
        $transport->shouldNotReceive('getUserByUsername');
        $transport->shouldNotReceive('enableUser');

        $this->assertSame(['ok' => false, 'code' => 'invalid_action'], $this->gateway($transport)->control($this->client(1, 'exact'), 'delete'));
    }

    public function test_exact_identity_mismatch_or_missing_identity_prevents_write(): void
    {
        foreach ([['username' => 'other'], []] as $identity) {
            $transport = $this->configuredTransport();
            $transport->shouldReceive('getUserByUsername')->once()->with('exact')->andReturn(['data' => ['id' => 44] + $identity]);
            $transport->shouldNotReceive('enableUser');

            $this->assertSame(['ok' => false, 'code' => 'not_found'], $this->gateway($transport)->control($this->client(1, 'exact'), 'enable'));
            Mockery::close();
        }
    }

    public function test_each_control_uses_at_most_one_write_and_disconnect_verification_is_not_applicable(): void
    {
        $cases = [
            ['enable', null, 'enableUser', ['enabled' => 1], 'verified'],
            ['disable', null, 'disableUser', ['enabled' => 0], 'verified'],
            ['disconnect', null, 'disconnectUser', null, 'not_applicable'],
            ['change_profile', 9, 'changeProfile', ['profile_id' => 9], 'verified'],
        ];
        foreach ($cases as [$action, $profile, $writeMethod, $after, $verification]) {
            $transport = $this->configuredTransport();
            $before = ['data' => ['id' => 44, 'username' => 'exact']];
            $returns = [$before];
            if ($after !== null) {
                $returns[] = ['data' => ['id' => 44, 'username' => 'exact'] + $after];
            }
            $transport->shouldReceive('getUserByUsername')->times(count($returns))->with('exact')->andReturn(...$returns);
            $transport->shouldReceive($writeMethod)->once()->andReturn(['status' => 200]);

            $result = $this->gateway($transport)->control($this->client(12, 'exact'), $action, $profile);

            $this->assertTrue($result['ok'], $action);
            $this->assertSame($verification, $result['verification'], $action);
            Mockery::close();
        }
    }

    public function test_username_absence_is_not_proven_by_a_full_first_page_with_more_provider_pages(): void
    {
        $transport = $this->configuredTransport();
        $transport->shouldReceive('searchUsers')->once()->with('exact', 1, 100)->andReturn([
            'data' => $this->providerUsers(1, 100),
            'total' => 101,
            'current_page' => 1,
            'last_page' => 2,
        ]);

        $this->assertSame(
            ['ok' => false, 'code' => 'invalid_response'],
            $this->gateway($transport)->usernameExists('exact'),
        );
    }

    public function test_full_page_without_completeness_metadata_fails_closed(): void
    {
        $transport = $this->configuredTransport();
        $transport->shouldReceive('searchUsers')->once()->with('exact', 1, 100)->andReturn([
            'data' => $this->providerUsers(1, 100),
        ]);

        $this->assertSame(
            ['ok' => false, 'code' => 'invalid_response'],
            $this->gateway($transport)->usernameExists('exact'),
        );
    }

    public function test_complete_full_page_metadata_can_prove_username_absence(): void
    {
        $transport = $this->configuredTransport();
        $transport->shouldReceive('searchUsers')->once()->with('exact', 1, 100)->andReturn([
            'data' => $this->providerUsers(1, 100),
            'total' => 100,
            'current_page' => 1,
            'last_page' => 1,
        ]);

        $this->assertSame(
            ['ok' => true, 'data' => false],
            $this->gateway($transport)->usernameExists('exact'),
        );
    }

    public function test_username_absence_fails_closed_for_short_pages_with_malformed_or_contradictory_pagination_metadata(): void
    {
        $cases = [
            ['total' => 1],
            ['total' => '1', 'current_page' => 1, 'last_page' => 1],
            ['total' => 101, 'current_page' => 1, 'last_page' => 2],
            ['total' => 1, 'current_page' => 2, 'last_page' => 1],
            ['total' => 0, 'current_page' => 1, 'last_page' => 1],
        ];

        foreach ($cases as $metadata) {
            $transport = $this->configuredTransport();
            $transport->shouldReceive('searchUsers')->once()->with('exact', 1, 100)->andReturn([
                'data' => [['id' => 1, 'username' => 'other']],
            ] + $metadata);

            $this->assertSame(
                ['ok' => false, 'code' => 'invalid_response'],
                $this->gateway($transport)->usernameExists('exact'),
            );
            Mockery::close();
        }
    }

    public function test_username_absence_requires_a_valid_provider_search_response(): void
    {
        $cases = [
            [null, ['ok' => false, 'code' => 'unavailable']],
            [[], ['ok' => false, 'code' => 'invalid_response']],
            [['data' => 'not-a-list'], ['ok' => false, 'code' => 'invalid_response']],
            [['data' => [['id' => 1]]], ['ok' => false, 'code' => 'invalid_response']],
            [['data' => [['id' => 1, 'username' => 'other']]], ['ok' => true, 'data' => false]],
            [['data' => [['id' => 1, 'username' => ' ExAcT ']]], ['ok' => true, 'data' => true]],
        ];

        foreach ($cases as [$providerResponse, $expected]) {
            $transport = $this->configuredTransport();
            $transport->shouldReceive('searchUsers')->once()->with('exact', 1, 100)->andReturn($providerResponse);
            $transport->shouldNotReceive('usernameExists');

            $this->assertSame($expected, $this->gateway($transport)->usernameExists(' exact '));
            Mockery::close();
        }
    }

    public function test_post_write_verification_rejects_a_different_account_with_expected_state(): void
    {
        foreach ([
            ['id' => 45, 'username' => 'exact', 'enabled' => 1],
            ['id' => 44, 'username' => 'other', 'enabled' => 1],
        ] as $differentAccount) {
            $transport = $this->configuredTransport();
            $transport->shouldReceive('getUserByUsername')->twice()->with('exact')->andReturn(
                ['data' => ['id' => 44, 'username' => ' Exact ']],
                ['data' => $differentAccount],
            );
            $transport->shouldReceive('enableUser')->once()->with(44)->andReturn(['status' => 200]);

            $result = $this->gateway($transport)->control($this->client(1, ' exact '), 'enable');

            $this->assertFalse($result['ok']);
            $this->assertSame('verification_failed', $result['code']);
            $this->assertSame('mismatch', $result['verification']);
            Mockery::close();
        }
    }

    public function test_profile_with_expiration_fails_when_expiration_is_wrong_or_not_exposed(): void
    {
        foreach ([
            ['expiration' => '2026-09-02 00:00:00'],
            [],
        ] as $expirationFields) {
            $transport = $this->configuredTransport();
            $transport->shouldReceive('getUserByUsername')->twice()->with('exact')->andReturn(
                ['data' => ['id' => 44, 'username' => 'exact']],
                ['data' => ['id' => 44, 'username' => 'exact', 'profile_id' => 9] + $expirationFields],
            );
            $transport->shouldReceive('changeProfileAndExpiration')->once()->with(44, 9, '2026-09-01')->andReturn(['status' => 200]);
            $transport->shouldNotReceive('changeProfile');

            $result = $this->gateway($transport)->control($this->client(1, 'exact'), 'change_profile', 9, '2026-09-01');

            $this->assertFalse($result['ok']);
            $this->assertSame('verification_failed', $result['code']);
            $this->assertSame('mismatch', $result['verification']);
            Mockery::close();
        }
    }

    public function test_profile_with_expiration_normalizes_provider_date_formats(): void
    {
        foreach (['2026-09-01', '2026-09-01 00:00:00', '2026-09-01T00:00:00.000Z'] as $providerExpiration) {
            $transport = $this->configuredTransport();
            $transport->shouldReceive('getUserByUsername')->twice()->with('exact')->andReturn(
                ['data' => ['id' => 44, 'username' => 'exact']],
                ['data' => ['id' => 44, 'username' => 'exact', 'profile_id' => 9, 'expiration' => $providerExpiration]],
            );
            $transport->shouldReceive('changeProfileAndExpiration')->once()->with(44, 9, '2026-09-01')->andReturn(['status' => 200]);

            $result = $this->gateway($transport)->control($this->client(1, 'exact'), 'change_profile', 9, '2026-09-01');

            $this->assertTrue($result['ok'], $providerExpiration);
            Mockery::close();
        }
    }

    public function test_profile_with_expiration_uses_one_combined_write(): void
    {
        $transport = $this->configuredTransport();
        $transport->shouldReceive('getUserByUsername')->twice()->with('exact')->andReturn(
            ['data' => ['id' => 44, 'username' => 'exact']],
            ['data' => ['id' => 44, 'username' => 'exact', 'profile_id' => 9, 'expiration' => '2026-09-01 00:00:00']],
        );
        $transport->shouldReceive('changeProfileAndExpiration')->once()->with(44, 9, '2026-09-01')->andReturn(['status' => 200]);
        $transport->shouldNotReceive('changeProfile');

        $this->assertTrue($this->gateway($transport)->control($this->client(1, 'exact'), 'change_profile', 9, '2026-09-01')['ok']);
    }

    public function test_write_failure_is_not_retried_and_is_not_verified(): void
    {
        foreach ([
            ['enable', null, 'enableUser'],
            ['disable', null, 'disableUser'],
            ['disconnect', null, 'disconnectUser'],
            ['change_profile', 9, 'changeProfile'],
        ] as [$action, $profile, $writeMethod]) {
            $transport = $this->configuredTransport();
            $transport->shouldReceive('getUserByUsername')->once()->with('exact')->andReturn(['data' => ['id' => 44, 'username' => 'exact']]);
            $transport->shouldReceive($writeMethod)->once()->andReturn(null);

            $result = $this->gateway($transport)->control($this->client(12, 'exact'), $action, $profile);

            $this->assertFalse($result['ok'], $action);
            $this->assertSame('write_failed', $result['code'], $action);
            Mockery::close();
        }
    }

    public function test_verifiable_controls_return_verification_failed_for_mismatch_or_unavailable(): void
    {
        foreach (['enable', 'disable', 'change_profile'] as $action) {
            foreach (['mismatch', 'unavailable'] as $failure) {
                $profile = $action === 'change_profile' ? 9 : null;
                $writeMethod = ['enable' => 'enableUser', 'disable' => 'disableUser', 'change_profile' => 'changeProfile'][$action];
                $mismatch = ['data' => ['id' => 44, 'username' => 'exact', 'enabled' => $action === 'enable' ? 0 : 1, 'profile_id' => 8]];
                $transport = $this->configuredTransport();
                $transport->shouldReceive('getUserByUsername')->twice()->with('exact')->andReturn(
                    ['data' => ['id' => 44, 'username' => 'exact']],
                    $failure === 'unavailable' ? null : $mismatch,
                );
                $transport->shouldReceive($writeMethod)->once()->andReturn(['status' => 200]);

                $result = $this->gateway($transport)->control($this->client(12, 'exact'), $action, $profile);

                $this->assertFalse($result['ok'], "$action $failure");
                $this->assertSame('verification_failed', $result['code'], "$action $failure");
                $this->assertSame($failure, $result['verification'], "$action $failure");
                Mockery::close();
            }
        }
    }

    private function providerUsers(int $firstId, int $count): array
    {
        $users = [];
        for ($offset = 0; $offset < $count; $offset++) {
            $id = $firstId + $offset;
            $users[] = ['id' => $id, 'username' => "other-$id"];
        }
        return $users;
    }

    private function gateway(Sas4ApiService $transport): Sas4Gateway
    {
        return new Sas4Gateway($transport, Mockery::mock(ClientSasStatusService::class));
    }

    private function configuredTransport(int $calls = 1): Mockery\MockInterface
    {
        $transport = Mockery::mock(Sas4ApiService::class);
        $transport->shouldReceive('isConfigured')->times($calls)->andReturn(true);
        return $transport;
    }

    private function client(int $id, ?string $username): Clients
    {
        $client = new Clients();
        $client->id = $id;
        $client->sas_username = $username;
        return $client;
    }

    private function assertNotConfigured(array $result): void
    {
        $this->assertSame(['ok' => false, 'code' => 'not_configured'], $result);
    }

    private function setConfiguredValues(): void
    {
        config()->set('sas4.url', 'https://sas.example.test');
        config()->set('sas4.username', 'user');
        config()->set('sas4.password', 'password');
        config()->set('sas4.aes_key', 'key');
    }
}
