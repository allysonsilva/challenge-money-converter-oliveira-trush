# Laravel Money Converter

[![PHP Version][ico-php]][link-php]
[![Laravel Version][ico-laravel]][link-laravel]
[![CI Status][ico-actions]][link-actions]
[![PHPCS - GitHub Workflow Status](https://github.com/allysonsilva/challenge-money-converter/actions/workflows/phpcs.yml/badge.svg)](https://github.com/allysonsilva/challenge-money-converter/actions/workflows/phpcs.yml)
[![PHPMD - GitHub Workflow Status](https://github.com/allysonsilva/challenge-money-converter/actions/workflows/phpmd.yml/badge.svg)](https://github.com/allysonsilva/challenge-money-converter/actions/workflows/phpmd.yml)
[![Test Coverage](https://raw.githubusercontent.com/allysonsilva/challenge-money-converter/main/badge-coverage.svg)](https://github.com/allysonsilva/challenge-money-converter/actions/workflows/run-tests.yml)

## 🚀 Instalação / Visão Geral

- Execute o comando **`make docker/config-env`** para criar o arquivo `docker/.env` com as variáveis de ambiente do *docker-compose* configuradas corretamente!

- Configure a variável de ambiente `FORCE_MIGRATE` para `true` no arquivo `docker/php/services/app/.env.container` para que na inicialização do container o comando `php artisan migrate --force` possa ser executado.

- A aplicação utiliza a API de [Open Exchange Rates](https://openexchangerates.org/) para recuperar as taxas de câmbio mais recentes disponíveis e realizar as operações de conversão entre moedas.
  - Configure a variável de ambiente `EXCHANGE_RATES_API_KEY` no `.env` da aplicação com a chave de API da conta. Para fins de testes, utilize `b74dbb5cb09c4c3e9a05a2dc0d62b92d`.

- **Utilize o comando `make docker/up` para inicializar a aplicação.**

- Para acessar a rota de conversão de moedas (`api/v1/currency/convert`), é necessário estar logado, e como não existe nenhum usuário pré-cadastrado, então, um novo usuário deve ser gerado:
  - Acessar o container da aplicação com: `docker exec -ti app_currency bash`
  - Acessar o *Tinker* do Laravel: `php artisan tinker`
  - Criar um novo usuário: `Core\Models\User::factory()->create()`
    - Por padrão a senha gerada é: `password`

- Com o container da aplicação em execução e a chave de `EXCHANGE_RATES_API_KEY` configurado, é necessário atualizar o banco de dados (`redis`) com as cotações das últimas moedas, para que a aplicação possa fazer as operações diretamente do cache, ao invés de utilizar a conversão da API. Execute o comando **`php artisan currency-exchange:update`**.

- Caso queira executar *workers* (*Ex.: envio de email*) para manipular as filas no Laravel, será necessário executar o container responsável pela manipulação de fila, com o comando `make docker/queue/up`

## 📝 Utilização da API

- Para fazer requisição na aplicação, será necessário recuperar a porta no host que está vinculado a porta `8000` no container. Utilize o comando `docker port app_currency`.

- Para fazer *login*, *logout* e *refresh* na API é necessário definir o header de `Referer` com o mesmo valor de acesso da URL da API, normalmente como `http://127.0.0.1` e o header `Accept` como `application/json`

- Para fazer *login*, utilize a URI `POST api/v1/auth/login` com o seguinte body:
    ```json
    {
        "email": "email@mail.com",
        "password": "password"
    }
    ```
    - Após fazer o *login* ou *refresh*, irá ser gerado 2 cookies necessário para autenticação na API que são: `api_token` e `api_refresh`

- Para fazer *logout*, utilize a URI `DELETE api/v1/auth/logout` com o *cookie* de `api_token` anexado a requisição

- Para fazer *refresh*, utilize a URI `PUT api/v1/auth/refresh` com o *cookie* de `api_refresh` anexado a requisição

## 🧪 Executar Testes

- Descomentar a linha `${DOCKER_PHP_APP_PATH}/.env.testing.container` do *docker-compose* da aplicação (`docker/php/services/app/docker-compose.yml`) para que as variáveis de ambiente nesse arquivo substitua as do arquivo `${DOCKER_PHP_APP_PATH}/.env.container`, por conta da precedência das variáveis na inicialização do container do *docker-compose*.

- Acessar o container `docker exec -ti app_currency bash` e executar o comando `composer tests` para realizar os testes da aplicação

- Para executar as ferramentas de qualidade de código (PHPCS e PHPMD), execute os comandos:
  - `composer code-quality:standard`
  - `composer code-quality:mess`

[ico-php]: https://img.shields.io/static/v1?label=php&message=%E2%89%A58.1&color=777BB4&logo=php
[ico-laravel]: https://img.shields.io/static/v1?label=laravel&message=%E2%89%A59.0&color=ff2d20&logo=laravel
[ico-actions]: https://github.com/allysonsilva/challenge-money-converter/actions/workflows/run-tests.yml/badge.svg

[link-php]: https://www.php.net
[link-laravel]: https://laravel.com
[link-actions]: https://github.com/allysonsilva/challenge-money-converter/actions/workflows/run-tests.yml
