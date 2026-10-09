# Validación final de la integración Laravel–FastAPI

Fecha de ejecución: 9 de octubre de 2026.

## Entorno comprobado

- Laravel 11.51.0.
- PHP 8.3.6.
- PHPUnit 11.
- Zona horaria de Laravel: `America/La_Paz`.
- Motor configurado en `http://127.0.0.1:8010`.
- La clave del motor se envía desde Laravel mediante `x-motor-key` y no se entrega al navegador.
- No están instalados Xdebug ni PCOV; por ello no se obtuvo un porcentaje de cobertura de líneas.

## Resultado automatizado

Comando ejecutado:

```powershell
php artisan test
```

Resultado: **63 pruebas correctas, 220 aserciones, 0 fallos**.

También se ejecutaron correctamente:

```powershell
php artisan view:cache
node --check public/assets/js/motorBusquedaAdmin.js
node --check public/assets/js/busquedaServicios.js
vendor/bin/pint --test <archivos de la integración>
git diff --check
```

El chequeo de Pint se limitó deliberadamente a los archivos de esta integración. El proyecto conserva deuda de formato previa en módulos ajenos, que no fue modificada para evitar cambios colaterales.

## Matriz de validación

| Área | Resultado | Evidencia |
|---|---|---|
| Contrato HTTP `/buscar` | Correcto | Consulta, filtros opcionales, ausencia de `limite` y ausencia de clave protegida comprobados con `Http::fake()`. |
| Endpoints `/motor/*` | Correcto | Métodos, rutas y cabecera `x-motor-key` comprobados con `Http::fake()`. |
| Búsqueda asíncrona con seguimiento | Correcto | Inicio, sondeo de estado, fases reales, recuperación de resultados y errores transitorios comprobados. |
| Privacidad de ejecuciones | Correcto | Cada consulta queda asociada al cliente autenticado y otro usuario recibe 403 al intentar consultarla. |
| Personalización visual | Correcto | Configuración persistente, permisos, validación estricta, vista previa, guardado y restauración comprobados. |
| Seguridad de presentación | Correcto | La clave y el identificador FastAPI permanecen en backend; mensajes y explicaciones se escapan y no se admite CSS arbitrario. |
| JSON inválido, HTTP inesperado, clave incorrecta, timeout y conexión | Correcto | Todos producen excepciones o respuestas controladas. |
| Estado FastAPI/MySQL/Qdrant | Correcto | Prueba automatizada y consulta real de solo lectura. |
| Permisos administrativos | Correcto | Se verificaron respuestas 403 y acceso autorizado para estado, indexación, programación e historial. |
| Indexación manual | Correcto con API simulada | Registra usuario, identificador y respuesta aceptada. No se lanzó una indexación real. |
| Indexación automática | Correcto con API simulada | El comando inicia únicamente cuando la configuración corresponde. |
| Prevención de duplicados | Correcto | Lock y detección de ejecución activa comprobados. |
| Seguimiento tras pérdida temporal | Correcto | La ejecución permanece activa y conserva el error de seguimiento. |
| Reinicio de FastAPI | Correcto con API simulada | Un identificador perdido (404) finaliza con error explicativo. |
| Reinicio de Laravel | Correcto | Las ejecuciones persisten en MySQL y el comando recupera registros activos; solicitudes antiguas sin identificador se cierran de forma segura. |
| Métricas finales | Correcto | Nuevos, reindexados, metadatos, sin cambios y eliminados quedan almacenados. |
| Historial | Correcto | Usuario/sistema, fechas, duración, fases, errores, filtros y paginación comprobados. |
| Scheduler | Correcto | La tarea del motor y `backup-automatico` permanecen registradas por separado. |
| Zona horaria y frecuencia | Correcto | Diario, semanal, día, hora, desactivación y próxima ejecución comprobados. |
| Ranking en Laravel | Correcto | Se conserva el orden recibido de FastAPI y no se reordena por puntuación local. |
| Vigencia en MySQL | Correcto | Se excluyen perfiles suspendidos, no aprobados o con usuario inactivo. |
| Puntuaciones internas en frontend | Correcto | No se imprimen `semantic_score` ni `final_score` en el HTML del cliente. |
| Explicaciones y servicios realizados | Correcto | Se presentan los datos informativos del motor sin alterar el orden ni exponer las puntuaciones internas. |
| Solicitud de servicio desde resultados | Integrada | Las tarjetas abren el perfil y reutilizan el formulario existente de solicitudes. |

## Pruebas reales de comunicación y búsqueda

La consulta real `GET /motor/estado`, realizada mediante el servicio Laravel, devolvió:

- FastAPI: activo.
- MySQL: activo.
- Qdrant: activo.
- Tiempo observado: 56,68 ms.

Consultas reales ejecutadas sin filtros:

| Consulta | Total | Primer resultado | Tiempo |
|---|---:|---|---:|
| Necesito cambiar la pantalla de mi celular | 14 | `proveedor:15` | 1304,47 ms |
| Mi laptop se calienta demasiado | 14 | `proveedor:1` | 346,55 ms |
| Mi impresora no imprime | 14 | `proveedor:27` | 336,42 ms |
| Necesito instalar una red WiFi | 14 | `proveedor:26` | 363,44 ms |
| Mi computadora no enciende | 14 | `proveedor:1` | 344,00 ms |

La primera consulta tuvo calentamiento inicial. El promedio de las cuatro siguientes fue aproximadamente 347,60 ms. Esto mide el tiempo total observado por Laravel, no separa internamente embeddings, Qdrant y reranking.

Pruebas reales de filtros con la consulta de laptop:

- Domicilio: 5 resultados; las modalidades inspeccionadas incluían domicilio o mixto.
- Local: 5 resultados; las modalidades inspeccionadas incluían local o mixto.
- Solo disponibles: 14 resultados; la muestra inspeccionada informó `disponible: true`.

Fallos reales, simulados sin modificar `.env`:

- Clave temporalmente incorrecta: error controlado `autorizacion`, HTTP 401.
- URL temporalmente inaccesible: error controlado `timeout`.

También se verificó el contrato real de búsqueda con seguimiento: `POST /motor/busquedas` devolvió un identificador, `GET /motor/busquedas/{identificador}` informó las fases reales y `GET /motor/busquedas/{identificador}/resultados` entregó el ranking final. Laravel conserva únicamente su identificador interno en el navegador.

## Evaluación cualitativa de relevancia

No se calculó Precision@K ni NDCG@K porque no existe un conjunto previo de juicios de relevancia por consulta/proveedor. No debe interpretarse el total de resultados como una métrica de precisión.

La inspección manual de especialidades mostró:

- Pantalla de celular: el primer proveedor ofrece celulares; resultado coherente.
- Sobrecalentamiento de laptop: el primer proveedor ofrece laptops; resultado coherente.
- Impresora: los primeros puestos incluyen proveedores con impresoras; resultado coherente.
- Computadora que no enciende: el primer proveedor ofrece computadoras; resultado coherente.
- Instalación de red WiFi: el primer proveedor no declara una especialidad explícita de redes; los puestos 2 y 3 sí incluyen equipos o servicios de red. Debe revisarse con juicios de relevancia antes de atribuirlo a un error del ranking.

Laravel respetó en todos los casos el orden entregado por FastAPI.

## Pruebas pendientes o no ejecutadas

- Indexación real de proveedores nuevos, modificados, metadatos y eliminados. Requiere un conjunto de datos aislado y restaurable en MySQL/Qdrant; no se alteraron datos reales.
- Apagado real de MySQL y Qdrant. Se validaron sus respuestas activas y los errores HTTP/conexión con simulaciones, pero no se detuvieron servicios compartidos.
- Reinicio físico de FastAPI y Laravel durante una indexación real. La recuperación fue cubierta automáticamente con estados persistidos y respuestas 404 simuladas.
- Prueba de carga concurrente real. Solo se realizaron solicitudes secuenciales controladas para no saturar el entorno.
- Validación visual manual en navegadores y tamaños de pantalla. Blade y JavaScript compilan, pero no se hizo una sesión E2E con navegador.
- Citas: la búsqueda enlaza con el perfil y el flujo existente de solicitud; la creación posterior de citas no fue modificada ni recorrida mediante una prueba E2E.

## Scheduler

`php artisan schedule:list` conserva:

```text
* * * * * backup-automatico
* * * * * php artisan motor:indexaciones-procesar
```

En desarrollo, `php artisan dev` incluye `schedule:work`. El script `composer dev` no lo incluye. No se encontró en el repositorio una definición de cron o Supervisor para producción; el servidor debe ejecutar `php artisan schedule:run` cada minuto.

## Conclusión

La integración principal Laravel–FastAPI, el panel, la personalización visual, los permisos, el historial, el Scheduler y la búsqueda semántica asíncrona superaron las pruebas automatizadas y las comprobaciones reales seguras. Las pruebas que requieren mutar el índice o detener infraestructura permanecen documentadas como pendientes para un entorno aislado. No se modificó el motor Python, no se alteraron fórmulas de ranking, no se añadieron indexaciones disparadas por cambios de proveedores y se conservó intacto el flujo antiguo de “Modo IA”.
