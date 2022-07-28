<?php

namespace Tests\Unit\Auth\Guard;

use Mockery;
use Tests\TestCase;
use Mockery\MockInterface;
use Illuminate\Http\Request;
use Core\Auth\CookieAuthGuard;
use Illuminate\Cookie\CookieJar;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Failed;
use Illuminate\Encryption\Encrypter;
use Illuminate\Auth\Events\Validated;
use Illuminate\Support\Facades\Event;
use Illuminate\Auth\Events\Attempting;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Cookie\CookieValuePrefix;
use Illuminate\Auth\Events\Authenticated;
use Illuminate\Contracts\Auth\UserProvider;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Contracts\Auth\Authenticatable;

/**
 * @small
 *
 * @group Auth
 *
 * @testdox CookieAuthGuard - (Tests\Unit\Auth\CookieAuthTest)
 */
class CookieAuthTest extends TestCase
{
    private const USER_COOKIE_DATA = ['id' => 11, 'role' => 'baz'];

    /**
     * @testdox Autenticar usuário quando o payload do cookie "api_token" tiver informações válidas.
     *
     * Usuário deve se autenticar quando o cookie "api_token" existir e o
     * guard puder decriptar o cookie e recuperar o ID do usuário.
     */
    public function testAuthenticatedUserWhenCookieApiTokenExists()
    {
        // Arrange
        [$user, $userId] = $this->getUser(timesCalled: 0);

        $guard = $this->getGuard();
        $guard->getRequest()->cookies->set('api_token', $this->getCookiePayload('api_token'));
        $guard->getProvider()->shouldReceive('retrieveById')->once()->with($userId)->andReturn($user);

        $events = Mockery::mock(Dispatcher::class);
        $events->shouldReceive('dispatch')->once()->with(Mockery::type(Authenticated::class));

        $guard->setDispatcher($events);

        // Act && Assert
        $this->assertSame($user, $guard->user());
        $this->assertSame($user, $guard->getUser());
        $this->assertSame($events, $guard->getDispatcher());

        $this->assertTrue($guard->check());
        $this->assertFalse($guard->guest());
    }

    /**
     * @testdox Deve ser possível criar novos cookies de autenticação quando o cookie de refresh existir e for solicitado.
     *
     * Quando existir o cookie de refresh "api_refresh", e seu payload tiver um usuário válido,
     * e for solicitado a criação de novos cookies, por meio do método "refresh" do guard, deve ser possível!
     */
    public function testCreateNewCookiesWhenRefreshCookieExistsAndIsRequested()
    {
        // Arrange
        [$user, $userId] = $this->getUser();

        $guard = $this->getGuard();
        $guard->setDispatcher($events = Event::fake());
        $guard->setUser($user);
        $guard->getRequest()->cookies->set($cookieName = 'api_refresh', $this->getCookiePayload('api_refresh'));

        // Act
        $guard->refresh();

        // Assert
        Event::assertDispatched(Authenticated::class);

        $this->assertTrue(Cookie::hasQueued('api_token'));
        $this->assertTrue(Cookie::hasQueued('api_refresh'));
        $this->assertSame($events, $guard->getDispatcher());

        $decryptedCookieValue = $this->encrypter()->decrypt(Cookie::queued($cookieName)->getValue(), false);
        $validatedValue = CookieValuePrefix::validate($cookieName, $decryptedCookieValue, $this->encrypter()->getKey());
        $cookiePayload = json_decode($validatedValue, true);

        $this->assertSame($userId, data_get($cookiePayload, 'user.id'));
        $this->assertSame($user->role, data_get($cookiePayload, 'user.role'));
    }

    /**
     * @testdox Quando for feito logout, então, todos os cookies devem ser removidos da sessão/navegador
     */
    public function testLogoutRemovesAllSessionCookies()
    {
        // Arrange
        [$user] = $this->getUser(0);

        $guard = $this->getGuard();
        $guard->setDispatcher(Event::fake());
        $guard->setUser($user);

        // $cookies = Mockery::mock(CookieJar::class);
        // $cookie = Mockery::mock(Cookie::class);
        // $cookies->shouldReceive('forget')->once()->with('bar')->andReturn($cookie);
        // $cookies->shouldReceive('queue')->once()->with($cookie);

        // Act
        $guard->logout();

        // Assert
        $this->assertTrue(Cookie::hasQueued('api_token'));
        // ! Cookie setado com uma data igual ou inferior a data/hora atual é removido automaticamente do navegador
        $this->assertTrue(strtotime('now') > Cookie::queued('api_token')->getExpiresTime());

        $this->assertTrue(Cookie::hasQueued('api_refresh'));
        $this->assertTrue(strtotime('now') > Cookie::queued('api_refresh')->getExpiresTime());

        $this->assertNull($guard->user());
        $this->assertNull($guard->id());
    }

    /**
     * @testdox Quando o cookie de autenticação ou refresh não existir, então, o usuário é considerado como "não logado"
     */
    public function testReturnsNullWhenCookieDoesntExist()
    {
        // Act
        $guard = $this->getGuard();

        // Assert
        $this->assertNull($guard->user());
    }

    /**
     * @testdox Quando as credencias não corresponderem com nenhum usuário, então, o método "attempt" deve retornar "false"
     */
    public function testAttemptReturnsFalseIfUserNotGiven()
    {
        // Arrange
        $guard = $this->getGuard();
        $guard->getProvider()->shouldReceive('retrieveByCredentials')->once()->with(['foo'])->andReturn(null);
        $guard->setDispatcher($events = Mockery::mock(Dispatcher::class));

        $events->shouldReceive('dispatch')->once()->with(Mockery::type(Attempting::class));
        $events->shouldReceive('dispatch')->once()->with(Mockery::type(Failed::class));
        $events->shouldNotReceive('dispatch')->with(Mockery::type(Validated::class));

        // Act && Assert
        $this->assertFalse($guard->attempt(['foo']));
    }

    /**
     * @testdox O método "authenticate" deve retornar o usuário autenticado/logado.
     */
    public function testAuthenticateReturnsUserWhenUserIsNotNull()
    {
        $user = Mockery::mock(Authenticatable::class);
        $guard = $this->getGuard()->setUser($user);

        $this->assertEquals($user, $guard->authenticate());
    }

    /**
     * @testdox Logar o usuário e gerar os cookies de response quando as credenciais forem válidas.
     */
    public function testAttemptLoginSuccessfully()
    {
        // Arrange
        $guard = $this->getGuard();
        $guard->setDispatcher(Event::fake());
        [$user, $userId] = $this->getUser(timesCalled: 2);

        $guard->getProvider()->shouldReceive('retrieveByCredentials')->once()->with(['foo'])->andReturn($user);
        $guard->getProvider()->shouldReceive('validateCredentials')->once()->with($user, ['foo'])->andReturnTrue();

        // Act && Assert
        $this->assertTrue($guard->attempt(['foo']));
        $this->assertSame($guard->id(), $userId);

        // Assert
        Event::assertDispatched(Attempting::class);
        Event::assertDispatched(Validated::class);
        Event::assertDispatched(Login::class);
        Event::assertDispatched(Authenticated::class);

        $this->assertTrue(Cookie::hasQueued('api_token'));
        $this->assertTrue(Cookie::hasQueued('api_refresh'));

        $cookieValue = function (string $cookieName): array {
            $decryptedCookieValue = $this->encrypter()->decrypt(Cookie::queued($cookieName)->getValue(), false);
            $validatedValue = CookieValuePrefix::validate(
                $cookieName,
                $decryptedCookieValue,
                $this->encrypter()->getKey()
            );

            return json_decode($validatedValue, true);
        };

        $apiTokenCookiePayload = $cookieValue('api_token');

        $this->assertSame($userId, data_get($apiTokenCookiePayload, 'user.id'));
        $this->assertSame($user->role, data_get($apiTokenCookiePayload, 'user.role'));

        $apiRefreshCookiePayload = $cookieValue('api_refresh');

        $this->assertSame($userId, data_get($apiRefreshCookiePayload, 'user.id'));
        $this->assertSame($user->role, data_get($apiRefreshCookiePayload, 'user.role'));
    }

    /**
     * @testdox Quando o método "setUser" for chamado, o evento de Authenticated deve ser acionado
     */
    public function testSetUserFiresAuthenticatedEvent()
    {
        // Arrange
        $guard = $this->getGuard();
        $guard->setDispatcher(Event::fake());

        // Act
        $guard->setUser(Mockery::mock(Authenticatable::class));

        // Assert
        Event::assertDispatched(Authenticated::class);
    }

    /**
     * @testdox A exceção "AuthenticationException" deve ser lançada quando o método "authenticate" for chamado e o usuário for "null"
     */
    public function testAuthenticateThrowsWhenUserIsNull()
    {
        // Assert
        $this->expectException(AuthenticationException::class);
        $this->expectExceptionMessage('Unauthenticated.');

        // Arrange
        $guard = $this->getGuard();

        // Act
        $guard->authenticate();
    }

    /**
     * @testdox Quando o usuário não for "null", o método "hasUser" deve retornar "true"
     */
    public function testHasUserReturnsTrueWhenUserIsNotNull()
    {
        // Arrange
        $guard = $this->getGuard()->setUser(Mockery::mock(Authenticatable::class));

        // Act && Assert
        $this->assertTrue($guard->hasUser());
    }

    /**
     * @testdox Quando o usuário for "null", o método "hasUser" deve retornar "false"
     */
    public function testHasUserReturnsFalseWhenUserIsNull()
    {
        // Arrange
        $guard = $this->getGuard();

        // Assert
        $this->assertFalse($guard->hasUser());
    }

    /**
     * @testdox Quando o usuário não for "null", o método "check" deve retornar "true" e o método "guest" deve retornar "false"
     */
    public function testIsAuthedReturnsTrueWhenUserIsNotNull()
    {
        // Arrange
        $guard = $this->getGuard();

        // Act
        $guard->setUser(Mockery::mock(Authenticatable::class));

        // Assert
        $this->assertTrue($guard->check());
        $this->assertFalse($guard->guest());
    }

    /**
     * @testdox No método "user", um usuário deve ser retornado de imediato quando não for "null" (quando existir)
     */
    public function testUserMethodReturnsCachedUser()
    {
        // Arrange
        $guard = $this->getGuard();
        $user = Mockery::mock(Authenticatable::class);

        // Act
        $guard->setUser($user);

        // Assert
        $this->assertSame($user, $guard->user());
    }

    /**
     * @testdox Teste do método "loginUsingId"
     */
    public function testLoginUsingIdLogsInWithUser()
    {
        // Arrange
        $guard = $this->getGuard(true);
        $user = Mockery::mock(Authenticatable::class);

        $guard->getProvider()->shouldReceive('retrieveById')->once()->with(2)->andReturn($user);
        $guard->shouldReceive('login')->once()->with($user);

        // Act && Assert
        $this->assertSame($user, $guard->loginUsingId(2));
    }

    /**
     * @testdox Quando o usuário for "null" então o método "loginUsingId" deve retornar "false" (falha no login)
     */
    public function testLoginUsingIdFailure()
    {
        // Arrange
        $guard = $this->getGuard(true);

        $guard->getProvider()->shouldReceive('retrieveById')->once()->with(2)->andReturn(null);
        $guard->shouldNotReceive('login');

        // Act && Assert
        $this->assertFalse($guard->loginUsingId(2));
    }

    /**
     * @testdox Teste do método "onceUsingId"
     */
    public function testOnceUsingIdSetsUser()
    {
        // Arrange
        $guard = $this->getGuard(true);
        $user = Mockery::mock(Authenticatable::class);

        $guard->getProvider()->shouldReceive('retrieveById')->once()->with(2)->andReturn($user);
        $guard->shouldReceive('setUser')->once()->with($user);

        // Act && Assert
        $this->assertSame($user, $guard->onceUsingId(2));
    }

    /**
     * @testdox Quando o usuário for "null" então o método "onceUsingId" deve retornar "false" (falha no login)
     */
    public function testOnceUsingIdFailure()
    {
        // Arrange
        $guard = $this->getGuard(true);

        $guard->getProvider()->shouldReceive('retrieveById')->once()->with(2)->andReturn(null);
        $guard->shouldNotReceive('setUser');

        // Act && Assert
        $this->assertFalse($guard->onceUsingId(2));
    }

    /**
     * @testdox Teste do método "once"
     */
    public function testLoginOnceSetsUser()
    {
        // Arrange
        $guard = $this->getGuard(true);
        $user = Mockery::mock(Authenticatable::class);

        $guard->getProvider()->shouldReceive('retrieveByCredentials')->once()->with(['foo'])->andReturn($user);
        $guard->getProvider()->shouldReceive('validateCredentials')->once()->with($user, ['foo'])->andReturn(true);
        $guard->shouldReceive('setUser')->once()->with($user);

        // Act && Assert
        $this->assertTrue($guard->once(['foo']));
    }

    /**
     * @testdox Quando o usuário for "null" então o método "once" deve retornar "false" (falha no login)
     */
    public function testLoginOnceFailure()
    {
        // Arrange
        $guard = $this->getGuard(true);
        $user = Mockery::mock(Authenticatable::class);

        $guard->getProvider()->shouldReceive('retrieveByCredentials')->once()->with(['foo'])->andReturn($user);
        $guard->getProvider()->shouldReceive('validateCredentials')->once()->with($user, ['foo'])->andReturn(false);

        // Act && Assert
        $this->assertFalse($guard->once(['foo']));
    }

    /**
     * Recupera os "mocks" necessários para o `CookieAuthGuard`.
     *
     * @return array
     */
    protected function getMocks(): array
    {
        return [
            Mockery::mock(UserProvider::class),
            Request::create('/', 'GET'),
            Mockery::mock(CookieJar::class),
        ];
    }

    /**
     * Recupera o encrypter customizado utilizado para encriptar e decriptar os dados,
     * nesse caso se trata do payload dos cookies.
     *
     * - Obs: esse mesmo encrypter, é utilizado no projeto legado para gerar os cookies
     * e a API ter a capacidade de decriptar o payload e recuperar o usuário, seguindo
     * o fluxo como se o usuário autenticado no projeto legado tivesse autenticado na API.
     *
     * @return \Illuminate\Encryption\Encrypter
     */
    private function encrypter(): Encrypter
    {
        return app('cookie-auth-encrypter');
    }

    /**
     * Recupera o dado/informação essencial do payload do cookie de autenticação
     * e refresh, ou seja, a informação primária para autenticar e verificar se o
     * cookie é válido (existe um ID de um usuário no mesmo).
     *
     * @return string
     */
    private function getCookiePayload(string $cookieName): string
    {
        return $this->encrypter()->encrypt(
            CookieValuePrefix::create($cookieName, $this->encrypter()->getKey()) . json_encode(
                ['user' => static::USER_COOKIE_DATA]
            ),
            false
        );
    }

    /**
     * Recupera o mock do usuário.
     *
     * @param int $timesCalled
     *
     * @return array
     */
    private function getUser(int $timesCalled = 1): array
    {
        $mockUser = Mockery::mock(Authenticatable::class);

        $mockUser->shouldReceive('getAuthIdentifier')
                 ->andSet('role', static::USER_COOKIE_DATA['role'])
                 ->times($timesCalled)
                 ->andReturn($userId = static::USER_COOKIE_DATA['id']);

        return [$mockUser, $userId];
    }

    /**
     * Recupera a instância do guard de tratamento dos cookies.
     *
     * @param bool $toMock
     *
     * @return \App\Auth\CookieAuthGuard|\Mockery\MockInterface
     */
    private function getGuard(bool $toMock = false): CookieAuthGuard|MockInterface
    {
        [$provider, $request] = $this->getMocks();
        $constructorArguments = [$this->encrypter(), $provider];

        if ($toMock) {
            return Mockery::mock(CookieAuthGuard::class, $constructorArguments)->makePartial();
        }

        return tap(new CookieAuthGuard(...$constructorArguments), function ($guard) use ($request) {
            $guard->setCookieJar(app('cookie'));

            $guard->setRequest($request);
        });
    }
}
