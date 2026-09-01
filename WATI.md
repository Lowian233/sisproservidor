# Documentacion de integraciones Wati

## Configuracion

**Archivo:** `.env`
```env
WATI_ENDPOINT=https://live-mt-server.wati.io/10102814
WATI_TOKEN=<JWT Bearer Token>
```

**Archivo:** `config/services.php:48-51`
```php
'wati' => [
    'endpoint' => env('WATI_ENDPOINT', 'https://live-mt-server.wati.io/300356'),
    'token' => env('WATI_TOKEN', ''),
],
```

---

## APIs de Wati consumidas

| API Wati | Metodo | Uso en el proyecto |
|---|---|---|
| `GET /api/v1/getMessages/{phone}` | Obtener historial de mensajes | Descarga de documentos, verificacion de pagos, debug |
| `GET /api/v1/getMedia?fileName=` | Descargar archivos (PDF, imagenes) | `WatiMediaService`, webhooks |
| `POST /api/v2/sendTemplateMessage` | Enviar mensaje de plantilla | Envio de reportes Excel |
| `POST /api/v1/sendSessionFile/{numero}` | Enviar archivo adjunto | Envio de reportes Excel |

---

## 1. Flujo de Clientes Express (Wati Chatbot)

Las rutas estan en `routes/api.php:20-28`, sin autenticacion para que Wati pueda llamarlas.

**Controlador:** `app/Http/Controllers/Api/ClienteExpressController.php`

### 1.1 Verificar NIT
```
GET /api/v1/clientes/verificar-nit/{nit}
```
- Busca el cliente por NIT en `clientes_express`.
- Si existe: retorna `existe: true`, datos del cliente, y sus sedes enumeradas (opcion 1, 2, 3...).
- Si no existe: retorna `existe: false`.
- Las sedes se formatean con numeros de opcion para que Wati las muestre como lista seleccionable.

### 1.2 Upsert de cliente (crear o actualizar)
```
POST /api/v1/clientes
Body: { nit, nombreEmpresa, ciudadEmpresa?, direccion?, numeroEmpresa?, ... }
```
- Si el NIT ya existe: actualiza solo los campos enviados.
- Si el NIT no existe y viene `nombreEmpresa`: crea el cliente.
- Si el NIT no existe y NO viene `nombreEmpresa`: retorna error 404 indicando que envie nombreEmpresa.

### 1.3 Consultar sede
```
GET /api/v1/clientes/{idCliente}/sede?opcion=2
```
- Resuelve la opcion numerica elegida por el usuario en Wati a una sede concreta.

### 1.4 Crear sede (desde el chat)
```
POST /api/v1/clientes/{idCliente}/sede
Body: { nombreSede, direccion?, localidad? }
```
- Wati: caso "el usuario indica una nueva ubicacion por chat".

### 1.5 CRUD adicional
- `GET /api/v1/clientes` - Listar todos
- `GET /api/v1/clientes/{id}` - Ver uno
- `POST /api/v1/clientes/{id}` - Actualizar

---

## 2. Flujo de Solicitudes Express

**Controlador:** `app/Http/Controllers/Api/SolicitudExpressController.php`

```
POST /api/v1/solicitud
```

Endpoint unificado que maneja INSERT y UPDATE:

**INSERT** (cuando llega `tipoResiduo`):
- Genera un `idSolicitud` autoincremental
- Crea registro en `solicitudes_express` con tipoResiduo, peso, precio, localidad, estado, idSede, idCliente, RequiereContrato
- Calcula precio automaticamente segun el peso: Menos de 12 kg = $60.000, Menos de 20 kg = $80.000, etc.

**UPDATE** (cuando NO llega `tipoResiduo`):
- Actualiza todas las filas con el mismo `idSolicitud`
- Campos actualizables: idCliente, idSede, localidad, estado, RequiereContrato, peso, precio

---

## 3. Flujo de Recepcion de PDFs (Webhooks)

**Controlador:** `app/Http/Controllers/WebhookController.php`

### 3.1 Webhook principal - Recibir PDF
```
POST /api/v1/webhook/wati
Body: { idCliente, pdfUrl }
```
1. Recibe `idCliente` y `pdfUrl`
2. Intenta descargar el PDF:
   - Primero: URL absoluta (HTTP GET directo)
   - Fallback: `WATI GET /api/v1/getMedia` con el nombre del archivo
3. Guarda en `public/documentos/{idCliente}/{año}/{mes}/`

### 3.2 Webhook documentos - Descargar multiples archivos
```
POST /api/v1/webhook/wati-documentos
Body: { numberPhone, idCliente, numberDocument, sheet, minutosAtras }
```
1. Crea carpeta `public/documentos/{idCliente}/{año}/{mes}/`
2. Instancia `DescargarDocumentosWati` job que:
   - Itera paginas de `GET /api/v1/getMessages/{phone}`
   - Filtra solo mensajes entrantes (descarta `owner=us` y `eventType=sent`)
   - Solo procesa tipos `document` e `image`
   - Descarga cada archivo via `WatiMediaService::download()`
   - Respeta `timestampLimite` y `numberDocument` como limites

### 3.3 Debug - Ver mensajes recientes
```
GET /api/v1/debug/wati-mensajes/{phone}
```
- Retorna los ultimos 5 mensajes del numero para depuracion.

---

## 4. Flujo de Verificacion de Pagos

**Controlador:** `app/Http/Controllers/Api/VerificarPagoController.php`
**Job:** `app/Jobs/VerificarPagoWati.php`

### 4.1 Iniciar verificacion
```
POST /api/v1/verificar-pago
Body: { numberPhone, monto?, diasMargen? }
```
1. Limpia resultados anteriores del telefono
2. Ejecuta `VerificarPagoWati` job que:
   - Busca el ultimo archivo (imagen/PDF) enviado por el usuario via `GET /api/v1/getMessages/{phone}`
   - Descarga el archivo via `WatiMediaService::download()`
   - Extrae texto del comprobante:
     - **PDF digital:** extrae texto localmente (sin OCR)
     - **PDF escaneado/imagen:** envia a `https://api.ocr.space/parse/image` (API key en `OCR_SPACE_KEY`)
     - Si el PDF excede 1MB, lo convierte a JPEG con Imagick antes del OCR
   - Del texto extraido busca: valor ($), fecha, NIT
   - Valida que el comprobante mencione "PROSARC"
   - Valida que el monto coincida (si se especifico)
   - Guarda resultado en `storage/app/verificaciones/{phone}.json`

### 4.2 Consultar resultado
```
GET /api/v1/resultado-pago/{phone}?idCliente=17&monto=80000
```
1. Si no hay resultado aun: retorna `"Estamos verificando tu pago, espera unos segundos..."`
2. Si hay resultado: guarda los comprobantes en `public/documentos/{idCliente}/{año}/{mes}/` (idempotente via marcador `.saved`)
3. Retorna JSON con `ok`, `factura_valida`, `mensaje` (texto para mostrar al usuario en Wati) y, si el pago es válido y se envía `idCliente`, `fecha_programada` (`Y-m-d`). La fecha también se agrega a `mensaje`.

### 4.3 Metodos de extraccion de datos

`VerificarPagoWati::extraerValor()` - Busca patrones como `$80.000`, `$80,000`, `COP 80000`

`VerificarPagoWati::extraerFecha()` - Busca formatos: `YYYY-MM-DD`, `DD/MM/YYYY`, `DD de mes de YYYY`

`VerificarPagoWati::extraerNit()` - Busca `NIT: 123456789-0` o numeros de 9-10 digitos (filtrando contextos como "celular", "cuenta", etc.)

### 4.4 Mensajes de respuesta

`VerificarPagoWati::enriquecerResultado()`:
- **Pago valido:** `"Tu comprobante de pago fue verificado correctamente."`
- **No es de PROSARC:** `"El comprobante no parece ser de PROSARC. Verifica que el pago se haya hecho a PROSARC S.A. ESP."`
- **Monto no coincide:** `"El monto encontrado ($X) no coincide con el valor esperado ($Y)."`
- **Error:** `"No pudimos validar tu comprobante de pago. Por favor intenta de nuevo."`

---

## 5. Flujo de Envio de Reportes

**Controlador:** `app/Http/Controllers/CotizacionExpresController.php`
**Vista:** `resources/views/cotizacionExpres/index.blade.php`

### 5.1 Envio via servidor (Wati API)
```
POST /cotizacion-expres/reporte/enviar
Body: { numeros: ["573001234567", ...] }
```
1. Genera Excel con todas las cotizaciones
2. Guarda temporalmente en `storage/app/temp/`
3. Para cada numero:
   - Envia template Wati: `POST /api/v2/sendTemplateMessage?whatsappNumber={numero}` con template `envio_reporte_excel`
   - Espera 1 segundo
   - Envia archivo: `POST /api/v1/sendSessionFile/{numero}` con el Excel adjunto
4. Elimina el archivo temporal

### 5.2 Envio via WhatsApp Web (fallback)
Si el envio por Wati falla, el JavaScript del frontend abre `https://wa.me/{numero}?text={mensaje}` en nuevas pestañas.

---

## 6. Servicio de Descarga de Medios

**Archivo:** `app/Services/WatiMediaService.php`

`WatiMediaService::download($dataPath, $textoNombre, $type)`:
1. Si `dataPath` es URL absoluta → descarga directa
2. Si no → llama a `GET {endpoint}/api/v1/getMedia?fileName={path}` probando variantes:
   - `dataPath` original
   - `basename(dataPath)`
   - `textoNombre` (nombre textual del archivo)
   - `basename(textoNombre)`
3. Valida que el contenido sea binario real (PDF o imagen), no JSON/HTML
4. Detecta la extension desde la firma binaria del archivo

---

## 7. Jobs en Background

### 7.1 DescargarDocumentosWati
**Archivo:** `app/Jobs/DescargarDocumentosWati.php`
- Implementa `ShouldQueue` (timeout: 300s, 1 intento)
- Parametros: `numberPhone`, `numberDocument`, `sheet`, `carpeta`, `timestampLimite`
- Itera paginas de mensajes de Wati, descarga documentos/imagenes entrantes
- Usa `WatiMediaService::download()` para cada archivo

### 7.2 VerificarPagoWati
**Archivo:** `app/Jobs/VerificarPagoWati.php`
- Implementa `ShouldQueue` (timeout: 120s, 1 intento)
- Parametros: `numberPhone`, `monto`, `diasMargen`
- Busca el ultimo comprobante, hace OCR, valida monto/PROSARC/NIT
- Guarda resultado en `storage/app/verificaciones/{phone}.json`

---

## 8. Comandos de Consola

### 8.1 wati:descargar
```
php artisan wati:descargar {jobFile}
```
- Version background del `DescargarDocumentosWati` job (implementacion alternativa)
- Lee parametros de un archivo JSON, lo elimina tras leer
- NOTA: No usa `WatiMediaService`, llama a `getMedia` directamente

### 8.2 wati:verificar-pago
```
php artisan wati:verificar-pago {jobFile}
```
- Wrapper para ejecutar `VerificarPagoWati` desde consola
- Lee parametros de archivo JSON

---

## 9. Diagrama de flujo general

```
WhatsApp (usuario)
    │
    ▼
Wati Cloud API
    │
    ├── Mensaje entrante → Webhook de Wati → SisPRO API
    │
    ├── SisPRO → verificarNit(nit)
    │   ├── Cliente existe → retorna datos + sedes
    │   └── Cliente nuevo → crea cliente → retorna ID
    │
    ├── SisPRO → POST /solicitud (tipoResiduo, peso, ...)
    │   └── Crea/actualiza solicitudes_express
    │
    ├── SisPRO → verificar-pago (numberPhone, monto)
    │   ├── Descarga comprobante desde Wati
    │   ├── OCR (local o OCR.space)
    │   └── Valida monto, PROSARC, NIT
    │
    └── SisPRO → enviarReporte (numeros)
        ├── Genera Excel
        ├── Envia template Wati
        └── Adjunta Excel via sendSessionFile
```

---

## 10. Archivos involucrados (17 archivos)

| # | Archivo | Rol |
|---|---------|-----|
| 1 | `.env` | Credenciales WATI_ENDPOINT, WATI_TOKEN |
| 2 | `config/services.php` | Configuracion wati |
| 3 | `routes/api.php` | 11 endpoints para Wati |
| 4 | `routes/web.php` | 1 endpoint de reporte |
| 5 | `app/Http/Controllers/Api/ClienteExpressController.php` | API de clientes para Wati |
| 6 | `app/Http/Controllers/Api/SolicitudExpressController.php` | API de solicitudes |
| 7 | `app/Http/Controllers/Api/VerificarPagoController.php` | API de verificacion de pagos |
| 8 | `app/Http/Controllers/WebhookController.php` | Webhooks de recepcion |
| 9 | `app/Http/Controllers/CotizacionExpresController.php` | Envio de reportes |
| 10 | `app/Services/WatiMediaService.php` | Descarga de archivos de Wati |
| 11 | `app/Jobs/DescargarDocumentosWati.php` | Job: descargar documentos |
| 12 | `app/Jobs/VerificarPagoWati.php` | Job: verificar pagos + OCR |
| 13 | `app/Console/Commands/DescargarDocumentosWatiCommand.php` | Comando: wati:descargar |
| 14 | `app/Console/Commands/VerificarPagoWatiCommand.php` | Comando: wati:verificar-pago |
| 15 | `resources/views/cotizacionExpres/index.blade.php` | UI de envio WhatsApp |
| 16 | `resources/views/cotizacionExpres/show.blade.php` | Texto referencia Wati |
| 17 | `resources/views/emails/Express/sendReciboExpress.blade.php` | Link WhatsApp estatico |

---

## 11. Aclaraciones operativas (importante)

### 11.1 WATI en el menu lateral
- No existe un modulo lateral llamado exactamente "WATI".
- La parte funcional visible para usuario esta principalmente en **Cotizaciones Express** (envio de reportes) y en los flujos API/Webhook.
- Por eso puede parecer que "no existe WATI" aunque la integracion si este activa en backend.

### 11.2 Que depende de permisos de usuario
- El acceso a **Cotizaciones Express** depende de roles permitidos por `Permisos::TODOPROSARC` en `config/menu.php`.
- Si el usuario no tiene ese rol, no vera la opcion aunque la integracion WATI siga funcionando en API.

### 11.3 Que depende de ambiente/configuracion
- Si `WATI_ENDPOINT` o `WATI_TOKEN` estan vacios/invalidos, fallaran llamadas a WATI (`getMessages`, `getMedia`, `sendTemplateMessage`, `sendSessionFile`).
- Si `OCR_SPACE_KEY` no esta configurado, la validacion OCR de comprobantes puede fallar para imagenes/PDF escaneados.

---

## 12. Checklist rapido de diagnostico

Usar este checklist cuando "WATI no funciona":

1. Verificar variables en `.env`:
   - `WATI_ENDPOINT`
   - `WATI_TOKEN`
   - `OCR_SPACE_KEY` (si aplica validacion OCR)

2. Confirmar que existen rutas API:
   - `POST /api/v1/webhook/wati`
   - `POST /api/v1/webhook/wati-documentos`
   - `GET /api/v1/debug/wati-mensajes/{phone}`
   - `POST /api/v1/verificar-pago`
   - `GET /api/v1/resultado-pago/{phone}`

3. Si no aparece en menu, validar rol del usuario (`UsRol`, `UsRol2`) contra permisos de menu.

4. Revisar logs de Laravel (`storage/logs/laravel.log`) buscando:
   - `Webhook Wati recibido`
   - `Wati template fallido`
   - `Wati archivo fallido`
   - `Webhook Wati API fallida`

5. Si hay errores HTTP 401/403 en WATI, normalmente es token invalido/expirado.

6. Si hay error de OCR, validar tamano/tipo de archivo y clave `OCR_SPACE_KEY`.

---

## 13. Runbook corto (soporte)

Caso reportado: "WATI no funciona"

1. Confirmar configuracion en `.env`
- `WATI_ENDPOINT` con URL valida de WATI.
- `WATI_TOKEN` vigente.
- `OCR_SPACE_KEY` si el caso incluye validacion de comprobantes.

2. Confirmar rutas activas
- `POST /api/v1/webhook/wati`
- `POST /api/v1/webhook/wati-documentos`
- `GET /api/v1/debug/wati-mensajes/{phone}`
- `POST /api/v1/verificar-pago`
- `GET /api/v1/resultado-pago/{phone}`

3. Validar menu/permisos (si el problema es visual)
- Si no aparece opcion en lateral, revisar `UsRol` y `UsRol2`.
- La UI relacionada esta en "Cotizaciones Express", no en un menu llamado "WATI".

4. Revisar logs
- Archivo: `storage/logs/laravel.log`
- Buscar: `Webhook Wati recibido`, `Wati template fallido`, `Wati archivo fallido`, `Webhook Wati API fallida`.

5. Interpretacion rapida
- `401/403` en llamadas a WATI: token invalido o expirado.
- `404` en `getMedia`: nombre/ruta del archivo no coincide.
- OCR sin resultado: archivo no legible, tamano alto, o `OCR_SPACE_KEY` faltante.

6. Prueba minima de conectividad
- Ejecutar `GET /api/v1/debug/wati-mensajes/{phone}` con un numero real de prueba.
- Si responde OK, conectividad base WATI esta operativa.
