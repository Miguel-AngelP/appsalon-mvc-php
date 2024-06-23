<h1 class="pagina-titulo">Olvidé mi Contraseña</h1>

<p class="descripcion-pagina"> Reestable tu password escribiendo tu email</p>

<?php

  include_once __DIR__ . '/../templates/alertas.php';

?>

<form class="formulario" action="/olvide" method="POST">
    <div class="campo">
        <label for="email">Email</label>
        <input 
            type="email"
            id="email"
            name="email"
            placeholder="Tu Email"
        />
    </div>

    <input type="submit" class="boton" value="Enviar Instrucciones">

</form>

<div class="acciones">
    <a href="/">¿Ya tienes una cuenta? Inicia sesión</a>
    <a href="/crear-cuenta">¿Aun no tienes una cuenta? Crear Una</a>
</div>