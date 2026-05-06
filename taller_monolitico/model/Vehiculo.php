<?php
class Vehiculo
{
    public $id;
    public $marca;
    public $modelo;
    public $anio;
    public $categoria;
    public $estado;

    public function __construct($id, $marca, $modelo, $anio, $categoria, $estado)
    {
        $this->id = $id;
        $this->marca = $marca;
        $this->modelo = $modelo;
        $this->anio = $anio;
        $this->categoria = $categoria;
        $this->estado = $estado;
    }
}
?>
