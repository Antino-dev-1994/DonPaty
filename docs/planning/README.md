# Planeación de DonPaty

Este directorio contiene la fuente de verdad funcional y técnica del sistema de administración de la panadería y el hogar.

## Estado

- Planeación funcional y técnica: base completa, pendiente de aprobación final.
- Implementación: no iniciada.
- Arquitectura acordada: monolito modular Laravel con Inertia, Vue 3 y TypeScript.
- Desarrollo inicial: SQLite.
- Producción accesible desde Internet: MySQL y HTTPS.
- Aplicación instalable futura: Capacitor, sin funcionamiento offline en la primera versión.

## Documentos

1. [Visión, alcance y decisiones](01-product-vision.md)
2. [Reglas de negocio](02-business-rules.md)
3. [Modelo de dominio](03-domain-model.md)
4. [Backlog funcional](04-functional-backlog.md)
5. [Mapa de pantallas](05-screen-map.md)
6. [Flujos operativos](06-operational-flows.md)
7. [Matriz de permisos](07-permission-matrix.md)
8. [Requisitos no funcionales](08-non-functional-requirements.md)
9. [Estrategia básica de pruebas](09-basic-test-strategy.md)
10. [Arquitectura técnica](10-technical-architecture.md)
11. [Dependencias y eventos](11-module-dependencies-and-events.md)
12. [Esquema físico de datos](12-physical-data-schema.md)
13. [Plan de implementación](13-implementation-plan.md)
14. [Configuración y datos iniciales](14-initial-configuration-and-data.md)
15. [Definición de terminado y lanzamiento](15-definition-of-done-and-release.md)
16. [Riesgos y decisiones aplazadas](16-risks-and-deferred-decisions.md)

## Principios de mantenimiento

- Toda decisión funcional nueva debe quedar documentada antes de implementarse.
- Si una regla cambia, se actualizan primero estos documentos y después el código.
- Las historias del backlog deben conservar criterios de aceptación verificables.
- Los documentos confirmados describen el comportamiento esperado, no necesariamente la estructura física final de las tablas.
