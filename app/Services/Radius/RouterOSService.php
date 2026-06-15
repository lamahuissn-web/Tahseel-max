<?php

namespace App\Services\Radius;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Config;

/**
 * RouterOS API Client — Direct MikroTik CHR connection (port 8728/8729)
 * 
 * Extracted from SAS Panel (RouterOS.php) and adapted for Laravel.
 * Handles: challenge-response auth (legacy), modern auth, PPPoE,
 * Hotspot, API command execution, buffer management.
 */
class RouterOSService
{
    private bool $debug = false;
    private bool $connected = false;
    private int $port = 8728;
    private bool $ssl = false;
    private int $timeout = 5;
    private int $attempts = 5;
    private int $delay = 3;
    /** @var resource|null */
    private $socket = null;
    private string $lastError = "";

    public function __construct(
        private string $host = "",
        private string $username = "",
        private string $password = "",
        int $port = 8728
    ) {
        $this->port = $port;

        // Load from config if not explicitly provided
        if (empty($this->host)) {
            $this->host = Config::get("services.routeros.host", "");
            $this->username = Config::get("services.routeros.username", "");
            $this->password = Config::get("services.routeros.password", "");
            $this->port = (int) Config::get("services.routeros.port", 8728);
        }

        if ($this->port === 8729) {
            $this->ssl = true;
        }
    }

    public function connect(
        ?string $host = null,
        ?string $username = null,
        ?string $password = null,
        ?int $port = null
    ): bool {
        $this->host = $host ?? $this->host;
        $this->username = $username ?? $this->username;
        $this->password = $password ?? $this->password;
        $this->port = $port ?? $this->port;

        if ($this->port === 8729) {
            $this->ssl = true;
        }

        $protocol = $this->ssl ? "ssl://" : "";
        $context = stream_context_create([
            "ssl" => [
                "verify_peer" => false,
                "verify_peer_name" => false
            ]
        ]);

        $this->socket = @stream_socket_client(
            $protocol . $this->host . ":" . $this->port,
            $errno,
            $errstr,
            $this->timeout,
            STREAM_CLIENT_CONNECT,
            $context
        );

        if (!$this->socket) {
            $this->lastError = $errstr ?: "Connection Timeout or Refused";
            Log::warning("RouterOS connect failed: {$this->host}:{$this->port}");
            return false;
        }

        stream_set_timeout($this->socket, $this->timeout);
        $result = $this->doLogin();
        if ($result) {
            Log::info("RouterOS connected: {$this->host}:{$this->port}");
        }
        return $result;
    }

    private function doLogin(): bool
    {
        // Method 1: Modern auth
        $this->write("/login", false);
        $this->write("=name=" . $this->username, false);
        $this->write("=password=" . $this->password, true);

        $response = $this->read();

        // Check for legacy challenge-response
        if (!empty($response[0]) && strpos($response[0], "!done") === 0) {
            if (preg_match("/=ret=(.*)/", $response[0], $matches)) {
                $challenge = pack("H*", $matches[1]);
                $hash = md5(chr(0) . $this->password . $challenge);

                $this->write("/login", false);
                $this->write("=name=" . $this->username, false);
                $this->write("=response=00" . $hash, true);
                $response = $this->read();
            }
        }

        if (!empty($response[0]) && strpos($response[0], "!done") === 0) {
            $this->connected = true;
            $this->lastError = "";
            return true;
        }

        $this->lastError = "Invalid Username or Password";
        if (!empty($response[0]) && strpos($response[0], "!trap") === 0) {
            foreach ($response as $line) {
                if (strpos($line, "=message=") === 0) {
                    $this->lastError = substr($line, 9);
                    break;
                }
            }
        }

        Log::warning("RouterOS login failed: {$this->lastError}");
        $this->disconnect();
        return false;
    }

    public function comm(string $command, array $params = []): array|null
    {
        if (!$this->connected) {
            return null;
        }

        $count = count($params);
        $this->write($command, $count === 0);

        $i = 0;
        foreach ($params as $k => $v) {
            $el = match ($k[0]) {
                "?" => "$k=$v",
                "~" => "$k~$v",
                default => "=$k=$v",
            };
            $last = ($i++ === $count - 1);
            $this->write($el, $last);
        }

        $response = $this->read();
        return $this->parseResponse($response);
    }

    public function getActivePppUsers(): array
    {
        $result = $this->comm("/ppp/active/print", [
            ".proplist" => "name,address,uptime,bytes-in,bytes-out,mac-address,caller-id,service"
        ]);
        return is_array($result) ? $result : [];
    }

    public function getPppUser(string $username): ?array
    {
        $result = $this->comm("/ppp/active/print", [
            "?name" => $username,
            ".proplist" => "name,address,uptime,bytes-in,bytes-out,mac-address,caller-id,service"
        ]);
        if (!is_array($result) || empty($result)) {
            return null;
        }
        return $result[0];
    }

    public function kickUser(string $username): bool
    {
        $kicked = false;

        $pppUsers = $this->comm("/ppp/active/print", ["?name" => $username]);
        if (is_array($pppUsers)) {
            foreach ($pppUsers as $user) {
                if (!empty($user[".id"])) {
                    $this->comm("/ppp/active/remove", [".id" => $user[".id"]]);
                    $kicked = true;
                }
            }
        }

        $hsUsers = $this->comm("/ip/hotspot/active/print", ["?user" => $username]);
        if (is_array($hsUsers)) {
            foreach ($hsUsers as $user) {
                if (!empty($user[".id"])) {
                    $this->comm("/ip/hotspot/active/remove", [".id" => $user[".id"]]);
                    $kicked = true;
                }
            }
        }

        return $kicked;
    }

    public function getRouterStats(): array
    {
        $resource = $this->comm("/system/resource/print");
        $pppCount = $this->comm("/ppp/active/print", [".count" => ""]);
        $hsCount = $this->comm("/ip/hotspot/active/print", [".count" => ""]);

        $cpu = $resource[0]["cpu-load"] ?? "0%";
        $mem = (int) ($resource[0]["free-memory"] ?? 0);
        $hdd = (int) ($resource[0]["free-hdd-space"] ?? 0);

        $ppp = 0;
        if (is_array($pppCount)) {
            if (isset($pppCount[0][".count"])) {
                $ppp = (int) $pppCount[0][".count"];
            } elseif (is_numeric($pppCount[0] ?? null)) {
                $ppp = (int) $pppCount[0];
            }
        } elseif (is_numeric($pppCount)) {
            $ppp = (int) $pppCount;
        }

        $hs = 0;
        if (is_array($hsCount)) {
            if (isset($hsCount[0][".count"])) {
                $hs = (int) $hsCount[0][".count"];
            } elseif (is_numeric($hsCount[0] ?? null)) {
                $hs = (int) $hsCount[0];
            }
        } elseif (is_numeric($hsCount)) {
            $hs = (int) $hsCount;
        }

        return [
            "cpu" => $cpu,
            "free_memory" => $this->formatBytes($mem),
            "free_memory_raw" => $mem,
            "free_hdd" => $this->formatBytes($hdd),
            "free_hdd_raw" => $hdd,
            "ppp_active" => $ppp,
            "hotspot_active" => $hs,
            "total_active" => $ppp + $hs,
            "raw" => $resource,
        ];
    }

    public function getLastError(): string
    {
        return $this->lastError;
    }

    public function isConnected(): bool
    {
        return $this->connected;
    }

    public function disconnect(): void
    {
        if (is_resource($this->socket)) {
            fclose($this->socket);
        }
        $this->socket = null;
        $this->connected = false;
    }

    public function __destruct()
    {
        $this->disconnect();
    }

    private function write(string $command, bool $last = true): void
    {
        $len = strlen($command);

        if ($len < 128) {
            $header = chr($len);
        } elseif ($len < 16384) {
            $header = chr(($len >> 8) | 0x80) . chr($len & 0xFF);
        } elseif ($len < 2097152) {
            $header = chr($len >> 16 | 0xC0) . chr(($len >> 8) & 0xFF) . chr($len & 0xFF);
        } elseif ($len < 268435456) {
            $header = chr($len >> 24 | 0xE0) . chr(($len >> 16) & 0xFF) . chr(($len >> 8) & 0xFF) . chr($len & 0xFF);
        } else {
            $header = chr(0xF0) . chr(($len >> 24) & 0xFF) . chr(($len >> 16) & 0xFF) . chr(($len >> 8) & 0xFF) . chr($len & 0xFF);
        }

        for ($attempt = 0; $attempt < $this->attempts; $attempt++) {
            $res = fwrite($this->socket, $header . $command);
            if ($res !== false) break;
            sleep($this->delay);
        }

        if ($last) {
            fwrite($this->socket, chr(0));
        }
    }

    private function read(): array
    {
        $response = [];

        while (true) {
            $byte = fread($this->socket, 1);
            if ($byte === false || $byte === "") break;

            $len = ord($byte);
            if (($len & 0x80) === 0) {
                $length = $len;
            } elseif (($len & 0xC0) === 0x80) {
                $length = (($len & 0x3F) << 8) + ord(fread($this->socket, 1));
            } elseif (($len & 0xE0) === 0xC0) {
                $length = (($len & 0x1F) << 16) + ord(fread($this->socket, 1)) + (ord(fread($this->socket, 1)) << 8);
            } elseif (($len & 0xF0) === 0xE0) {
                $length = (($len & 0x0F) << 24) + ord(fread($this->socket, 1)) + (ord(fread($this->socket, 1)) << 8) + (ord(fread($this->socket, 1)) << 16);
            } else {
                $length = ord(fread($this->socket, 1)) + (ord(fread($this->socket, 1)) << 8) + (ord(fread($this->socket, 1)) << 16) + (ord(fread($this->socket, 1)) << 24);
            }

            if ($length > 6000000) {
                return ["!trap", "=message=Protocol Error: Packet too large"];
            }

            $line = "";
            if ($length > 0) {
                $rec = 0;
                while ($rec < $length) {
                    $r = fread($this->socket, $length - $rec);
                    if ($r === false) break;
                    $rec += strlen($r);
                    $line .= $r;
                }
            }

            $response[] = $line;
            if ($line === "!done") break;
        }

        return $response;
    }

    private function parseResponse(array $response): array
    {
        $result = [];
        $i = -1;
        $retVal = null;

        foreach ($response as $line) {
            if ($line === "!re" || $line === "!trap") {
                $i++;
                if ($line === "!trap") {
                    $result[$i]["__error"] = true;
                }
            } elseif (strpos($line, "=") === 0) {
                if (preg_match("/^=([^=]+)=(.*)$/", $line, $matches)) {
                    if ($i >= 0) {
                        $result[$i][$matches[1]] = $matches[2];
                    } elseif ($matches[1] === "ret") {
                        $retVal = $matches[2];
                    }
                }
            }
        }

        if ($i === -1 && $retVal !== null) {
            return [$retVal];
        }

        return $result;
    }

    private function formatBytes(int $bytes, int $precision = 2): string
    {
        $units = ["B", "KB", "MB", "GB", "TB"];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= pow(1024, $pow);
        return round($bytes, $precision) . " " . $units[$pow];
    }
}
