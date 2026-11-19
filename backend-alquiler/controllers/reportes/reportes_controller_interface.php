<?php

namespace app\controllers\reportes;

interface ReportesControllerInterface
{
    /**
     * Indicadores del panel: conteos de la flota, clientes y reservas, reservas recientes,
     * vencidas y proximas a 7 dias.
     *
     * @return array<string, mixed>
     */
    public function get_resumen(): array;
}
