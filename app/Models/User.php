<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable implements MustVerifyEmail
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasRoles, LogsActivity, TwoFactorAuthenticatable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'celular',
        'celular_verificado_at',
        'recibe_notificaciones_whatsapp',
        'fecha_nacimiento',
        'password',
        'google_id',
        'avatar',
        'estado',
        'email_verified_at',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_secret',
        'two_factor_recovery_codes',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'celular_verificado_at' => 'datetime',
            'recibe_notificaciones_whatsapp' => 'boolean',
            'fecha_nacimiento' => 'date',
            'password' => 'hashed',
            'estado' => 'boolean',
            'two_factor_confirmed_at' => 'datetime',
        ];
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->useLogName('usuarios')
            ->logOnly(['name', 'email','celular', 'celular_verificado_at', 'recibe_notificaciones_whatsapp', 'fecha_nacimiento', 'estado', 'google_id', 'avatar', 'email_verified_at'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    public function perfilProveedor()
    {
        return $this->hasOne(PerfilProveedor::class);
    }

    public function getAvatarUrlAttribute(): ?string
    {
        if (! $this->avatar) {
            return null;
        }

        if (str_starts_with($this->avatar, 'http://') || str_starts_with($this->avatar, 'https://')) {
            return $this->avatar;
        }

        return asset($this->avatar);
    }

    public function getInicialAttribute(): string
    {
        return mb_strtoupper(mb_substr($this->name ?: 'U', 0, 1));
    }

    public function solicitudes()
    {
        return $this->hasMany(Solicitud::class, 'cliente_user_id');
    }

    public function historialSolicitudes()
    {
        return $this->hasMany(HistorialSolicitud::class);
    }

    public function respuestasCalificacion()
    {
        return $this->hasMany(RespuestaCalificacion::class);
    }
}
