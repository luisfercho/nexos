<html lang="es">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, user-scalable=no, initial-scale=1.0">
        <title>Cuenta creada</title>
    </head>
    <body>
        <p>Hola!</p>
        <p>Se ha registrado tu nueva cuenta de ahorros correctamente, tus datos son:</p>
        <ul>
            <li><b>Numero de cuenta</b> {{ $account->number }}</li>
        </ul>
        <p>Recuerda que tu contraseña son los ultimos 4 digitos de tu identificación</p>
        <p>Gracias por tu atención.</p>
    </body>

</html>