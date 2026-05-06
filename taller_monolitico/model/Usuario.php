<?php
class Usuario
{
    public $id;
    public $nombre;
    public $contacto;
    public $licencia;

    public function __construct($id, $nombre, $contacto, $licencia)
    {
        $this->id = $id;
        $this->nombre = $nombre;
        $this->contacto = $contacto;
        $this->licencia = $licencia;
    }
}
?>
