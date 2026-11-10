<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Representa la reserva de un vehiculo por parte de un cliente.
 *
 * @property int $id
 * @property int $cliente_id
 * @property int $vehiculo_id
 * @property string $fecha_inicio
 * @property string $fecha_fin
 * @property string $estado
 */
final class Reserva extends Model
{
    public const ACTIVA = 'activa';
    public const COMPLETADA = 'completada';
    public const CANCELADA = 'cancelada';

    protected $table = 'reservas';

    protected $fillable = [
        'cliente_id',
        'vehiculo_id',
        'fecha_inicio',
        'fecha_fin',
        'estado',
    ];

    public function cliente(): BelongsTo
    {
        return $this->belongsTo(Cliente::class, 'cliente_id');
    }

    public function vehiculo(): BelongsTo
    {
        return $this->belongsTo(Vehiculo::class, 'vehiculo_id');
    }
}
