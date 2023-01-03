<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class InvoiceMailable extends Mailable
{
    use Queueable, SerializesModels;

    public $comprobanteUrl;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($comprobanteUrl)
    {
        $this->comprobanteUrl = $comprobanteUrl;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->subject('Comprobante de compra')
                    ->markdown('emails.invoice')
                    ->attach($this->comprobanteUrl);
    }
}
