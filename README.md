# 📘 Manual de Usuario
## Sistema de Supervisión: ESP8266 + Pantalla TFT + Servidor PHP + Telegram

---

## 🤖 Introducción

Este manual está diseñado para que cualquier usuario comprenda el funcionamiento general del sistema de monitoreo basado en **[ESP8266🔗](https://es.aliexpress.com/item/1005008285868316.html?spm=a2g0o.order_list.order_list_main.15.5ad3194dwJRqke&gatewayAdapt=glo2esp) con pantalla de 0.9”** y servidor web PHP, incluyendo su integración con **Telegram**.

![S3590cd3747434bdead931e3ae90ee80cG](https://github.com/user-attachments/assets/cd0fb5c5-eac9-45aa-8296-2266bb9888d3)

El sistema permite detectar cortes de energía, pérdidas de conexión WiFi o fallos en la red, notificando en tiempo real al usuario por medio de:
- Mensajes automáticos al servidor PHP.
- Notificaciones en Telegram.
- Información visual en la pantalla TFT.

---

## 🏢 Situaciones y Mensajes del Sistema

| Situación                   | Acción de la ESP8266            | Acción del Servidor / Telegram |
|-----------------------------|---------------------------------|---------------------------------|
| Energía y WiFi OK          | Envía `conectado` al servidor   | El servidor registra `conectado` y opcionalmente notifica. |
| WiFi Desconectado           | Muestra "Sin WiFi" en pantalla  | No puede enviar datos hasta recuperar conexión. |
| Corte de Energía detectado  | Envía `noluz-` al servidor      | El servidor puede reenviar alerta a Telegram. |
| Recuperación de energía     | Envía `conectado` al servidor   | El servidor puede notificar en Telegram la recuperación. |

---

## 👁️ Información que Muestra la Pantalla

- **Conexión OK:** Estado "WiFi Conectado" y hora del último envío.
- **Sin WiFi:** Mensaje de alerta "Sin conexión WiFi".
- **Corte de Energía:** Indicador de fallo y posible mensaje si es detectado antes del apagado.

La pantalla sirve como primer punto de diagnóstico local para que el usuario pueda actuar rápidamente.

---

## 💻 Información que Muestra el PHP

- Recibe los mensajes `conectado`, `nowifi-` y `noluz-` desde la ESP8266.
- Puede guardar un historial de eventos.
- Puede mostrar en tiempo real el último estado recibido.
- Puede ejecutar código adicional para notificar por **Telegram** cuando:
  - Se detecte `noluz-` (corte de energía).
  - Se detecte `nowifi-` (fallo de conexión).
  - Se recupere la conexión (`conectado`).

---

## 🚀 Beneficios de Implementar Este Proyecto

- ⚡ **Supervisión en Tiempo Real**: Sabrás al instante si tu sistema ha perdido energía o conexión.
- 🚨 **Alertas Inmediatas**: Recibirás notificaciones automáticas en **Telegram** sin necesidad de revisar manualmente.
- 📊 **Registro de Eventos**: El servidor puede almacenar todas las alertas, facilitando el análisis de incidencias.
- 👁️ **Visualización Local**: La pantalla te permite comprobar el estado sin depender de otros dispositivos.
- 🔗 **Escalable y Adaptable**: Puedes conectar múltiples ESP8266 al mismo servidor para cubrir diferentes zonas.

---

## 🧪 Conclusión

El sistema permite tanto monitoreo local como remoto, con avisos claros y automáticos cuando:
- Hay un corte de energía.
- Se pierde la conexión a Internet.
- Se recupera el servicio.

De esta forma siempre estarás informado de lo que ocurre, y podrás actuar rápidamente si algo va mal, mejorando la seguridad y fiabilidad de tus instalaciones.

