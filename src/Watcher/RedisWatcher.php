<?php

declare(strict_types=1);

namespace XDApp\Casbin\Watcher;


use Casbin\Persist\Watcher;
use Hyperf\Redis\Redis;
use Hyperf\Redis\RedisFactory;
use Hyperf\Redis\RedisProxy;
use Hyperf\Utils\ApplicationContext;
use Monolog\Logger;
use Psr\Log\LoggerInterface;
use Swoole\Coroutine;
use Swoole\Coroutine\Channel;
use Swoole\Timer;
use XDApp\Casbin\Exception\RedisWatcherException;
use Closure;

class RedisWatcher implements Watcher
{
    const DEFAULT_SQUASH_TIME = 10 * 1000;

    private array $config;

    private string $baseID;

    private string $channel;

    private bool $ignoreSelf;

    private Closure $updateCallback;

    private Channel $msgChan;

    private bool $squashMessages;

    private int $squashTime;

    private int $pendingTimer = 0;

    private RedisProxy $redis;

    protected LoggerInterface $logger;

    /**
     * RedisWatcher constructor.
     *
     * @param RedisProxy $redis
     * @param array $config
     */
    public function __construct(RedisProxy $redis, array $config = [])
    {
        $this->config = $config;
        $this->redis = $redis;
        $this->baseID = $config['baseID'] ?? uniqid("casbin");
        $this->ignoreSelf = $config['ignoreSelf'] ?? true;
        $this->channel = $config['channel'] ?? '/casbin';
        $this->squashMessages = $config['squashMessages'] ?? false;
        $this->squashTime = $config['squashTime'] ?? self::DEFAULT_SQUASH_TIME;
        $this->logger = new Logger('casbin');
        $this->msgChan = new Channel(1);

        $this->handleLoop();
    }

    private function getConn(): Redis
    {
        return $this->redis;
    }

    public function setLogger(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    private function handleLoop()
    {
        go(function () {
            $this->logger->debug("handle loop", ['baseId' => $this->baseID]);
            while (true) {
                $this->logger->debug("connect redis");
                try {
                    $this->subscribe();
                    return;
                } catch (\Throwable $e) {
                    $this->logger->warning($e->getMessage());
                    Coroutine::sleep(3);
                }
            }
        });

        go(function () {
            while (true) {
                $this->handleMsg($this->msgChan->pop());
            }
        });
    }

    private function subscribe()
    {
        $this->getConn()->subscribe($this->getChannels(), function ($redis, $chan, $msg) {
            $this->logger->debug("recv msg", ['channel' => $chan, 'msg' => $msg]);
            $this->msgChan->push($msg);
        });
    }

    protected function handleMsg(string $msg)
    {
        if ($this->ignoreSelf && $this->baseID === $msg) {
            return;
        }

        if ($this->squashMessages && $this->pendingTimer === 0) {
            $this->pendingTimer = Timer::after($this->squashTime, function () {
                $this->handleUpdateCallback();
                $this->pendingTimer = 0;
            });
        } elseif (!$this->squashMessages) {
            $this->handleUpdateCallback();
        }
    }

    private function handleUpdateCallback()
    {
        if ($this->updateCallback) {
            ($this->updateCallback)();
        }
    }

    protected function getChannels()
    {
        return [$this->channel];
    }

    public function setUpdateCallback(Closure $func): void
    {
        $this->updateCallback = $func;
    }

    public function update(): void
    {
        $rs = $this->getConn()->publish($this->channel, $this->baseID);
        if ($rs) {
            $this->logger->info("casbin update", ['baseID' => $this->baseID, 'channel' => $this->channel]);
        } elseif ($rs === 0) {
            $this->logger->info("no subscriber for casbin update", ['baseID' => $this->baseID, 'channel' => $this->channel]);
        } else {
            $this->logger->error("casbin update failed", ['baseID' => $this->baseID, 'channel' => $this->channel]);
        }
    }
}