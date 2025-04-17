# Blackout

🧾 Manual de Uso — Sistema ESP8266 + PHP
(Proyecto: Detector y Monitor de Apagones)

💡 Descripción General
Este sistema tiene dos componentes que trabajan juntos:

82_Apagon_v5.ino
Es un programa para una placa ESP8266 que detecta apagones (cortes de luz) y problemas de conexión WiFi.
Envia alertas a un servidor web usando HTTP.

index.php
Es un script PHP que recibe los mensajes que envía la ESP8266 y los guarda en un archivo o base de datos para su consulta.

⚙️ ¿Cómo Funciona?
👉 Cuando la ESP8266 detecta un evento importante, como:

Falta de corriente eléctrica (apagón).

Falta de conexión WiFi.

Reinicio o encendido.

envía un mensaje al servidor PHP.
El PHP lo recibe y lo almacena o muestra para que puedas saber cuándo ocurrió el problema.

💻 Explicación del Código
1️⃣ 82_Apagon_v5.ino — Código en la ESP8266
Al encenderse, la ESP se conecta a tu red WiFi.

Envía un mensaje al servidor con la palabra conectado cuando todo está bien.

Si no encuentra WiFi o hay un problema, intenta enviar: nowifi-.

Si detecta un apagón (o se ha reiniciado por pérdida de energía), enviará noluz-.

✅ Ejemplo de uso real:
Supón que tienes un refrigerador y no quieres que se descongele si hay un corte de luz cuando no estás en casa.
Este programa puede avisarte en tiempo real enviando un mensaje a tu servidor.

2️⃣ index.php — Código en el Servidor
Recibe las alertas enviadas desde la ESP8266.

Puede guardar los mensajes en archivos .txt, bases de datos o mostrarlos en una página web.

Te permite ver desde cualquier parte del mundo si ha habido un apagón o si la ESP perdió WiFi.

✅ Ejemplo de uso real:
Tu ESP8266 envía:

arduino
Copiar
Editar
http://tuservidor/index.php?mensaje=noluz-ESP01
El servidor guarda ese texto, lo muestra en una página o lo envía por email/Telegram.

🗂️ Ejemplo de Comunicación Completa

Dispositivo	Acción	Mensaje Enviado
ESP8266	Se conecta correctamente	conectado-ESP01
ESP8266	Pierde WiFi	nowifi-ESP01
ESP8266	Se reinicia (apagón)	noluz-ESP01
Servidor PHP	Recibe y guarda el mensaje	Guarda en log.txt o base de datos
🧠 Posibles Usos

Caso	Descripción
Monitor de Apagones	Saber cuándo hubo cortes de energía en una vivienda o empresa.
Fallo de WiFi	Saber cuándo tu router o conexión se cae.
Supervisión Remota	Monitorizar remotamente dispositivos que no deben apagarse.
Alerta por Telegram/SMS	Ampliable para que index.php envíe avisos automáticos.
🏠 Ejemplo Real
En casa:
Una ESP conectada a la red detecta si hay apagones. Cuando la corriente vuelve, la placa se reinicia y envía noluz-ESP01.
Así sabes la hora exacta del corte.

En oficina o servidor:
La ESP detecta si el servidor o router pierde energía y envía noluz-ESP01. Si pierde solo WiFi, envía nowifi-ESP01.
Así puedes detectar si es un fallo de red o de luz.

💪 Requisitos
📡 Una placa ESP8266.

💻 Un servidor web con PHP (por ejemplo un hosting o una Raspberry Pi).

🕸️ Acceso a Internet para la ESP.


