<?php 

namespace Controllers;

use MVC\Router;
use Classes\Email;
use Model\Usuario;


class LoginController{
    public static function login(Router $router){ 
        $auth = new Usuario($_POST);
        $alertas = [];
        if($_SERVER['REQUEST_METHOD'] === 'POST') {
            $auth = new Usuario($_POST);
            $alertas = $auth->validarLogin();

            if(empty($alertas)) {
                // Comprobar que exista el usuario
                $usuario = Usuario::where('email', $auth->email);
                if($usuario) {
                    // Verificar el password
                    
                    if( $usuario->comprobarPasswordAndVerificado($auth->password) ) {
                        // Autenticar el usuario
                        session_start();

                        $_SESSION['id'] = $usuario->id;
                        $_SESSION['nombre'] = $usuario->nombre . " " . $usuario->apellido;
                        $_SESSION['email'] = $usuario->email;
                        $_SESSION['login'] = true;

                        
                        // Redireccionamiento
                        if($usuario->admin === "1") {
                            $_SESSION['admin'] = $usuario->admin ?? null;
                            header('Location: /admin');
                        } else {
                            header('Location: /cita');
                        }
                        exit(); // Asegúrate de detener la ejecución del script después de la redirección
                    }
                } else {
                    Usuario::setAlerta('error', 'Usuario no encontrado');
                }

            }
        }
        $alertas = Usuario::getAlertas();
        
        $router->render('auth/login',[
            "alertas" => $alertas,
            "auth" => $auth
        ]);
    }


    public static function logout(){ 
        session_start();

        $_SESSION = [];

        header('location: /' );
    }

    public static function olvide(Router $router){ 

        $alertas = [];

        if($_SERVER['REQUEST_METHOD'] === 'POST'){
            $auth = new Usuario($_POST);
            $alertas = $auth->validarEmail();
            
            if(empty($alertas)){
                $usuario = Usuario::where('email',$auth->email);

                if($usuario && $usuario->confirmado === "1"){
                    
                    // Generar un token
                    $usuario->crearToken();
                    $usuario->guardar();

                    // Enviar el email
                    $email = new Email($usuario->nombre, $usuario->email, $usuario->token);
                    $email->enviarInstrucciones();

                    //Alerta de exito
                    Usuario::setAlerta('exito','Revise su bandeja de entrada');
                    
                } else{
                    Usuario::setAlerta('error','El usuario no existe o no está confirmado');
                    
                }
            }
        }

        $alertas = Usuario::getAlertas();
        
        $router->render('auth/olvide-password',[
            'alertas' => $alertas
        ]);
    }
    public static function recuperar(Router $router){
        $alertas = [];
        $error = false;
        
        $token = s($_GET['token']);

        // Buscar ususario por su token
        $usuario = Usuario::where('token', $token);

        if(empty($usuario)){
            Usuario::setAlerta('error','Token no Valido');
            $error = true;
        }

        if($_SERVER['REQUEST_METHOD'] === 'POST'){
            // leer el nuevo password y guardarlo

            $password = new Usuario($_POST);
            $alertas = $password->validarPassword();
            
            
            if(empty($alertas)){
                $usuario->password = null;
                $usuario->password = $password->password;
                $usuario->hashPassword();
                $usuario->token = null;

                $resultado = $usuario->guardar();
                if($resultado){
                    header('location: /');
                }

            }
            
        }
        
        $alertas = Usuario::getAlertas();
        $router->render('auth/recuperar-password',[
            "alertas" => $alertas,
            "error" => $error
        ]);
    }
    public static function crear(Router $router){ 
        $usuario = new Usuario;

        // Alertas Vacias
        $alertas = [];
        if($_SERVER['REQUEST_METHOD'] === 'POST'){
            $usuario->sincronizar($_POST);
            $alertas = $usuario->validarNuevaCuenta();

            // Revisar que alertas este vacío
            if(empty($alertas)){
                // Verificar que el usuario no esté registrado
                $resultado = $usuario->existeUsuario();

                if($resultado->num_rows){
                    $alertas = Usuario::getAlertas();
                } else {
                    // Hashear el password
                    $usuario->hashPassword();

                    // generar un Token único
                    $usuario->crearToken();

                    //Enviar el Email al usuario con su token

                    $email = new Email($usuario->email, $usuario->nombre,  $usuario->token);
                    $email->enviarConfirmacion();

                    // Crear el usuario
                    $resultado = $usuario->guardar();
                    

                    if($resultado){
                        header('Location: /mensaje');
                    }

                }
            }
        }

        $router->render('auth/crear-cuenta',[
            'usuario' => $usuario,
            'alertas'=> $alertas
            
        ]);
    }


    public static function mensaje (Router $router){

        $router->render('auth/mensaje');

    }

    public static function confirmar (Router $router){
            $alertas = [];

            $token = s($_GET['token']);

            $usuario = Usuario::where('token', $token);

            if(empty($usuario)){
                // Mostrar mensaje de error
                Usuario::setAlerta('error','Token No Válido');
            } else{
                //Modificar a usuario confirmado
                

                $usuario->confirmado = "1";
                $usuario->token = null;
                $usuario->guardar();
                Usuario::setAlerta('exito','Cuenta Comprobada Correctamente');
            }
            
        // Obtener alertas
        $alertas = Usuario::getAlertas();            

        //Renderizar la vista
        $router->render('auth/confirmar-cuenta',[
            'alertas' => $alertas
        ]);
    }

}