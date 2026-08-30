# DonPaty

Sistema de administración integral para la panadería y el hogar, construido como monolito modular con Laravel, Inertia, Vue 3 y TypeScript.

## Estado

La implementación se ejecuta por entregas. El avance autoritativo está en [docs/implementation/CHECKLIST.md](docs/implementation/CHECKLIST.md) y la planeación completa en [docs/planning/README.md](docs/planning/README.md).

## Stack base

- PHP 8.4.
- Laravel 13.
- Inertia 3.
- Vue 3 con TypeScript.
- Tailwind CSS 4.
- Node.js 24.5.0.
- SQLite para desarrollo y MySQL para producción.

## Instalación local

1. Copiar `.env.example` como `.env`.
2. Crear `database/database.sqlite`.
3. Ejecutar `composer install`.
4. Ejecutar `php artisan key:generate`.
5. Ejecutar `php artisan migrate`.
6. Ejecutar `npm install`.
7. Ejecutar `npm run build` o `composer run dev`.

Las rutas de PHP y Node pueden estar administradas por Herd en Windows.

## Arquitectura

Los módulos viven bajo `app/Modules`. Cada módulo agrega únicamente las capas que necesita:

```text
Domain/
Application/
Infrastructure/
Presentation/
```

Los flujos críticos se implementan mediante casos de uso transaccionales. Los controladores, páginas Vue y modelos de persistencia no contienen cálculos de negocio.
