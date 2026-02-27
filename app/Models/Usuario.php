<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Notifications\Messages\MailMessage;

class Usuario extends Authenticatable implements MustVerifyEmail
{
    use HasApiTokens, Notifiable;

    protected $fillable = [
        'nombre',
        'email',
        'clave',
        'es_admin',
        'email_verified_at'
    ];

    protected $hidden = [
        'clave',
        'remember_token'
    ];


public function sendEmailVerificationNotification()
{
    $this->notify(new class extends VerifyEmail {
        public function toMail($notifiable) {
            $urlReact = "http://localhost:5173/verificar/" . $notifiable->id;

            return (new MailMessage)
                ->subject('🏠 Verificación de Cuenta')
                ->greeting('¡Hola, ' . $notifiable->nombre . '!')
                ->line('Para activar tu cuenta, haz clic en el botón de abajo.')
                ->action('Confirmar mi correo', $urlReact)
                ->salutation('Saludos, Administración.');
        }
    });
}
}
