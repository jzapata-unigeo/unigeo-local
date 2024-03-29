<?php

require_once 'Conexion.php';

class ModelRegistro {

    public $nombre;
    public $apellido;
    public $email;
    public $fecnacimiento;
    public $localidad;
    public $sexo;
    public $password;
    public $mensaje = false;

    function insertar() {
        try {
            $conect = conexion::con();
            $fecha = date("Y-m-d", strtotime($this->fecnacimiento));
            $pass = password_hash($this->password, PASSWORD_DEFAULT);
            $sql = "insert into usuario (Nombre, Apellido, Email, FecNacimiento,Localidad, sexo, Password)";
            $sql .= " VALUES (?, ?, ?, ?, ?, ?, ?)";
            $conect->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $query = $conect->prepare($sql);
            $query->bindValue(1, $this->nombre);
            $query->bindValue(2, $this->apellido);
            $query->bindValue(3, $this->email);
            $query->bindValue(4, $fecha);
            $query->bindValue(5, $this->localidad);
            $query->bindValue(6, $this->sexo);
            $query->bindValue(7, $pass);
            $query->execute();
            if ($query) {
                $this->mensaje = true;
            }
        } catch (Exception $e) {
            $_SESSION["ermensaje"] = 'Error: ' . $e->getMessage();
        }
    }

    function validar_clave($clave, &$error_clave) {
        if (strlen($clave) < 6) {
            $error_clave = "La clave debe tener al menos 6 caracteres";
            return false;
        }
        if (strlen($clave) > 16) {
            $error_clave = "La clave no puede tener más de 16 caracteres";
            return false;
        }
        if (!preg_match('`[a-z]`', $clave)) {
            $error_clave = "La clave debe tener al menos una letra minúscula";
            return false;
        }
        if (!preg_match('`[A-Z]`', $clave)) {
            $error_clave = "La clave debe tener al menos una letra mayúscula";
            return false;
        }
        if (!preg_match('`[0-9]`', $clave)) {
            $error_clave = "La clave debe tener al menos un caracter numérico";
            return false;
        }
        $error_clave = "";
        return true;
    }

}
