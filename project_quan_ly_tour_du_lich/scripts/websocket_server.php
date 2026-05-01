<?php

declare(strict_types=1);

use Ratchet\ConnectionInterface;
use Ratchet\Http\HttpServer;
use Ratchet\MessageComponentInterface;
use Ratchet\Server\IoServer;
use Ratchet\WebSocket\WsServer;
use React\EventLoop\Factory as LoopFactory;
use React\Socket\SocketServer;

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../commons/env.php';
require_once __DIR__ . '/../commons/function.php';

final class RealtimeNotificationSocketServer implements MessageComponentInterface
{
    /** @var SplObjectStorage<ConnectionInterface, array<string,mixed>> */
    private SplObjectStorage $clients;

    /** @var array<string,string> */
    private array $groupPayloadHashes = [];

    /** @var array<string,int> */
    private array $groupPingAt = [];

    public function __construct()
    {
        $this->clients = new SplObjectStorage();
    }

    public function onOpen(ConnectionInterface $conn): void
    {
        $query = (string)$conn->httpRequest->getUri()->getQuery();
        parse_str($query, $queryParams);

        $token = trim((string)($queryParams['token'] ?? ''));
        $auth = verifyRealtimeAuthToken($token, 'notifications');
        if (!$auth) {
            $conn->close();
            return;
        }

        $metadata = [
            'user_id' => (int)($auth['user_id'] ?? 0),
            'role' => (string)($auth['role'] ?? ''),
            'scope' => (string)($auth['scope'] ?? ''),
        ];

        if ($metadata['user_id'] <= 0 || $metadata['role'] === '') {
            $conn->close();
            return;
        }

        $this->clients->attach($conn, $metadata);

        $conn->send(json_encode([
            'type' => 'welcome',
            'payload' => [
                'role' => $metadata['role'],
                'user_id' => $metadata['user_id'],
                'ts' => time(),
            ],
        ], JSON_UNESCAPED_UNICODE));
    }

    public function onMessage(ConnectionInterface $from, $msg): void
    {
        $decoded = json_decode((string)$msg, true);
        if (!is_array($decoded)) {
            return;
        }

        $type = trim((string)($decoded['type'] ?? ''));
        if ($type !== 'ping') {
            return;
        }

        $from->send(json_encode([
            'type' => 'pong',
            'payload' => ['ts' => time()],
        ], JSON_UNESCAPED_UNICODE));
    }

    public function onClose(ConnectionInterface $conn): void
    {
        if ($this->clients->contains($conn)) {
            $this->clients->detach($conn);
        }
    }

    public function onError(ConnectionInterface $conn, \Exception $e): void
    {
        error_log('[websocket_server] Connection error: ' . $e->getMessage());
        $conn->close();
        if ($this->clients->contains($conn)) {
            $this->clients->detach($conn);
        }
    }

    public function tick(): void
    {
        if (count($this->clients) === 0) {
            return;
        }

        /** @var array<string, array{meta: array<string,mixed>, clients: ConnectionInterface[]}> $groups */
        $groups = [];

        foreach ($this->clients as $client) {
            $meta = $this->clients->getInfo();
            if (!is_array($meta)) {
                continue;
            }
            $groupKey = $this->buildGroupKey($meta);
            if (!isset($groups[$groupKey])) {
                $groups[$groupKey] = ['meta' => $meta, 'clients' => []];
            }
            $groups[$groupKey]['clients'][] = $client;
        }

        foreach ($groups as $groupKey => $group) {
            $meta = $group['meta'];
            $userId = (int)($meta['user_id'] ?? 0);
            $role = (string)($meta['role'] ?? '');
            if ($userId <= 0 || $role === '') {
                continue;
            }

            try {
                $payload = getRealtimeNotificationPayload($role, $userId);
            } catch (Throwable $e) {
                error_log('[websocket_server] Payload error: ' . $e->getMessage());
                $payload = ['success' => false];
            }

            $messageJson = json_encode([
                'type' => 'notification',
                'payload' => $payload,
            ], JSON_UNESCAPED_UNICODE);
            if ($messageJson === false) {
                continue;
            }

            $payloadHash = md5($messageJson);
            $lastHash = $this->groupPayloadHashes[$groupKey] ?? '';
            $shouldPing = (time() - (int)($this->groupPingAt[$groupKey] ?? 0)) >= 20;

            if ($payloadHash !== $lastHash) {
                $this->sendToGroup($group['clients'], $messageJson);
                $this->groupPayloadHashes[$groupKey] = $payloadHash;
                $this->groupPingAt[$groupKey] = time();
                continue;
            }

            if ($shouldPing) {
                $this->sendToGroup($group['clients'], json_encode([
                    'type' => 'ping',
                    'payload' => ['ts' => time()],
                ], JSON_UNESCAPED_UNICODE));
                $this->groupPingAt[$groupKey] = time();
            }
        }
    }

    /** @param ConnectionInterface[] $clients */
    private function sendToGroup(array $clients, string $message): void
    {
        foreach ($clients as $client) {
            try {
                $client->send($message);
            } catch (Throwable $e) {
                if ($this->clients->contains($client)) {
                    $this->clients->detach($client);
                }
                $client->close();
            }
        }
    }

    /** @param array<string,mixed> $meta */
    private function buildGroupKey(array $meta): string
    {
        return (string)($meta['role'] ?? '') . ':' . (int)($meta['user_id'] ?? 0);
    }
}

if (!realtimeWebSocketEnabled()) {
    fwrite(STDERR, "REALTIME_WS_ENABLED=0, websocket server will not start.\n");
    exit(1);
}

$host = REALTIME_WS_HOST;
$port = REALTIME_WS_PORT;
$address = $host . ':' . $port;

$loop = LoopFactory::create();
$app = new RealtimeNotificationSocketServer();
$loop->addPeriodicTimer(2.0, static function () use ($app): void {
    $app->tick();
});

$socket = new SocketServer($address, [], $loop);
new IoServer(new HttpServer(new WsServer($app)), $socket, $loop);

echo '[websocket_server] Listening on ws://' . $address . PHP_EOL;
$loop->run();
