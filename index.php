
<!-- INICIALIZACION -->
    <?php
    session_start();
    header("Refresh: 10");
    header('Content-Type: text/html; charset=utf-8');
    date_default_timezone_set('Europe/Madrid');
    // $logFile = 'blackout_log.txt';
    // EN HISTORICO.TXT SE ALMACENAN TODOS LOS MENSAJES GENERADOS POR LA APLICACION O LLEGADOS DESDE LA ESP8266
    $historico='historico.txt';
    // EN INCIDENCIA.TXT SE ALMACENA LA ULTIMA INCIDENCIA OCURRIDA: DESCONEXION, FALTA LUZ O FALTA WIFI
    $incidencia='incidencia.txt';
    // EN ultimaconexion.txt SE ALMACENA LA FECHA Y HORA CADA 15 SEG O EL TIEMPO ESPECIFICADO EN LA CONFIGURACION DE LA ESP
    $lastReconnectFile = 'ultimaconexion.txt';
    // EN estadotelegram.txt SE ALMACENA NADA O UN si EN CASO DE QUE LA ESP SEA LA ENCARGADA DE ENVIAR MENSAJES A TELEGRAM
    $estadoTelegramFile = 'estadotelegram.txt';
    // $maxFileSize = 1024 * 1024;
    // $lastMessageIdFile = 'last_message_id.txt';

// CONFIGURACION DE TELEGRAM
    include("config.php");

// OBTENEMOS EL MENSAJE DE LA ESP8266
    $message = isset($_GET['msg']) ? trim($_GET['msg']) : '';

// FUNCION PARA ENVIAR MENSAJES A TELEGRAM
    function sendTelegramNotification($message, $apiUrl, $chatId) {
    global $lastMessageIdFile;

    $data = [
        'chat_id' => $chatId,
        'text' => $message
    ];

    $options = [
        'http' => [
            'header' => "Content-type: application/x-www-form-urlencoded\r\n",
            'method' => 'POST',
            'content' => http_build_query($data),
            'ignore_errors' => true
        ]
    ];

    $context = stream_context_create($options);
    $result = @file_get_contents($apiUrl, false, $context);

    if ($result === false) {
        $error = error_get_last();
        file_put_contents('telegram_errors.log', date('Y-m-d H:i:s')." - Error: ".$error['message']."\n", FILE_APPEND);
        return false;
    }

    $responseData = json_decode($result, true);

    if (isset($responseData['ok']) && $responseData['ok'] && isset($responseData['result']['message_id'])) {
        // Guardamos el ID del último mensaje enviado
        file_put_contents($lastMessageIdFile, $responseData['result']['message_id']);
        return $responseData['result']['message_id'];
    } else {
        file_put_contents('telegram_errors.log', date('Y-m-d H:i:s')." - Respuesta inesperada: ".$result."\n", FILE_APPEND);
        return false;
    }
    }

// FUNCION PARA ELIMINAR MENSAJES EN TELEGRAM
    // function deleteTelegramMessage($apiUrl, $chatId) {
    // global $lastMessageIdFile;

    // if (!file_exists($lastMessageIdFile)) {
    //     file_put_contents('telegram_errors.log', date('Y-m-d H:i:s')." - Error: Archivo last_message_id.txt no encontrado\n", FILE_APPEND);
    //     return false;
    // }

    // $messageId = trim(file_get_contents($lastMessageIdFile));

    // if (empty($messageId)) {
    //     file_put_contents('telegram_errors.log', date('Y-m-d H:i:s')." - Error: ID de mensaje vacío\n", FILE_APPEND);
    //     return false;
    // }

    // $data = [
    //     'chat_id' => $chatId,
    //     'message_id' => $messageId
    // ];

    // $options = [
    //     'http' => [
    //         'header' => "Content-type: application/x-www-form-urlencoded\r\n",
    //         'method' => 'POST',
    //         'content' => http_build_query($data),
    //         'ignore_errors' => true
    //     ]
    // ];

    // $context = stream_context_create($options);
    // $result = @file_get_contents($apiUrl . "/deleteMessage", false, $context);

    // if ($result === false) {
    //     $error = error_get_last();
    //     file_put_contents('telegram_errors.log', date('Y-m-d H:i:s')." - Error: ".$error['message']."\n", FILE_APPEND);
    //     return false;
    // }

    // $responseData = json_decode($result, true);

    // if (isset($responseData['ok']) && $responseData['ok']) {
    //     return true;
    // } else {
    //     file_put_contents('telegram_errors.log', date('Y-m-d H:i:s')." - Respuesta inesperada: ".$result."\n", FILE_APPEND);
    //     return false;
    // }
    // }

// GUARDA LA HORA EN UN ARCHIVO
    $timestamp = date('Y-m-d H:i');
    $error = 0;    
    $lastReconnectTime = file_get_contents($lastReconnectFile);
    $timestamp1 = strtotime($lastReconnectTime);
    $timestamp2 = strtotime($timestamp);
    $diferenciaSegundos = abs($timestamp2 - $timestamp1);
    $formatoTiempo = gmdate('H:i:s', $diferenciaSegundos);
    $diferenciaMinutos = ($diferenciaSegundos / 60);

    
// RECUPERA EL ULTIMO MENSAJE DEL HISTORICO
    $archivo = $historico;
    $ultimahistoria = null;
    $handle = fopen($archivo, 'r');
    if ($handle) {
    while (($linea = fgets($handle)) !== false) {
        $linea = trim($linea);
        if (!empty($linea)) {
            $ultimahistoria = $linea;
        }
    }
    fclose($handle);
    }

// COMPRUEBA EL TIPO DE INCIDENCIA Y ENVIA LOS MENSAJES OPORTUNOS

    // DETECTA UNA DESCONEXION

    if ($diferenciaMinutos>1){
        $logMessage = $lastReconnectTime .";desconectado;❌ Desconectado...;"."\n";
        if (explode(";",$logMessage)[1]!=explode(";",$ultimahistoria)[1] && explode(";",$ultimahistoria)[3]==""){
            file_put_contents($incidencia, $logMessage);
            file_put_contents($historico, $logMessage, FILE_APPEND);
            $mensa="Web: ❌ "." El sistema está desconectado desde ".explode(";",$logMessage)[0]."\n";
            sendTelegramNotification($mensa, $telegramAPIUrl, $telegramChatID);
    }
    // }

    // ELIMINA LOS 1500 MENSAJES MAS ANTIGUOS AL LLEGAR A 3000
    $lineas = file($historico, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    if (count($lineas) >= 3000) {
        $lineas = array_slice($lineas, 1500);
        file_put_contents($historico, implode(PHP_EOL, $lineas) . PHP_EOL);
    }
    }
   
    if (!empty($message)) {
    
    // FALTA ALIMENTACION
    if (substr($message,0,5)== 'noluz') {
        $logMessage = $lastReconnectTime.";".$message."\n";
        file_put_contents($historico, $logMessage, FILE_APPEND);
        file_put_contents($incidencia, $logMessage);
        // file_put_contents($logFile, $logMessage);
        $mensa="Web: ".explode(";",$logMessage)[0]." ".explode(";",$logMessage)[2]." ".explode(";",$logMessage)[3]." \n";
        if (trim(file_get_contents($estadoTelegramFile))!="si"){sendTelegramNotification($mensa, $telegramAPIUrl, $telegramChatID);}
    }
    
    //  FALTA WIFI
     if (substr($message,0,6)== 'nowifi') {
        $logMessage = $lastReconnectTime.";".$message."\n";
        file_put_contents($historico, $logMessage, FILE_APPEND);
        file_put_contents($incidencia, $logMessage);
        // file_put_contents($logFile, $logMessage);
        $mensa="Web: ".explode(";",$logMessage)[0]." ".explode(";",$logMessage)[2]." ".explode(";",$logMessage)[3]." minutos\n";
        if (trim(file_get_contents($estadoTelegramFile))!="si"){sendTelegramNotification($mensa, $telegramAPIUrl, $telegramChatID);}
    }

    // TODO ESTA CORRECTO
    if (substr($message,0,9)== 'Conectado') {
        $logMessage = $timestamp.";".$message.";".$diferenciaMinutos."\n";
        if (explode(";",$logMessage)[1]!=explode(";",$ultimahistoria)[1]){
        file_put_contents($historico, $logMessage, FILE_APPEND);
        }
        file_put_contents($lastReconnectFile, $timestamp);
    }

    // CADA CIERTO TIEMPO (CONFIGURADO EN LA ESP) ENVIA UN TELEGRAM COMUNICANDO QUE EL SISTEMA FUNCIONA. SOLO SI HEMOS CONFIGURADO LA ESP PARA QUE NO ENVIE TELEGRAM
    if (substr($message,0,4)== 'Bien'){
        // deleteTelegramMessage($telegramAPIUrl, $telegramChatID);
        $mensa="Web: ".explode(";",$message)[1]."\n";
        if (trim(file_get_contents($estadoTelegramFile))!="si"){sendTelegramNotification($mensa, $telegramAPIUrl, $telegramChatID);}
    }

    }

    // http_response_code(400);

    $estado=$ultimahistoria;
    $noque=file_get_contents($incidencia);
    $fecha=explode(" ",$estado)[0];
    $hora1=explode(" ",$estado)[1];
    $hora=explode(";",$hora1)[0];
    $tipo=explode(";",$estado)[1];
    $mensaje=explode(";",$estado)[2];
    $tiempo=explode(";",$estado)[3];
    if ($tipo=="noluz"){$tipo="Energía";}
    if ($tipo=="nowifi"){$tipo="Wifi";}
    if (explode(";",$estado)[1]=="Conectado"  && explode(";",$estado)[4]=="si"){file_put_contents($estadoTelegramFile, "si");} else {file_put_contents($estadoTelegramFile, "");}
 
    ?>

<!-- GESTION DE LA HORA -->
    <script>
    function padZero(number) {
        return number < 10 ? '0' + number : number;
    }

    function getDayName(dayNumber) {
        const days = [
            'DOM', 'LUN', 'MAR', 
            'MIE', 'JUE', 'VIE', 'SAB'
        ];
        return days[dayNumber];
    }

    function updateClock() {
        var now = new Date();
        
        // Formatear hora
        var hours = padZero(now.getHours());
        var minutes = padZero(now.getMinutes());
        var timeString = hours + ':' + minutes;
        
        // Formatear fecha
        var day = padZero(now.getDate());
        var month = padZero(now.getMonth() + 1); // Los meses van de 0-11
        var year = now.getFullYear();
        var dateString = year + '-' + month + '-' + day;
        
        // Mostrar fecha y hora separadas por espacio
        document.getElementById('clock').textContent = dateString + ' ' + timeString;
        
        // Actualizar el día de la semana (opcional)
        var dayName = getDayName(now.getDay());
        document.getElementById('day').textContent = dayName;
    }

    // Función auxiliar para añadir cero inicial
    function padZero(num) {
        return (num < 10 ? '0' : '') + num;
    }

    // Función para obtener nombre del día
    function getDayName(dayIndex) {
        var days = ['Domingo', 'Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado'];
        return days[dayIndex];
    }
    
    // Actualiza el reloj cada segundo
    setInterval(updateClock, 1000);

    // Inicializa el reloj y el día al cargar la página
    updateClock();
    </script>


<!-- INICIA HTML Y CARGA ESTILOS -->
    <!DOCTYPE html>
    <html lang="es">
    <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Monitor de Estado</title>
    <style>
    body {
        font-family: Arial, sans-serif;
        background-color: #575757FF;
        color: #e0e0e0;
        margin: 0;
        padding: 20px;
        display: flex;
        flex-direction: column;
        align-items: center;
    }
    
    .container {
        width: 100%;
        max-width: 800px;
    }
    
    .status-box {
        background-color: #3E3E3EFF;
        border-radius: 15px;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.3);
        padding: 20px;
        margin-bottom: 20px;
        border: 1px solid #333;
    }
    
    .status-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 15px;
        border-bottom: 1px solid #333;
        padding-bottom: 10px;
    }
    
    .status-title {
        font-size: 24px;
        font-weight: bold;
        color: #ffffff;
    }
    
    .status-time {
        font-size: 18px;
        color: #b0b0b0;
    }
    
    .status-day {
        background-color: #3C83ACFF;
        color: white;
        padding: 5px 10px;
        border-radius: 20px;
        font-size: 14px;
        margin-left: 10px;
    }
    
    .status-content {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 15px;
    }

    @media (max-width: 600px) {
    .status-content {
        grid-template-columns: 1fr;
    }
    }

    .status-item {
        margin-bottom: 10px;
    }
    
    .status-label {
        font-weight: bold;
        color: #a0a0a0;
    }
    
    .status-value {
        color: #e0e0e0;
    }
    
    .status-icon {
        text-align: center;
        grid-column: span 2;
        margin-top: 10px;
    }
    
    .history-box {
        background-color: #3E3E3EFF;
        border-radius: 15px;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.3);
        padding: 20px;
        border: 1px solid #333;
    }
    
    .history-title {
        font-size: 20px;
        font-weight: bold;
        color: #ffffff;
        margin-bottom: 15px;
        border-bottom: 1px solid #333;
        padding-bottom: 10px;
    }
    
    .history-list {
        max-height: 300px;
        overflow-y: auto;
    }
    
    .history-item {
        padding: 3px 0;
        border-bottom: 1px solid #333;
        color: #e0e0e0;
    }
    
    .history-item:last-child {
        border-bottom: none;
    }
    
    .status-connected {
        color: #81C784;
    }
    
    .status-disconnected {
        color: #E57373;
    }
    
    .status-poweroutage {
        color: #FFB74D;
    }
    
    .status-wifiproblem {
        color: #64B5F6;
    }

    /* Estilo para la barra de desplazamiento */
    .history-list::-webkit-scrollbar {
        width: 8px;
    }

    .history-list::-webkit-scrollbar-track {
        background: #2d2d2d;
        border-radius: 10px;
    }

    .history-list::-webkit-scrollbar-thumb {
        background: #555;
        border-radius: 10px;
    }

    .history-list::-webkit-scrollbar-thumb:hover {
        background: #777;
    }
    </style>
    </head>

<!-- MUESTRA INFORMACION EN LA WEB -->
    <body>
    <div class="container">
    <div class="status-box">
        <div class="status-header">
            <div class="status-title">Estado del sistema</div>
            <!-- <div style="display: flex; align-items: center;">
                <div class="status-time" id="clock"></div>
                <div class="status-day" id="day"></div>
            </div> -->
        </div>
        
        <div class="status-content">
            <div class="status-item">
                <div class="status-label">Fecha y hora:</div>
                <div style="display: flex; align-items: center;">
                <div class="status-time" id="clock"></div>
                <div class="status-day" id="day"></div>
                </div>
                <!-- <div class="status-value"><?php echo $fecha." ".$hora; ?></div> -->
            </div>
            
            <div class="status-item">
                <div class="status-label">Estado actual:</div>
                <div class="status-value 
                    <?php 
                        if($tipo == "Conectado") echo "status-connected";
                        elseif($tipo == "Desconectado") echo "status-disconnected";
                        elseif($tipo == "Energía") echo "status-poweroutage";
                        elseif($tipo == "Wifi") echo "status-wifiproblem";
                    ?>
                ">
                    <?php echo $tipo; ?>
                </div>
            </div>
            
            <div class="status-item">
                <div class="status-label">Última incidencia:</div>
                <div class="status-value"><?php echo explode(";",$noque)[0]."<br>".explode(";",$noque)[2]." ".explode(";",$noque)[3]; ?></div>
            </div>
            
            <div class="status-item">
                <div class="status-label">Tiempo sin conexión:</div>
                <div class="status-value">
                    <?php 
                        // echo explode(";",$noque)[3];
                        if ($diferenciaMinutos>1) {echo $formatoTiempo." minutos";}
                    ?>
                </div>
            </div>
            
            <div class="status-icon">
                <?php 
                    if ($tipo=="Conectado") {
                        echo "<img src='aceptar.png' width='100px' alt='Sistema operativo'>";
                    } 
                    if ($tipo=="desconectado" || $tipo=="Energía" || $tipo=="Wifi") {
                        echo "<img src='eliminar.png' width='100px' alt='Sistema no operativo'>";
                    }
                ?>
            </div>
        </div>
    </div>
    
    <div class="history-box">
        <div class="history-title">Histórico de incidencias</div>
        <div class="history-list">
            <?php
            $historyContent = file_exists($historico) ? file_get_contents($historico) : "No hay registros históricos";
            $historyLines = explode("\n", trim($historyContent));
            $historyLines = array_reverse($historyLines);
            
            foreach ($historyLines as $line) {
                if (!empty(trim($line))) {
                    $parts = explode(";", $line);
                    echo "<div class='history-item'>";
                    echo "<strong>" . htmlspecialchars($parts[0]) . "</strong> - ";
                    
                    if (count($parts) > 2) {
                        echo htmlspecialchars($parts[2]);
                        if (count($parts) > 3) {
                            echo " " . htmlspecialchars($parts[3]);
                        }
                    }
                    
                    echo "</div>";
                }
            }
            ?>
        </div>
    </div>
    </div>

<!-- GESTION DE LA HORA -->
    <script>
    function padZero(number) {
        return number < 10 ? '0' + number : number;
    }

    function getDayName(dayNumber) {
        const days = [
            'DOM', 'LUN', 'MAR', 
            'MIE', 'JUE', 'VIE', 'SAB'
        ];
        return days[dayNumber];
    }

    function updateClock() {
        var now = new Date();
        
        // Formatear hora
        var hours = padZero(now.getHours());
        var minutes = padZero(now.getMinutes());
        var timeString = hours + ':' + minutes;
        
        // Formatear fecha
        var day = padZero(now.getDate());
        var month = padZero(now.getMonth() + 1); // Los meses van de 0-11
        var year = now.getFullYear();
        var dateString = year + '-' + month + '-' + day;
        
        // Mostrar fecha y hora separadas por espacio
        document.getElementById('clock').textContent = dateString + ' ' + timeString;
        
        // Actualizar el día de la semana (opcional)
        var dayName = getDayName(now.getDay());
        document.getElementById('day').textContent = dayName;
    }

    // Actualiza el reloj cada segundo
    setInterval(updateClock, 1000);

    // Inicializa el reloj y el día al cargar la página
    updateClock();
    </script>
    </body>
    </html>

<!-- CONTROLES DE PROGRAMACION -->
    <?php
    // echo "<br>";
    // echo $estado;
    // echo "<br>";
    // echo trim(file_get_contents($estadoTelegramFile));
    // echo "<br>";
    // echo $diferenciaMinutos;
    // echo "<br>";

    ?>