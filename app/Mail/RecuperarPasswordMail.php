<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Queue\SerializesModels;

class RecuperarPasswordMail extends Mailable
{
    use Queueable, SerializesModels;

    // Esta variable guardará el código de 6 dígitos
    public $codigo;

    /**
     * Se recibe el código al instanciar la clase
     */
    public function __construct($codigo)
    {
        $this->codigo = $codigo;
    }

    /**
     * Define el remitente y el asunto del correo
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            from: new Address('blackstyle.services@gmail.com', 'Soporte Condominio'),
            subject: 'Código de recuperación: ' . $this->codigo,
        );
    }

    /**
     * Define qué vista se va a usar y qué datos pasar
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.recuperar_password', // Asegúrate de crear esta vista
            with: [
                'codigo' => $this->codigo,
            ],
        );
    }

    /**
     * Archivos adjuntos (opcional)
     */
    public function attachments(): array
    {
        return [];
    }
}
