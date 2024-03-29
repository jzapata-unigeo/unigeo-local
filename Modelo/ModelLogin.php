<?php

require_once 'Conexion.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require '../src/Exception.php';
require '../src/PHPMailer.php';
require '../src/SMTP.php';
require_once '../controlador/funhelp.php';

class ModeloLogin {

    public $usuario;
    public $password;
    public $mensaje;
    public $fecven;

    public function checklogin($usuario, $password) {
        try {
            session_start();
            $sql = "select * FROM usuario WHERE Email = ?";
            $connect = conexion::con();
            $connect->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $query = $connect->prepare($sql);
            $query->bindParam(1, $usuario, PDO::PARAM_STR);
            $query->execute();
            $row = $query->fetch(PDO::FETCH_ASSOC);
            if ($query->rowCount() < 1) {
                $_SESSION['mensajeu'] = "Usuario no existe, intenta de nuevo";
                header('Location: ../vistas/Login.php');
            } else {
                $hash = $row['Password'];
                if (password_verify($password, $hash)) {
                    session_start();
                    $_SESSION['usuario'] = $usuario;
                    $_SESSION['login'] = true;
                    header('Location: ../vistas/index.html');
                } else {
                    $_SESSION['mensajeu'] = "Contraseña incorrecta, intenta de nuevo";
                    header('Location: ../vistas/Login.php');
                }
            }
        } catch (Exception $e) {
            echo $e->getMessage();
        }
    }

    public function recpass($usuario) {
        try {

            $sql = "SELECT * FROM usuario WHERE Email = ?";
            $connect = conexion::con();
            $connect->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $query = $connect->prepare($sql);
            $query->bindParam(1, $usuario, PDO::PARAM_STR);
            $query->execute();
            $row = $query->fetch(PDO::FETCH_ASSOC);

            if ($query->rowCount() < 1) {
                $_SESSION['mensajeu'] = "La direccion de correo no esta registrada en el sistema";
                header('Location: ../vistas/Login.php');
            } else {
                $rs = array("correo" => $row['Email'], "nombre" => $row['Nombre'], "apellido" => $row['Apellido']);
//header('Location: ../web/registro.php');
            }
        } catch (Exception $e) {
            echo $e->getMessage();
        }

        return $rs;
    }

    public function fechact() {
        date_default_timezone_set('America/Bogota');
        $this->fechact = date('Y-m-d H:i');
        return $this->fechact;
    }

    public function fechaven() {
        date_default_timezone_set('America/Bogota');
        $fechact = date('Y-m-d H:i');
        $fechat = strtotime('+1 hour', strtotime($fechact));
        $fechaven = date('Y-m-d H:i', $fechat);
        return $fechaven;
    }

    public function token() {
        $this->token = bin2hex(random_bytes(5));
        return $this->token;
    }

    public function sendmail($address, $nombre, $token) {
        $template = file_get_contents('../vistas/template.php');
        $template = str_replace("{{name}}", $nombre, $template);
        $template = str_replace("{{action_url_2}}", 'https://URL/' . $token, $template);
        $template = str_replace("{{action_url_1}}", 'https://URL/' . $token, $template);
        $template = str_replace("{{year}}", date('Y'), $template);
        $template = str_replace("{{operating_system}}", funhelp::getOS(), $template);
        $template = str_replace("{{browser_name}}", funhelp::getBrowser(), $template);


        try {

            $mail = new PHPMailer(TRUE);
            $mail->setLanguage('es', 'src/phpmailer.lang-es.php');
            $mail->Body = $template; //asigna a $body el contenido del correo electrónico
            $mail->SMTPDebug = 0; // Activar los mensajes de depuración, 
            $mail->IsSMTP(); // Indica que se usará SMTP para enviar el correo
            $mail->Host = "smtp.gmail.com"; // Asigna la dirección smtp
            $mail->SMTPAuth = true; // Activar autenticación segura a traves de SMTP, necesario para gmail
            $mail->Username = "unigeou@gmail.com"; //Indica la direccion de correo de envio
            $mail->Password = "unigeo01**"; //password
            $mail->SMTPSecure = "tls"; // Indica que la conexión segura se realizará mediante TLS
            $mail->Port = 587; // Asigna el puerto usado por GMail para conexion con su servidor SMTP
            $mail->SetFrom('unigeou@gmail.com', 'Unigeo'); //Asignar la dirección de correo y el nombre del contacto que aparecerá cuando llegue el correo
            $mail->Subject = "Recuperacion de contraseña - Unigeo"; //Asignar el asunto del correo
            $mail->MsgHTML($template);
            $mail->isHTML(true); //enviar correo con formato HTML 
            $mail->AddAddress($address, $nombre); //Indica aquí la dirección que recibirá el correo que será enviado
            $mail->CharSet = 'UTF-8';
            $mail->send();

            echo 'Mensaje enviado';
        } catch (Exception $e) {

            echo "El mensaje no puede ser enviado. Mailer Error: {$mail->ErrorInfo}";
        }
    }

    public function newpas($usuario, $token, $fecven) {
        try {
            $sql = "UPDATE usuario SET CodRec=?, codven=? WHERE email=?";
            $connect = conexion::con();
            $connect->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $query = $connect->prepare($sql);
            $query->bindParam(1, $token, PDO::PARAM_STR);
            $query->bindParam(2, $fecven, PDO::PARAM_STR);
            $query->bindParam(3, $usuario, PDO::PARAM_STR);
            $query->execute();
            if ($query->execute()) {
                
            }
        } catch (Exception $exc) {
            echo $exc->getTraceAsString();
        }
    }

}
