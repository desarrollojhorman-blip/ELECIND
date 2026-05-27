<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, HasRoles, LogsActivity, Notifiable, SoftDeletes;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->useLogName('usuario')
            ->logOnly(['username', 'nombre', 'apellidos', 'email', 'dni', 'numero_empleado', 'activo', 'tipo_usuario', 'cliente_id'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->setDescriptionForEvent(fn (string $_e) => "Usuario: {$this->username}");
    }

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'username',
        'nombre',
        'apellidos',
        'email',
        'dni',
        'cif',
        'telefono',
        'numero_empleado',
        'tasa_hora',
        'tasa_extra',
        'tasa_festivo',
        'tipo_usuario',
        'cliente_id',
        'activo',
        'preferencias_notificaciones',
        'deleted_by',
        'snapshot_data',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
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
            'password' => 'hashed',
            'activo' => 'boolean',
            'preferencias_notificaciones' => 'array',
            'snapshot_data' => 'array',
            'tasa_hora' => 'decimal:3',
            'tasa_extra' => 'decimal:3',
            'tasa_festivo' => 'decimal:3',
        ];
    }

    public function cliente(): BelongsTo
    {
        return $this->belongsTo(Cliente::class);
    }

    public function proyectos(): BelongsToMany
    {
        return $this->belongsToMany(Proyecto::class, 'proyecto_usuario')
            ->withPivot('rol_en_proyecto')
            ->withTimestamps();
    }

    public function nivelMaximo(): int
    {
        $roles = $this->roles;

        if ($roles->isEmpty()) {
            return 0;
        }

        return (int) $roles->max('nivel');
    }

    public function tieneAccesoWeb(): bool
    {
        return $this->roles->contains(fn ($rol): bool => in_array($rol->getAttribute('acceso'), ['web', 'ambos'], true));
    }

    public function tieneAccesoMovil(): bool
    {
        return $this->roles->contains(fn ($rol): bool => in_array($rol->getAttribute('acceso'), ['movil', 'ambos'], true));
    }
}
