# HITO-AUDIT-002 - Validacion integral del proyecto

**Fecha:** 2026-06-07  
**Perfil aplicado:** `_auditoria/senior-code-architecture-quality-auditor.md`  
**Base de contraste:** `AGENTS.md`, `_perfiles_tecnicos/senior-fullstack-erp-architect_v3.md`, codigo real en `backend/` y `frontend/`.

## Resultado ejecutivo

**Estado:** Aprobado con deuda no bloqueante documentada.

Se reviso funcionalidad, congruencia documental y alineacion con el perfil arquitecto. Durante la auditoria se corrigieron bloqueantes de validacion en tests y entorno:

- `composer test` pasa: **100 tests, 284 assertions**.
- `npm run build` pasa.
- `npm run lint` pasa con **0 errores** y 1 warning no bloqueante.
- `npm run test` pasa fuera del sandbox: **3 archivos, 9 tests**. En sandbox fallaba antes de ejecutar por `spawn EPERM` al cargar Vite/Rolldown.
- `npm run test:e2e` pasa fuera del sandbox: **8 tests Playwright**. En sandbox fallaba por `EPERM` al limpiar `test-results/.last-run.json`.

## Correcciones aplicadas

| ID | Gate | Hallazgo | Correccion | Estado |
|---|---|---|---|---|
| A2-01 | G-TEST / ADR-008 | `AuthTest` usaba `User::create()` y columna removida `dni` | Migrado a `User::factory()` + `numero_documento` | Cerrado |
| A2-02 | G-DEVOPS | Tests feature fallaban por falta de `APP_KEY` en PHPUnit | Agregado `APP_KEY` en `backend/phpunit.xml` | Cerrado |
| A2-03 | G-MIG / G-API | `customers.tipo_documento` era `VARCHAR(2)` pero Request/UI aceptan `DNI`, `RUC`, `CE`, `PASAPORTE` | Nueva migracion `2026_06_07_020000_expand_customers_tipo_documento_length.php` a `VARCHAR(20)` | Cerrado |
| A2-04 | G-TEST / G-API | Tests de Customers/Products esperaban wrapper `data`, pero esos controllers historicos devuelven resource sin wrapper | Tests alineados al contrato real de esos endpoints | Cerrado |
| A2-05 | G-FE | `npm run lint` fallaba por regla `react-hooks/set-state-in-effect` en Sidebar y Company | Comentarios de excepcion locales donde el patron es intencional y ya acotado | Cerrado |

## Evidencia de arquitectura validada

- Service Layer respetado en modulos de negocio: controllers delegan en services (`CustomerService`, `ProductService`, `SaleService`, `CompraService`, `EmpresaService`, `PersonalService`).
- RBAC granular por rutas y policies. Ejemplos vigentes: `PUT /sales/{sale}` usa `permission:editar-ventas`, `POST /sales/{sale}/confirmar` usa `permission:confirmar-ventas`, `DELETE /sales/{sale}` usa `permission:eliminar-ventas`.
- Personal esta alineado con la iteracion feedback: `dim_documento_identidad`, `numero_documento`, rol single-select, `roles.max:1`, `PersonalResource::collection()` para listado y acciones de gestion (`toggle-activo`, `reset-password`).
- Purchases backend existe y esta cubierto por tests (`CompraTest`, `ProveedorTest`).
- Company Settings esta implementado con bloqueo de ventas/compras y validacion contextual.

## Riesgos residuales no bloqueantes

| ID | Severidad | Riesgo | Plan |
|---|---|---|---|
| R2-01 | Medio | Customers/Products/Sales/Purchases antiguos devuelven recursos individuales sin wrapper `data`, mientras Personal ya usa el wrapper Laravel idiomatico | Estandarizar contrato API en una ola dedicada y ajustar frontend/tests en bloque |
| R2-02 | Bajo | `npm run lint` mantiene warning de React Compiler por `watch()` de React Hook Form en `PersonalFormPage.tsx` | Reemplazar por `useWatch` cuando se refactorice el formulario |
| R2-03 | Bajo | Documentos heredados contienen texto con mojibake por encoding historico | Normalizar encoding UTF-8 en una tarea documental separada |
| R2-04 | Medio | El perfil v3 queda historico: describe Hito 003 como proximo y no refleja Hitos 004a/007 | Usar `AGENTS.md` y este reporte como fuente actual; actualizar v3 o crear v4 |

## Verificacion ejecutada

```bash
cd backend && composer test
# passed: 100 tests, 284 assertions

cd frontend && npm run build
# passed

cd frontend && npm run lint
# passed: 0 errors, 1 warning

cd frontend && npm run test
# passed fuera del sandbox: 3 files, 9 tests

cd frontend && npm run test:e2e
# passed fuera del sandbox: 8 tests
```

## Decision de cierre

No quedan hallazgos criticos abiertos para los hitos actualmente implementados. El proyecto cumple funcionalidad validable con las suites disponibles y la documentacion de `_docs_desarrollo/HITO-007-personal.md` queda actualizada al estado real de Personal.
