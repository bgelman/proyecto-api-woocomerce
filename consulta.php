<?php
// Datos de conexión a la API de WooCommerce
$consumerKey = 'ck_e75d6181150167c6b61e091f37538d5c9fbd2c38';
$consumerSecret = 'cs_3438fd8f6170ab53a7e930eb6d6778f62f3ea7a8';
$storeURL = 'https://mariabenita.com.ar/wp-json/wc/v3/';

// Endpoint y parámetros de la solicitud
$endpoint = $storeURL . 'orders';
$params = array(
    'consumer_key' => $consumerKey,
    'consumer_secret' => $consumerSecret
);

// Datos de conexión a la base de datos MySQL
$host = 'localhost';
$usuario = 'c0250220_physis';
$contraseña = 'KUkaraki10';
$baseDeDatos = 'c0250220_physis';

// Conexión a la base de datos MySQL
$conexion = mysqli_connect($host, $usuario, $contraseña, $baseDeDatos);

// Verificar si la conexión fue exitosa
if (!$conexion) {
    die('Error al conectar con la base de datos: ' . mysqli_connect_error());
}

//Obtener ultimas ordenes 
// Inicializar cURL
$ch = curl_init();

// Configurar opciones de cURL
curl_setopt($ch, CURLOPT_URL, $endpoint);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
curl_setopt($ch, CURLOPT_USERPWD, $consumerKey . ':' . $consumerSecret);

// Realizar la solicitud GET a la API
curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');

// Ejecutar la solicitud y obtener la respuesta
$response = curl_exec($ch);

// Verificar el código de respuesta
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
if ($httpCode === 200) {
    // La solicitud fue exitosa
   // echo $response;
} else {
    // Hubo un error en la solicitud
    echo 'Error en la solicitud. Código de respuesta: ' . $httpCode;
}
//Svar_dump($response);
// Cerrar la conexión cURL
//curl_close($ch);
//var_dump(json_decode($response));

$ordenesWoo = json_decode($response);
//echo 'Orden woocomerce: '. $ordenesWoo[0]->id. '- estado:'.$ordenesWoo[0]->status;




// Obtener ultima orden de la tabla "ordenes"
$sqlGet = "SELECT * FROM ordenes ORDER BY id_orden_wp DESC LIMIT 1";

$resultado = mysqli_query($conexion, $sqlGet);
if (mysqli_num_rows($resultado) > 0) {
    $ordenBD = mysqli_fetch_assoc($resultado);
    
   } else {
        echo 'No se encontraron órdenes.';
    }

//echo '<br> ultima orden BD '.$ordenBD['id_orden_wp']. '- estado:'.$ordenBD['estado_pedido'];


//Controlo si hay ordenes nuevas 
if ($ordenBD['id_orden_wp'] < $ordenesWoo[0]->id){
    //la orden de woocommerce es mas grande? 
    echo 'Hay ordenes nuevas! <br><br>';

    //traigo las ordenes a partir de la fecha de la ultima orden
    // Fecha a partir de la cual se desea obtener las órdenes (en formato YYYY-MM-DD)

    $datetime = new DateTime($ordenBD['fecha']);

 
        $fechaDesde = $datetime->format(DateTime::ATOM);// Updated ISO8601

        // Endpoint y parámetros de la solicitud GET
        $endpoint = $storeURL . 'orders';
        $params = array(
            'consumer_key' => $consumerKey,
            'consumer_secret' => $consumerSecret,
            'after' => $fechaDesde
        );
        echo($fechaDesde .' <br><br>');
        // Construir la URL con los parámetros
        $url = $endpoint . '?' . http_build_query($params);
        echo($url .' <br><br>');
        // Inicializar cURL
        $ch = curl_init();

        // Configurar opciones de cURL
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
        curl_setopt($ch, CURLOPT_USERPWD, $consumerKey . ':' . $consumerSecret);

        // Realizar la solicitud GET a la API
        $response = curl_exec($ch);

        // Verificar el código de respuesta
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        if ($httpCode === 200) {
            // La solicitud fue exitosa
         //   echo $response;


            // Decodificar el JSON en un arreglo asociativo
            $ordenes = json_decode($response, true);
           // var_dump($ordenes);
echo($ordenes .' <br><br>');
            foreach ($ordenes as $orden) {

                //Get del cliente

                //Enviar orden a physis
                echo '<br>ID: ' . $orden['id'] . '<br>';
                echo 'status: ' . $orden['status'] . '<br>';
                echo 'Total: $' . $orden['billing']['first_name'] . '<br>';

                echo 'Enviar a physis';
                echo '-------------------<br>';
                // Datos del endpoint de la API
                $endpoint = 'https://api-ejemplo.com/endpoint'; //endpoing de physis
                $apiKey = 'tu_api_key';

                //DATOS DEL CLIENTE
                $cliente = array(
                    'id' => $orden['customer_id'],
                    'physis_id' => " ",
                    'name' => $orden['billing']['first_name'],
                    'document_type' => $orden['billing']['first_name'],
                    'document_number' => $orden['billing']['email'],
                    'email' =>  $orden['billing']['email'],
                    'phone' =>  $orden['billing']['phone'],
                    'city' => $orden['billing']['city'],
                    'postal_code' =>$orden['billing']['postcode'],
                    'neighborhood' => $orden['billing']['address_2'],
                    'address_street' => $orden['billing']['address_1'],
                    'address_number' => $orden['billing']['address_2'],
                    'address_floor' => $orden['billing']['address_2'],
                    'address_department' => $orden['billing']['address_2']

                );

                $pago = array(
                    'payment_at' => $orden['date_completed'],
                    'payment_method' => $orden['payment_method'],
                    'method_physis_id' => " ",
                    'total' =>  $orden['total'],
                    'cart_hash' =>  $orden['cart_hash'],
                    'total_tax' =>  $orden['total_tax'],
                    'discount_total' =>  $orden['discount_total'],
                );


                // Datos del cuerpo de la solicitud POST
                $data = array(

                    
                    'id' => $orden['id'],
                    'created_at' => $orden['date_created'],
                    'total' => $orden['total'],
                    'customer' => $cliente,
                    'items' => $orden['line_items'],
                    'shipping' => $orden['shipping_lines'],
                    'payment' =>  $pago
                   
                    
                );

                // Convertir los datos a formato JSON
                    $jsonData = json_encode($data);

                    echo "<br>". $jsonData ."<br><br><br>";

                    // Configurar opciones de la solicitud
                  /*   $options = array(
                        'http' => array(
                            'header'  => "Content-Type: application/json\r\n" .
                                        "Authorization: Bearer $apiKey\r\n",
                            'method'  => 'POST',
                            'content' => $jsonData
                        )
                    );

                    // Crear el contexto de la solicitud
                    $context = stream_context_create($options);

                    // Realizar la solicitud POST a la API
                    $response = file_get_contents($endpoint, false, $context);

                    // Verificar la respuesta de la API
                    if ($response === false) {
                        echo 'Error al realizar la solicitud del post.';
                        // hacemos algo ? 
                    } else {
                        echo 'Respuesta de la API:<br>';
                        echo $response;
                    } */

                //si se envio guardo los datos en la BD

                // Insertar un registro en la tabla "ordenes"
                $sqlInsert = "INSERT INTO ordenes (id_orden_wp, fecha, estado_pedido,exporto) VALUES (".$orden['id'].", '".$orden['date_created']."', '".$orden['status']."',1)";
                    if (mysqli_query($conexion, $sqlInsert)) {
                        echo '<br> <br> Orden '.$orden['id'].' insertado correctamente .';
                        $sqlInsert= "";
                    } else {
                        echo 'Error al insertar el registro: ' . mysqli_error($conexion);
                    }
                // si no se hizo el post 
                // que hacemos ? guardamos en la BD con el error? enviamos un mail para avisar del error? 



            
            }

            
    


        } else {
            // Hubo un error en la solicitud get inicial
            echo 'Error en la solicitud. Código de respuesta: ' . $httpCode;
        }

        // Cerrar la conexión cURL
        curl_close($ch);


}

// Insertar un registro en la tabla "ordenes"
/*$sqlInsert = "INSERT INTO ordenes (id_orden_wp, fecha, estado_pedido,exporto) VALUES (".$ultimaOrden.", '12/05/23', 'accept',1)";
if (mysqli_query($conexion, $sqlInsert)) {
    echo 'Registro insertado correctamente.';
} else {
    echo 'Error al insertar el registro: ' . mysqli_error($conexion);
}*/


// Verificar si se obtuvieron resultados
if (mysqli_num_rows($resultado) > 0) {
    while ($fila = mysqli_fetch_assoc($resultado)) {
        echo 'ID: ' . $fila['id_orden_wp'] . ', fecha: ' . $fila['fecha'] . ', estado_pedido: ' . $fila['estado_pedido'] . '<br>';
    }
} else {
    echo 'No se encontraron registros.';
}

// Cerrar la conexión a la base de datos
mysqli_close($conexion);




// Datos del endpoint de la API
$endpoint = 'https://api-ejemplo.com/endpoint'; //endpoing de physis
$apiKey = 'tu_api_key';

// Datos del cuerpo de la solicitud POST
$data = array(
    'nombre' => 'Juan',
    'edad' => 30
);





//Codigo del post
/*
// Convertir los datos a formato JSON
$jsonData = json_encode($data);

// Configurar opciones de la solicitud
$options = array(
    'http' => array(
        'header'  => "Content-Type: application/json\r\n" .
                     "Authorization: Bearer $apiKey\r\n",
        'method'  => 'POST',
        'content' => $jsonData
    )
);

// Crear el contexto de la solicitud
$context = stream_context_create($options);

// Realizar la solicitud POST a la API
$response = file_get_contents($endpoint, false, $context);

// Verificar la respuesta de la API
if ($response === false) {
    echo 'Error al realizar la solicitud.';
} else {
    echo 'Respuesta de la API:<br>';
    echo $response;
}
*/
?>

