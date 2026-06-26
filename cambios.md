# Registro de cambios

## 2026-06-25
- `app/Http/Controllers/Api/ClienteExpressController.php`
  - `verificarNit()`: agregado `datosRepre` (bool). `true` si nombreRepLegal, identificacionRepLegal y lugarExpedicion estan TODOS vacios. `false` si al menos uno tiene datos reales.
  - `verificarNit()`: agregado `datosRepreIncompletos` (bool). `true` si ALGUNO de los 3 (nombreRepLegal, identificacionRepLegal, lugarExpedicion) esta vacio. `false` solo si los 3 tienen datos reales.
  - `store()`: corregido bug - validacion `unique:nit` ahora excluye el cliente actual en UPDATE (`reglasCliente($cliente->id)`). Antes fallaba con error 422 al actualizar un cliente existente.
  - `datosRepreVacios()` y `datosRepreIncompletos()`: ahora tratan el string "No definido" como vacio (antes solo null/empty).
- `app/Http/Controllers/Api/SolicitudExpressController.php`
  - `solicitud()` INSERT y UPDATE: ahora usan `parsearPesoYPrecio()` en vez de `calcularPrecio()` directo.
  - `calcularPrecio()`: actualizado para soportar 3 formatos de peso: opcion numerica ("2"), texto nuevo Wati ("2. Pequeño generador < 20 Kg - $80.000"), texto antiguo ("Menos de 20 kg").
  - `parsearPesoYPrecio()`: nuevo metodo que devuelve `[peso, precio]` parseando cualquiera de los 3 formatos. Mapea opciones 1-7 a sus pesos y precios correspondientes.
- `app/Http/Controllers/CotizacionExpresController.php`
  - `destroy()`: ahora elimina realmente los registros de `solicitudes_express` por `idSolicitud`.
  - `eliminarLote()`: nuevo metodo POST que recibe `ids[]` y elimina en lote via `whereIn`.
- `resources/views/cotizacionExpres/index.blade.php`
  - Agregado boton rojo de eliminar (tacho) individual en columna Acciones con confirmacion.
  - Agregado checkbox por fila, y boton "Eliminar seleccionados (N)" que aparece al marcar items.
  - Corregido bug: `});` extra rompia el JS de eliminacion masiva.
  - Mejorado estilo de checkboxes (rojos, 16px) y removido checkbox del header.
- `routes/web.php` - Agregada ruta `POST /cotizacion-expres/eliminar-lote` para eliminacion masiva.
- BD: `clientes_express` id=19, `lugarExpedicion` seteado a NULL para pruebas.
