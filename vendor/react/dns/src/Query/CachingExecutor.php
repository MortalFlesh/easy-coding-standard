<?php

namespace ECSPrefix20220403\React\Dns\Query;

use ECSPrefix20220403\React\Cache\CacheInterface;
use ECSPrefix20220403\React\Dns\Model\Message;
use ECSPrefix20220403\React\Promise\Promise;
final class CachingExecutor implements \ECSPrefix20220403\React\Dns\Query\ExecutorInterface
{
    /**
     * Default TTL for negative responses (NXDOMAIN etc.).
     *
     * @internal
     */
    const TTL = 60;
    private $executor;
    private $cache;
    public function __construct(\ECSPrefix20220403\React\Dns\Query\ExecutorInterface $executor, \ECSPrefix20220403\React\Cache\CacheInterface $cache)
    {
        $this->executor = $executor;
        $this->cache = $cache;
    }
    public function query(\ECSPrefix20220403\React\Dns\Query\Query $query)
    {
        $id = $query->name . ':' . $query->type . ':' . $query->class;
        $cache = $this->cache;
        $that = $this;
        $executor = $this->executor;
        $pending = $cache->get($id);
        return new \ECSPrefix20220403\React\Promise\Promise(function ($resolve, $reject) use($query, $id, $cache, $executor, &$pending, $that) {
            $pending->then(function ($message) use($query, $id, $cache, $executor, &$pending, $that) {
                // return cached response message on cache hit
                if ($message !== null) {
                    return $message;
                }
                // perform DNS lookup if not already cached
                return $pending = $executor->query($query)->then(function (\ECSPrefix20220403\React\Dns\Model\Message $message) use($cache, $id, $that) {
                    // DNS response message received => store in cache when not truncated and return
                    if (!$message->tc) {
                        $cache->set($id, $message, $that->ttl($message));
                    }
                    return $message;
                });
            })->then($resolve, function ($e) use($reject, &$pending) {
                $reject($e);
                $pending = null;
            });
        }, function ($_, $reject) use(&$pending, $query) {
            $reject(new \RuntimeException('DNS query for ' . $query->describe() . ' has been cancelled'));
            $pending->cancel();
            $pending = null;
        });
    }
    /**
     * @param Message $message
     * @return int
     * @internal
     */
    public function ttl(\ECSPrefix20220403\React\Dns\Model\Message $message)
    {
        // select TTL from answers (should all be the same), use smallest value if available
        // @link https://tools.ietf.org/html/rfc2181#section-5.2
        $ttl = null;
        foreach ($message->answers as $answer) {
            if ($ttl === null || $answer->ttl < $ttl) {
                $ttl = $answer->ttl;
            }
        }
        if ($ttl === null) {
            $ttl = self::TTL;
        }
        return $ttl;
    }
}
