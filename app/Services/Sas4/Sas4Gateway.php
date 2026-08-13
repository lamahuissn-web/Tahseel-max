<?php

namespace App\Services\Sas4;

use App\Models\Clients;

/** Application-facing SAS4 boundary. Raw provider responses never cross status APIs. */
class Sas4Gateway
{
    private const CONTROL_ACTIONS = ['enable', 'disable', 'disconnect', 'change_profile'];

    public function __construct(
        private Sas4ApiService $transport,
        private ClientSasStatusService $statusService,
    ) {
    }

    public function configured(): bool
    {
        return $this->transport->isConfigured();
    }

    public function statuses(iterable $clients): array
    {
        // ClientSasStatusService owns the transport call. Guard this path from
        // configuration directly so it does not add a transport interaction.
        if (! $this->statusConfigurationPresent()) {
            return ['ok' => false, 'code' => 'not_configured'];
        }
        return ['ok' => true, 'data' => $this->statusService->resolve($clients)];
    }

    public function searchUsers(string $query = '', int $page = 1, int $count = 20): array
    {
        return $this->read(fn () => $this->transport->searchUsers($query, $page, $count));
    }

    public function profiles(): array
    {
        return $this->read(fn () => $this->transport->getProfiles());
    }

    public function clientInfo(Clients $client): array
    {
        return $this->linkedRead($client, fn ($username) => $this->transport->getUserFullInfo($username));
    }

    public function clientTraffic(Clients $client): array
    {
        return $this->linkedRead($client, fn ($username) => $this->transport->getTrafficAndSessions($username));
    }

    public function clientDailyTraffic(Clients $client, int $month, int $year): array
    {
        return $this->linkedRead($client, fn ($username) => $this->transport->getDailyTrafficReport($username, $month, $year));
    }

    public function usernameExists(string $username): array
    {
        if (! $this->configured()) return ['ok' => false, 'code' => 'not_configured'];
        $username = trim($username);
        $result = $this->transport->searchUsers($username, 1, 100);
        if ($result === null) return ['ok' => false, 'code' => 'unavailable'];
        if (! is_array($result) || ! array_key_exists('data', $result) || ! is_array($result['data']) || ! array_is_list($result['data'])) {
            return ['ok' => false, 'code' => 'invalid_response'];
        }

        $matches = 0;
        foreach ($result['data'] as $user) {
            if (! is_array($user) || ! isset($user['id'], $user['username']) || ! is_string($user['username']) || trim($user['username']) === '') {
                return ['ok' => false, 'code' => 'invalid_response'];
            }
            if ($this->normalizeUsername($user['username']) === $this->normalizeUsername($username)) {
                $matches++;
            }
        }

        if ($matches > 1) return ['ok' => false, 'code' => 'invalid_response'];
        if ($matches === 0 && ! $this->searchResultProvesCompletion($result, 100)) {
            return ['ok' => false, 'code' => 'invalid_response'];
        }
        return ['ok' => true, 'data' => $matches === 1];
    }

    public function createAccount(string $username, string $password, int|string $profileId, string $firstname = '', ?string $expiration = null, int $enabled = 0): array
    {
        if (! $this->configured()) return ['ok' => false, 'code' => 'not_configured'];
        $result = $this->transport->createUser($username, $password, $profileId, $firstname, 1, $expiration, $enabled);
        return $this->providerWriteResult($result);
    }

    /** Performs exactly one provider write. Reads used for identity/verification are never retried here. */
    public function control(Clients $client, string $action, int|string|null $profileId = null, ?string $expiration = null): array
    {
        if (! in_array($action, self::CONTROL_ACTIONS, true)) return ['ok' => false, 'code' => 'invalid_action'];
        if (! $this->configured()) return ['ok' => false, 'code' => 'not_configured'];
        $username = trim((string) $client->sas_username);
        if ($username === '') return ['ok' => false, 'code' => 'unlinked'];
        if ($action === 'change_profile' && ($profileId === null || (string) $profileId === '')) return ['ok' => false, 'code' => 'profile_required'];

        $found = $this->transport->getUserByUsername($username);
        $data = is_array($found) ? ($found['data'] ?? null) : null;
        if (! is_array($data) || ! isset($data['id'], $data['username']) || $this->normalizeUsername($data['username']) !== $this->normalizeUsername($username)) {
            return ['ok' => false, 'code' => 'not_found'];
        }
        $id = $data['id'];
        $result = match ($action) {
            'enable' => $this->transport->enableUser($id),
            'disable' => $this->transport->disableUser($id),
            'disconnect' => $this->transport->disconnectUser($id),
            'change_profile' => $expiration
                ? $this->transport->changeProfileAndExpiration($id, $profileId, $expiration)
                : $this->transport->changeProfile($id, $profileId),
        };
        $write = $this->providerWriteResult($result);
        if (! $write['ok']) {
            return $write + ['action' => $action, 'client_id' => (int) $client->id, 'sas_username' => $username];
        }

        $verification = 'not_applicable';
        if ($action === 'enable' || $action === 'disable' || $action === 'change_profile') {
            $after = $this->transport->getUserByUsername($username);
            $afterData = is_array($after) ? ($after['data'] ?? null) : null;
            if (! is_array($afterData)) {
                return ['ok' => false, 'code' => 'verification_failed', 'verification' => 'unavailable'];
            }
            $sameAccount = isset($afterData['id'], $afterData['username'])
                && (string) $afterData['id'] === (string) $id
                && $this->normalizeUsername($afterData['username']) === $this->normalizeUsername($username);
            if (! $sameAccount) {
                return ['ok' => false, 'code' => 'verification_failed', 'verification' => 'mismatch'];
            }
            $profileVerified = (string) ($afterData['profile_id'] ?? '') === (string) $profileId;
            $expirationVerified = $expiration === null
                || ($this->normalizeDate($afterData['expiration'] ?? null) !== null
                    && $this->normalizeDate($afterData['expiration']) === $this->normalizeDate($expiration));
            $verified = $action === 'enable' ? (int) ($afterData['enabled'] ?? -1) === 1
                : ($action === 'disable' ? (int) ($afterData['enabled'] ?? -1) === 0
                : $profileVerified && $expirationVerified);
            if (! $verified) {
                return ['ok' => false, 'code' => 'verification_failed', 'verification' => 'mismatch'];
            }
            $verification = 'verified';
        }

        return $write + ['action' => $action, 'client_id' => (int) $client->id, 'sas_username' => $username, 'verification' => $verification];
    }

    private function linkedRead(Clients $client, callable $call): array
    {
        if (! $this->configured()) return ['ok' => false, 'code' => 'not_configured'];
        $username = trim((string) $client->sas_username);
        if ($username === '') return ['ok' => false, 'code' => 'unlinked'];
        $data = $call($username);
        return is_array($data) ? ['ok' => true, 'data' => $data] : ['ok' => false, 'code' => 'unavailable'];
    }

    private function read(callable $call): array
    {
        if (! $this->configured()) return ['ok' => false, 'code' => 'not_configured'];
        $data = $call();
        return is_array($data) ? ['ok' => true, 'data' => $data] : ['ok' => false, 'code' => 'unavailable'];
    }

    private function searchResultProvesCompletion(array $result, int $requestedCount): bool
    {
        $rowCount = count($result['data']);
        $metadataKeys = ['total', 'current_page', 'last_page'];
        $presentMetadata = array_filter(
            $metadataKeys,
            fn (string $key): bool => array_key_exists($key, $result),
        );

        // Observed SAS filtered searches can omit pagination metadata. A short
        // page is complete under the provider's count-limited search contract;
        // a full page remains ambiguous and therefore fails closed.
        if ($presentMetadata === []) {
            return $rowCount < $requestedCount;
        }
        if (count($presentMetadata) !== count($metadataKeys)) {
            return false;
        }

        $total = $result['total'];
        $currentPage = $result['current_page'];
        $lastPage = $result['last_page'];
        if (! is_int($total) || ! is_int($currentPage) || ! is_int($lastPage)
            || $total < 0 || $currentPage !== 1 || $lastPage < 1
            || $total !== $rowCount || $lastPage !== 1) {
            return false;
        }

        return $rowCount <= $requestedCount;
    }

    private function normalizeUsername(mixed $username): string
    {
        return strtolower(trim((string) $username));
    }

    private function normalizeDate(mixed $value): ?string
    {
        if (! is_string($value) || trim($value) === '') {
            return null;
        }

        $formats = ['!Y-m-d', '!Y-m-d H:i:s', '!Y-m-d\\TH:i:s', '!Y-m-d\\TH:i:s.v\\Z', '!Y-m-d\\TH:i:sP', '!Y-m-d\\TH:i:s.vP'];
        foreach ($formats as $format) {
            $date = \DateTimeImmutable::createFromFormat($format, trim($value));
            $errors = \DateTimeImmutable::getLastErrors();
            if ($date !== false && ($errors === false || ($errors['warning_count'] === 0 && $errors['error_count'] === 0))) {
                return $date->format('Y-m-d');
            }
        }

        return null;
    }

    private function providerWriteResult(mixed $result): array
    {
        $status = is_array($result) ? ($result['status'] ?? null) : null;
        return in_array((int) $status, [200, 201], true)
            ? ['ok' => true, 'code' => 'success']
            : ['ok' => false, 'code' => 'write_failed'];
    }

    private function statusConfigurationPresent(): bool
    {
        foreach (['url', 'username', 'password', 'aes_key'] as $key) {
            if (trim((string) config("sas4.$key")) === '') {
                return false;
            }
        }

        return true;
    }
}
