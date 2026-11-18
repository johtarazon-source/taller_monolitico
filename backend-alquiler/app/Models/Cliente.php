<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Representa un cliente del negocio de alquiler.
 *
 * @property int $id
 * @property string $nombre
 * @property string|null $telefono
 * @property string|null $correo
 * @property string|null $numero_licencia
 */
final class Cliente extends Model
{
    protected $table = 'clientes';

    protected $fillable = [
        'nombre',
        'telefono',
        'correo',
        'numero_licencia',
    ];

    public function reservas(): HasMany
    {
        return $this->hasMany(Reserva::class, 'cliente_id');
    }
}
