<?php

namespace Core\Auth;

use Illuminate\Support\Str;

class HandleDomain
{
    /**
     * Verifica se o domínio da request (de acordo com o header `referer`) é o mesmo
     * domínio da API, ou seja, se a requisição foi originada no mesmo domínio da API ou por
     * meio de um outro sistema (CORS).
     *
     * @param string|null $headerDomain
     *
     * @return bool
     */
    public function isSameDomain(?string $headerDomain): bool
    {
        if (is_null($headerDomain)) {
            return false;
        }

        $domain = $this->parseURLDomain($headerDomain);

        return Str::is(array_filter(config('auth.cookies.stateful', [])), $domain);
    }

    private function parseURLDomain(string $urlDomain): ?string
    {
        $domainFQDN = filter_var($urlDomain, FILTER_VALIDATE_DOMAIN, FILTER_FLAG_HOSTNAME);

        if (! empty($domainFQDN)) {
            return $domainFQDN;
        }

        return parse_url($urlDomain, PHP_URL_HOST);
    }
}
