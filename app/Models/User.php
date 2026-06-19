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
        'tasa_lab_noche',
        'tasa_festivo',
        'tasa_fest_noche',
        'tasa_extra',
        'tasa_ex_lab_noc',
        'tasa_ex_fes',
        'tasa_ex_fes_noct',
        'tasa_plus_reten',
        'tipo_usuario',
        'cliente_id',
        'gestiona_todos_clientes',
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
            'gestiona_todos_clientes' => 'boolean',
            'preferencias_notificaciones' => 'array',
            'snapshot_data' => 'array',
            'tasa_hora' => 'decimal:3',
            'tasa_lab_noche' => 'decimal:3',
            'tasa_festivo' => 'decimal:3',
            'tasa_fest_noche' => 'decimal:3',
            'tasa_extra' => 'decimal:3',
            'tasa_ex_lab_noc' => 'decimal:3',
            'tasa_ex_fes' => 'decimal:3',
            'tasa_ex_fes_noct' => 'decimal:3',
            'tasa_plus_reten' => 'decimal:3',
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

    /** Clientes que gestiona este usuario (solo roles con solo_clientes_asignados). */
    public function clientesGestionados(): BelongsToMany
    {
        return $this->belongsToMany(Cliente::class, 'cliente_user_gestion');
    }

    /**
     * IDs de clientes gestionados para aplicar scoping en listados.
     *
     * - null: el usuario no tiene restricción → ve TODOS los clientes
     *         (admins, superadmin… y también jefes con gestiona_todos_clientes).
     * - array (posiblemente vacío): el usuario está scoped → solo los de la lista.
     *
     * @return int[]|null
     */
    public function idsClientesGestionados(): ?array
    {
        $tieneScoping = $this->roles->contains(
            fn (Role $r): bool => (bool) $r->getAttribute('solo_clientes_asignados')
        );

        if (! $tieneScoping) {
            return null;
        }

        // Marca "ve todos los clientes (presentes y futuros)" para jefes que
        // gestionan toda la cartera. Anula el scoping aunque el rol tenga el flag.
        if ($this->gestiona_todos_clientes) {
            return null;
        }

        return $this->clientesGestionados()->pluck('id')->all();
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
