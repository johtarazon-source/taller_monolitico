<?php
class Reserva
{
    public $id;
    public $idCliente;
    public $idVehiculo;
    public $fechaInicio;
    public $fechaFin;
    public $fechaDevolucion;
    public $estado;

    public function __construct($id, $idCliente, $idVehiculo, $fechaInicio, $fechaFin, $fechaDevolucion, $estado)
    {
        $this->id = $id;
        $this->idCliente = $idCliente;
        $this->idVehiculo = $idVehiculo;
        $this->fechaInicio = $fechaInicio;
        $this->fechaFin = $fechaFin;
        $this->fechaDevolucion = $fechaDevolucion;
        $this->estado = $estado;
    }
}
?>
