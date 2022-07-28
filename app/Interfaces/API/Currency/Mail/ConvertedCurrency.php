<?php

namespace App\API\Currency\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;
use CurrencyDomain\DTO\ConvertedCurrencyResultDTO;
use Illuminate\Contracts\Queue\ShouldBeEncrypted;

class ConvertedCurrency extends Mailable implements ShouldQueue, ShouldBeEncrypted
{
    use Queueable;
    use SerializesModels;

    /**
     * The number of times the job may be attempted.
     *
     * @var int
     */
    public int $tries = 5;

    /**
     * The DTO instance.
     */
    public ConvertedCurrencyResultDTO $details;

    /**
     * Create a new message instance.
     *
     * @param \CurrencyDomain\DTO\ConvertedCurrencyResultDTO $details
     *
     * @return void
     */
    public function __construct(ConvertedCurrencyResultDTO $details)
    {
        $this->details = $details;

        $this->onQueue('emails');
        $this->subject('Valor convertido de ' . $details->originCurrency() . ' => ' . $details->targetCurrency());
    }

    /**
     * Build the message.
     *
     * @return $this
     *
     * @codeCoverageIgnore
     */
    public function build()
    {
        return $this->markdown('currency-interface::conversion-details');
    }
}
