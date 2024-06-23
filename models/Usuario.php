<?php 

namespace Model;


class Usuario extends ActiveRecord{
        // base de datos
        protected static $tabla = 'usuarios';
        protected static $columnasDB = ['id','nombre','apellido','password','email','telefono','admin','confirmado','token',];

        public $id;
        public $nombre;
        public $apellido;
        public $password;
        public $email;
        public $telefono;
        public $admin;
        public $confirmado;
        public $token;


        public function __construct($args = []){
            $this->id = $args['id'] ?? null;
            $this->nombre = $args['nombre'] ?? '';
            $this->apellido = $args['apellido'] ?? '';
            $this->password = $args['password'] ?? '';
            $this->email = $args['email'] ?? '';
            $this->telefono = $args['telefono'] ?? '';
            $this->admin = $args['admin'] ?? '0';
            $this->confirmado = $args['confirmado'] ?? '0';
            $this->token = $args['token'] ?? '';
            
        }

        // Mensajes de Validación para la creación de una cuenta

        public function validarNuevaCuenta(){
            if(!$this->nombre){
                self::$alertas['error'][] = "el nombre es obligatorio";
            }
            if(!$this->apellido){
                self::$alertas['error'][] = "el apellido es obligatorio";
            }
            
            if(!$this->email){
                self::$alertas['error'][] = "el email es obligatorio";
            }
            if(!$this->password){
                self::$alertas['error'][] = "el password es obligatorio";
            }
            if(strlen($this->password) < 6) {
                self::$alertas['error'][] = "el password debe contener al menos 6 caracteres";
            }

            

            return self::$alertas;
        }

        public function validarLogin(){
            if(!$this->email){
                self::$alertas['error'][] = 'El Email es Obligatorio';
            }
            if(!$this->password){
                self::$alertas['error'][] = 'El Password es Obligatorio';
            }

            return self::$alertas;
        }

        public function validarEmail(){
            if(!$this->email){
                self::$alertas['error'][] = 'El Email es Obligatorio';
            }
            return self::$alertas;
        }
        public function validarPassword(){
            if(!$this->password){
                self::$alertas['error'][] = "El password es obligatorio";
            }
            if(strlen($this->password) < 6){
                self::$alertas['error'][] = "El password tener al menos 6 caracteres";
            }

            return self::$alertas;
        }

        // Revisa si el usuario ya existe
            public function existeUsuario() {
                $query = " SELECT * FROM " . self::$tabla. " WHERE email = '" . $this->email . "' LIMIT 1";

                $resultado = self::$db->query($query);

                if($resultado->num_rows){
                    self::$alertas['error'][] = 'El usuario ya existe';
                }

                return $resultado;
            
            }

        public function hashPassword(){
            $this->password = password_hash($this->password, PASSWORD_BCRYPT);
        }

        public function crearToken(){
            $this->token = uniqid();
        }

        public function comprobarPasswordAndVerificado($password){
            
            $resultado = password_verify($password, $this->password);

            if(!$resultado || !$this->confirmado){
                self::$alertas['error'] [] = 'Password Incorrecto o tu cuenta no a sido autenticada';
            } else{
                return true;
            }
        }

        public function enviarInstrucciones(){
             // Crear el Objeto de email
                $mail = new PHPMailer();
                $mail->isSMTP();
                $mail->Host = 'sandbox.smtp.mailtrap.io';
                $mail->SMTPAuth = true;
                $mail->Port = 2525;
                $mail->Username = '61189b12cd4881';
                $mail->Password = '35cced907289c7';    

                $mail->setFrom('cuentas@appsalon.com');
                $mail->addAddress('cuentas@appsalon.com', 'AppSalon.com');
                $mail->Subject = 'Reestablecer Password';

                // Set HTML
                $mail->isHTML(TRUE);
                $mail->CharSet = 'UTF-8';

                $contenido = "<html>";
                $contenido .= "<p><strong>Hola " . $this->nombre . "</strong> Has solicitad reestablecer tu password, sigue el siguiente enlace para configurar un nuevo password</p>";

                $contenido .= "<p>Presiona aquí: <a href='http://localhost:3000/recuperar?token=" . $this->token . "'>Reestablecer Password</a></p>";
                $contenido .= "<p>Si tu no solicitaste este cambio o esta cuenta, ignora este mensaje</p>";
                $contenido .= "</html>";
                $mail->Body = $contenido;

                // Enviar el mail
                $mail->send();
                }

}