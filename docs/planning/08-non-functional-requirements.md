# Requisitos no funcionales

## Alcance operativo esperado

- Una sede.
- Menos de 10 usuarios concurrentes inicialmente.
- Acceso desde Internet mediante navegador.
- Cliente Capacitor opcional en una fase posterior.
- SQLite únicamente para desarrollo inicial.
- MySQL desde el primer despliegue real multiusuario.

## Seguridad

- HTTPS obligatorio en producción.
- Contraseñas almacenadas mediante el mecanismo seguro del framework.
- Cookies seguras, `HttpOnly` y política apropiada de `SameSite`.
- Protección CSRF en la interfaz web.
- Limitación de intentos de inicio de sesión y operaciones sensibles.
- Sesiones revocables por dispositivo.
- Nueva autenticación para restauraciones, reaperturas críticas y cambios de propietarios.
- Políticas de autorización en servidor para todas las acciones.
- Validación de tipo, tamaño y contenido permitido en adjuntos.
- Archivos privados fuera del directorio público.
- Secretos únicamente en variables de entorno o almacenamiento seguro del proveedor.
- La auditoría nunca guarda contraseñas, tokens ni secretos completos.

La autenticación de dos factores se deja preparada como mejora posterior, pero no bloquea la primera versión.

## Integridad y consistencia

- Ventas, compras, producciones y pagos deben ser transaccionales.
- Inventario y finanzas se derivan de movimientos confirmados.
- Los asientos financieros confirmados siempre deben quedar balanceados.
- Las reversiones generan documentos opuestos y mantienen el original.
- Las solicitudes HTTP repetidas no deben duplicar operaciones críticas.
- Los cierres de caja y periodos impiden nuevas modificaciones sin autorización.
- En MySQL se utilizarán bloqueos y restricciones apropiadas para evitar dobles consumos concurrentes.

## Rendimiento

Objetivos iniciales en condiciones normales:

- Cargar pantallas operativas comunes en menos de 2 segundos.
- Confirmar una venta sencilla en menos de 3 segundos.
- Confirmar producción en menos de 5 segundos para fórmulas habituales.
- Paginar todos los listados que puedan crecer.
- Ejecutar reportes pesados de forma diferida si superan el tiempo interactivo razonable.
- Mantener búsquedas rápidas con al menos 100.000 movimientos históricos mediante índices y filtros.

Estos son objetivos de producto, no garantías absolutas frente a conexiones lentas o proveedores externos.

## Disponibilidad y recuperación

- Respaldos automáticos diarios de base de datos y adjuntos.
- Retención recomendada: 30 respaldos diarios y 12 mensuales.
- Verificación periódica de integridad.
- Descarga manual permitida al propietario.
- Objetivo inicial de pérdida máxima de datos: 24 horas.
- Objetivo inicial de restauración: dentro de 4 horas una vez disponible el respaldo y la infraestructura.
- Procedimiento de restauración documentado y probado antes de depender del sistema en producción.

## Privacidad

- Cada usuario accede únicamente a los ámbitos autorizados.
- Los habitantes ven por defecto solo sus movimientos.
- Los datos personales no se incluyen innecesariamente en exportaciones.
- Los adjuntos heredan los permisos de su documento.
- No se eliminan automáticamente registros financieros históricos.
- Las descargas y accesos a comprobantes sensibles quedan auditados cuando resulte necesario.

## Usabilidad y accesibilidad

- Interfaz responsive desde 360 px de ancho.
- Acciones frecuentes utilizables desde teléfono y tableta.
- Navegación completa mediante teclado en formularios esenciales.
- Etiquetas visibles y mensajes de validación específicos.
- El color no será el único medio para comunicar estados.
- Contraste suficiente y tamaños táctiles razonables.
- Confirmación explícita antes de completar o revertir documentos.
- Los errores conservan los datos ingresados siempre que sea seguro.

## Compatibilidad

- Versiones recientes de Chrome, Edge, Firefox y Safari.
- Diseño compatible con WebView de Capacitor cuando se implemente.
- API móvil versionada bajo `/api/mobile/v1`.
- Las reglas del dominio no dependerán de Inertia ni del navegador.
- Migraciones y consultas compatibles con SQLite y MySQL dentro del alcance acordado.

## Archivos y adjuntos

- Formatos iniciales: PDF, JPEG y PNG.
- Tamaño máximo recomendado por archivo: 10 MB, configurable.
- Nombres físicos no derivados directamente del nombre suministrado por el usuario.
- Acceso mediante URL temporal o respuesta autorizada del servidor.
- Los respaldos deben incluir archivos y referencias consistentes.

## Observabilidad

- Registro estructurado de errores del servidor.
- Identificador de correlación para operaciones críticas.
- Registro de fallos en trabajos diferidos y respaldos.
- Alerta administrativa visible cuando falle un respaldo.
- Auditoría de accesos y cambios sensibles separada del log técnico.

No se requiere inicialmente una plataforma externa compleja de monitoreo; bastan logs, estado de respaldos y alertas administrativas básicas.

## Mantenibilidad

- Monolito modular con límites explícitos.
- Clases y archivos con una responsabilidad principal.
- Casos de uso independientes de controladores.
- Objetos de valor para dinero y cantidades.
- Integraciones externas detrás de contratos.
- No usar consultas específicas de SQLite que impidan MySQL.
- Documentar decisiones de arquitectura que cambien el plan aprobado.
- Evitar abstracciones genéricas que no tengan al menos un uso claro dentro del dominio.

## Despliegue

- Ambientes separados de desarrollo y producción.
- Producción utiliza MySQL, HTTPS y almacenamiento persistente.
- Migraciones ejecutadas de forma controlada y con respaldo previo cuando exista información real.
- Configuración sensible fuera del repositorio.
- El despliegue debe incluir verificación mínima de inicio de sesión, conexión a base de datos y escritura en almacenamiento.

## Límites expresamente aceptados

- No hay modo offline en la primera versión.
- No hay garantía de sincronización si se pierde Internet durante una operación.
- No hay facturación electrónica DIAN.
- No hay cálculo tributario certificado.
- No hay nómina formal.
- No hay notificaciones por WhatsApp, correo o push inicialmente.
