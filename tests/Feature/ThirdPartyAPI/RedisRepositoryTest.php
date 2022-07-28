<?php

namespace Tests\Feature\ThirdPartyAPI;

use Tests\TestCase;
use Support\ThirdPartyServices\OpenExchangeRates\Classes\RedisRepository;
use Support\ThirdPartyServices\OpenExchangeRates\Exceptions\LUAScriptException;

/**
 * @group Redis
 * @group 3rdPartyAPI
 */
class RedisRepositoryTest extends TestCase
{
    private RedisRepository $redis;

    public function setUp(): void
    {
        parent::setUp();

        $this->redis = app(RedisRepository::class);
    }

    public function tearDown(): void
    {
        parent::tearDown();

        $this->redis->forget('foo')
                    ->forget('bar')
                    ->forget('mykey')
                    ->forget('fookey');
    }

    /**
     * @testdox Testando os métodos da classe de `RedisRepository`
     */
    public function testRedisRepository(): void
    {
        $redis = $this->redis;

        self::assertNull($redis->getFromCache('nonexists'));

        $redis->storeHashInCache('foo', $cachedItems = ['A' => 1, 'B' => 2, 'C' => 3]);

        $data = $redis->getFromCache('foo');

        self::assertEqualsCanonicalizing($cachedItems, $data);

        $itensFromCache = $redis->getHashFromCache('foo', ['A', 'C']);

        self::assertEqualsCanonicalizing($itensFromCache, ['A' => 1, 'C' => 3]);

        $redis->storeItemInCache('bar', 'value');

        self::assertEquals($redis->getFromCache('bar'), 'value');

        self::assertTrue($redis->existsInCache('foo'));
        self::assertTrue($redis->existsInCache('bar'));
        self::assertFalse($redis->existsInCache('non'));

        $redis->forget('foo')->forget('bar');

        self::assertFalse($redis->existsInCache('foo'));
        self::assertFalse($redis->existsInCache('bar'));
    }

    /**
     * @testdox Testando o proxy do método mágico de `__call` com uso da trait de `ForwardsCalls`
     */
    public function testCallMethod(): void
    {
        $redis = $this->redis;

        self::assertTrue($redis->set('mykey', 'value'));

        self::assertEquals($redis->getFromCache('mykey'), 'value');
    }

    /**
     * @testdox Testando a execução de SCRIPT LUA no método `runLUAScript`
     */
    public function testEvalScript(): void
    {
        $redis = $this->redis;

        self::assertEquals($redis->runLUAScript('return ARGV[1]', [], ['Hello']), 'Hello');
        self::assertEquals($redis->runLUAScript("return 'Hello, scripting!'"), 'Hello, scripting!');

        self::assertTrue($redis->runLUAScript("return redis.call('SET', KEYS[1], ARGV[1])", ['fookey'], ['barvalue']));
        self::assertEquals($redis->getFromCache('fookey'), 'barvalue');

        $this->expectException(LUAScriptException::class);

        $redis->runLUAScript('this-is-not-lua');
    }
}
