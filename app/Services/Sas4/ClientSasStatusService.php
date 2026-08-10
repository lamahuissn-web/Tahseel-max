<?php

namespace App\Services\Sas4;

use Illuminate\Support\Facades\Cache;

/**
 * Feature 009 — batch SAS connection status resolution.
 *
 * Contract:
 *  - one all-users SAS search per batch (never per-card / per-user)
 *  - exact, Unicode-safe, case-insensitive username matching only — never a
 *    fallback to client name, phone, `user`, or fuzzy identifiers
 *  - explicit online_status only: 1 => online, 0 => offline; any other value,
 *    a malformed row (non-array / missing, non-string or blank username /
 *    missing online_status) or a duplicate normalized username makes the
 *    ENTIRE payload malformed — one bounded token-refresh retry, then
 *    unavailable for every linked client (failure is never normalized to
 *    offline, malformed rows are never skipped or turned into not_found)
 *  - successful response without the exact username => not_found
 *  - no nonblank sas_username => unlinked, no SAS call at all
 *  - success-only 20-second cache of a minimal username => int(0|1) map;
 *    failed/malformed calls are never cached; empty data array is success
 *  - bounded external timeout (default 4 seconds, deployment-configurable in
 *    1..20) for EVERY HTTP request of the sequence (login token included);
 *    one bounded token-refresh retry only when the first attempt fails —
 *    never on ordinary calls
 *  - returns only client_id / sas_username / status — never enabled, IP,
 *    expiration, profile, traffic, token or raw payload fields
 */
class ClientSasStatusService
{
    public const CACHE_KEY = 'sas4_users_online_status_map';

    public const CACHE_TTL_SECONDS = 20;

    public const EXTERNAL_TIMEOUT_SECONDS = 4;

    public const MIN_EXTERNAL_TIMEOUT_SECONDS = 1;

    public const MAX_EXTERNAL_TIMEOUT_SECONDS = 20;

    public const ALL_USERS_SEARCH_COUNT = 5000;

    public const STATUS_UNLINKED = 'unlinked';

    public const STATUS_ONLINE = 'online';

    public const STATUS_OFFLINE = 'offline';

    public const STATUS_NOT_FOUND = 'not_found';

    public const STATUS_UNAVAILABLE = 'unavailable';

    private Sas4ApiService $sas;

    private int $cacheTtlSeconds;

    private int $externalTimeoutSeconds;

    public function __construct(
        Sas4ApiService $sas,
        ?int $cacheTtlSeconds = null,
        ?int $externalTimeoutSeconds = null,
    ) {
        $this->sas = $sas;
        $this->cacheTtlSeconds = $cacheTtlSeconds ?? self::CACHE_TTL_SECONDS;
        $this->externalTimeoutSeconds = self::boundedExternalTimeoutSeconds(
            $externalTimeoutSeconds ?? config('sas4.status_timeout_seconds', self::EXTERNAL_TIMEOUT_SECONDS),
        );
    }

    private static function boundedExternalTimeoutSeconds(mixed $configured): int
    {
        if (is_int($configured)) {
            $timeout = $configured;
        } elseif (is_string($configured) && preg_match('/^[1-9][0-9]*$/D', $configured) === 1) {
            $timeout = (int) $configured;
        } else {
            return self::EXTERNAL_TIMEOUT_SECONDS;
        }

        if ($timeout < self::MIN_EXTERNAL_TIMEOUT_SECONDS || $timeout > self::MAX_EXTERNAL_TIMEOUT_SECONDS) {
            return self::EXTERNAL_TIMEOUT_SECONDS;
        }

        return $timeout;
    }

    /**
     * Resolve statuses for a collection of already-scoped clients
     * (active + non-deleted, exactly the GET /api/v1/clients visibility).
     *
     * @param  iterable<int, object>  $clients  objects exposing ->id and ->sas_username
     * @return array<int, array{client_id: int, sas_username: string|null, status: string}>
     *                                                                                      keyed by client id
     */
    public function resolve(iterable $clients): array
    {
        $result = [];
        $linked = [];

        foreach ($clients as $client) {
            $id = (int) $client->id;
            $username = trim((string) ($client->sas_username ?? ''));
            $result[$id] = [
                'client_id' => $id,
                'sas_username' => $username !== '' ? $username : null,
                'status' => self::STATUS_UNLINKED,
            ];
            if ($username !== '') {
                $linked[$id] = $username;
            }
        }

        if ($linked === []) {
            return $result;
        }

        $map = $this->onlineStatusMap();

        if ($map === null) {
            // Timeout, auth/API failure or malformed payload: unavailable for
            // every linked client. Never normalized to offline.
            foreach (array_keys($linked) as $id) {
                $result[$id]['status'] = self::STATUS_UNAVAILABLE;
            }

            return $result;
        }

        foreach ($linked as $id => $username) {
            $needle = mb_strtolower($username, 'UTF-8');

            if (! array_key_exists($needle, $map)) {
                $result[$id]['status'] = self::STATUS_NOT_FOUND;

                continue;
            }

            $online = $map[$needle];
            if ($online === 1 || $online === '1') {
                $result[$id]['status'] = self::STATUS_ONLINE;
            } elseif ($online === 0 || $online === '0') {
                $result[$id]['status'] = self::STATUS_OFFLINE;
            } else {
                $result[$id]['status'] = self::STATUS_UNAVAILABLE;
            }
        }

        return $result;
    }

    /**
     * Success-only cached map of lowercase username => int(0|1).
     * Returns null on failure/malformed payload; null is never cached.
     */
    private function onlineStatusMap(): ?array
    {
        $cached = Cache::get(self::CACHE_KEY);
        if ($this->isValidNormalizedMap($cached)) {
            return $cached;
        }

        if ($cached !== null) {
            // Never trust a poisoned cache entry: drop it and treat the read
            // as a miss. An invalid cache must never drive not_found/offline/
            // online directly.
            Cache::forget(self::CACHE_KEY);
        }

        $map = $this->fetchAllUsersMap();
        if ($map === null) {
            return null;
        }

        Cache::put(self::CACHE_KEY, $map, $this->cacheTtlSeconds);

        return $map;
    }

    /**
     * A cached value is only usable when it is exactly the minimal normalized
     * map produced by buildMap(): every key is a nonblank string equal to its
     * own trim + mb_strtolower (UTF-8) and every value is a real int 0 or 1.
     * An empty map is valid. Anything else is poisoned and must be forgotten.
     */
    private function isValidNormalizedMap(mixed $map): bool
    {
        if (! is_array($map)) {
            return false;
        }

        foreach ($map as $key => $value) {
            if (! is_string($key) || $key === '' || trim($key) !== $key) {
                return false;
            }
            if (mb_strtolower($key, 'UTF-8') !== $key) {
                return false;
            }
            if ($value !== 0 && $value !== 1) {
                return false;
            }
        }

        return true;
    }

    /**
     * One all-users search normalized into the username => int(0|1) map, with
     * a single bounded token-refresh retry whenever the first attempt is
     * unusable (timeout, auth/API failure, missing/non-array data, or any
     * malformed row). Ordinary successful calls never refresh the token.
     */
    private function fetchAllUsersMap(): ?array
    {
        $map = $this->buildMap($this->searchAllUsers());
        if ($map !== null) {
            return $map;
        }

        // Invalidate the cached SAS token so getToken() re-fetches, then
        // retry exactly once.
        Cache::forget('sas4_token');
        $map = $this->buildMap($this->searchAllUsers());

        return $map;
    }

    private function searchAllUsers(): ?array
    {
        $result = $this->sas->searchUsers('', 1, self::ALL_USERS_SEARCH_COUNT, $this->externalTimeoutSeconds);

        return is_array($result) ? $result : null;
    }

    /**
     * Strictly normalize the all-users payload into a minimal
     * lowercase-username => int(0|1) map. ANY anomaly makes the ENTIRE
     * payload malformed (null): a `data` container that is not a list-shaped
     * array (array_is_list), a non-array row, a missing/non-string/blank
     * username, a missing online_status, an online_status that is not exactly
     * int/string 0 or 1, or a duplicate normalized username. Rows are never
     * silently skipped, unknown statuses are never cached, and malformed rows
     * never degrade into not_found. An empty data list is a well-formed
     * success.
     */
    private function buildMap(?array $payload): ?array
    {
        $rows = $payload['data'] ?? null;
        if (! is_array($rows) || ! array_is_list($rows)) {
            return null;
        }

        $map = [];
        foreach ($rows as $row) {
            if (! is_array($row)
                || ! isset($row['username'])
                || ! is_string($row['username'])
                || trim($row['username']) === ''
                || ! array_key_exists('online_status', $row)
                || ! in_array($row['online_status'], [0, 1, '0', '1'], true)
            ) {
                return null;
            }

            $username = mb_strtolower(trim($row['username']), 'UTF-8');
            if (isset($map[$username])) {
                return null; // duplicate normalized username
            }
            $map[$username] = (int) $row['online_status'];
        }

        return $map;
    }
}
