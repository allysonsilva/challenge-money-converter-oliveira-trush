<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Support\ThirdPartyServices\OpenExchangeRates\Facades\ExchangeRate;

/**
 * phpcs:disable SlevomatCodingStandard.TypeHints.PropertyTypeHint
 */
class UpdateCurrencyExchangeCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'currency-exchange:update';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Updates exchange currencies with the latest version in the external API.';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        ExchangeRate::refresh();

        $this->info('Moedas atualizadas com sucesso ✅');

        return 0;
    }
}
