<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Representa un vehiculo disponible para alquiler.
 *
 * @property int $id
 * @property string $marca
 * @property string $modelo
 * @property int $anio
 * @property string|null $categoria
 * @property string $estado
 */
final class Vehiculo extends Model
{
    public const DISPONIBLE = 'disponible';
    public const ALQUILADO = 'alquilado';
    public const MANTENIMIENTO = 'mantenimiento';

    protected $table = 'vehiculos';

    protected $fillable = [
        'marca',
        'modelo',
        'anio',
        'categoria',
        'estado',
    ];

    protected $casts = [
        'anio' => 'integer',
    ];

    public function reservas(): HasMany
    {
        return $this->hasMany(Reserva::class, 'vehiculo_id');
    }
}
