# ARMORA NextGen — Tablero de Desarrollo (Gantt)

> **Rol:** Planificador / Documentación Experta  
> **Basado en:** `AGENTS.md`, `_perfiles_tecnicos/senior-fullstack-erp-architect_v3.md`, `_auditoria/senior-code-architecture-quality-auditor.md`, `_docs_desarrollo/`  
> **Generado:** 2026-06-09 · **Rev 1:** 2026-06-09 (3 h/día confirmado)  
> **Próxima revisión:** Al finalizar PAUSA 1 (28 Jun 2026)

---

## 0. Parámetros del modelo

| Variable | Valor |
|---|---|
| Inicio del proyecto | Lun 01 Jun 2026 |
| Días laborables | Lun — Vie (5/7) |
| Horas efectivas/día | **3.0 h** (confirmado por equipo) |
| PAUSA 1 | 15 Jun — 28 Jun (10 días hábiles perdidos) |
| PAUSA 2 | 01 Oct — 08 Oct (6 días hábiles — **no impacta**, proyecto termina antes) |
| Velocidad observada (Hito 003 → Hito 007) | ~18–22h por hito completo (back + front + tests) |
| Capacidad semanal neta | **15 h/sem** |
| Capacidad mensual neta | **60 h/mes** |

---

## 1. Estado actual (Jun 09, 2026) — Línea base

```
HITO-001 ████████████████████████████████████ 100%  Fundación (BD + React + AGENTS)
HITO-002 ████████████████████████████████████ 100%  Customers + Products
HITO-003 ████████████████████████████████████ 100%  Sales + Inventory (back + front + portal)
HITO-004 ████████████████████████████████████ 100%  Purchases Ola A+B (backend + PurchaseFormPage)
HITO-004a████████████████████████████████████ 100%  Company Settings
HITO-007 ████████████████████████████████████ 100%  Personal (CRUD + reports + 23 tests)
AUDITORÍA████████████████████████████████████ 100%  Fase 0 + 11 fixes + 14 Ola B + 4 Personal
──────────────────────────────────────────────────────
INVERSIÓN TOTAL ESTIMADA: 110–130 h ⇢ ≈ 44–52 días hábiles (desde Jun 01)
```

---

## 2. Trabajo restante — Cuantificación por módulo

### 2.1 HITO-005 — Purchases Frontend (23 h)

| Item | Estimación | Depende de |
|---|---|---|
| PurchaseListPage — DataGrid real con filtros, paginación server-side, búsqueda | 6 h | Backend OK (existe) |
| PurchaseDetailPage — Vista detalle con cabecera + items + timeline de estados | 5 h | PurchaseListPage |
| SupplierListPage — DataGrid real (reemplazar placeholder) | 6 h | Backend OK (existe) |
| SupplierFormPage — Formulario RHF+Zod (reemplazar placeholder) | 6 h | SupplierListPage |

### 2.2 HITO-005 — Inventory Admin (16 h)

| Item | Estimación | Depende de |
|---|---|---|
| InventoryStockPage — DataGrid stock por almacén, filtros, búsqueda | 10 h | Backend OK (existe) |
| InventoryKardexPage — Kardex valorizado con selector de fechas/producto | 6 h | InventoryStockPage |

### 2.3 HITO-005 — Portal Proveedor (8 h)

| Item | Estimación | Depende de |
|---|---|---|
| Portal Proveedor — login, dashboard, órdenes de compra, historial | 8 h | Backend OK (existe) |

### 2.4 HITO-005 — Logistics (28 h)

| Item | Estimación | Depende de |
|---|---|---|
| **Backend**: Migraciones (rutas, zonas, transportistas, guías de remisión) | 4 h | — |
| **Backend**: Modelos (Route, Zone, Carrier, DispatchNote) + SoftDeletes + ULID | 3 h | Migraciones |
| **Backend**: Services (RouteService, DispatchService) + validaciones | 4 h | Modelos |
| **Backend**: Controllers + FormRequests + Resources + Policies (6 permisos) | 3 h | Services |
| **Backend**: Tests (6–8 casos) | 2 h | Controllers |
| **Frontend**: Logistics pages (rutas, zonas, transportistas, despacho) | 12 h | Backend endpoints |

### 2.5 HITO-005 — Finance Ola C (28 h)

| Item | Estimación | Depende de |
|---|---|---|
| **Backend**: Migraciones (finance_cuentas_contables, finance_asientos, asiento_lineas) | 4 h | — |
| **Backend**: Plan contable SUNAT básico (40 cuentas) seed | 2 h | Migraciones |
| **Backend**: Modelos + Services (generarAsientoPorVenta/Compra, validarCuadratura, exportarPLE 14.1/8.1) | 8 h | Migraciones |
| **Backend**: Controllers + FormRequests + Resources + Policies (4 permisos) + 5 rutas | 4 h | Services |
| **Backend**: Tests (~8 casos) | 2 h | Controllers |
| **Frontend**: Finance pages (asientos, libro diario, PLE export) | 8 h | Backend endpoints |

### 2.6 HITO-006 — Loyalty (20 h)

| Item | Estimación | Depende de |
|---|---|---|
| **Backend**: Migraciones (puntos, reglas, canjes, premios) | 3 h | — |
| **Backend**: Modelos + Services (calcularPuntos, canjear, expirar) | 5 h | Migraciones |
| **Backend**: Controllers + FormRequests + Resources + Policies (3 permisos) | 3 h | Services |
| **Backend**: Tests (~6 casos) | 1 h | Controllers |
| **Frontend**: Loyalty pages (reglas, canjes, historial) | 8 h | Backend endpoints |

### 2.7 HITO-006 — Cross-cutting (55 h)

| Item | Estimación | Depende de |
|---|---|---|
| Notifications (Laravel Mail + WebPush + eventos trigger) | 8 h | Loyalty, Sales |
| CI/CD (GitHub Actions: Pint, PHPUnit, npm build, npm lint) | 6 h | — |
| Sentry + Laravel Pulse (instalación + configuración + dashboard) | 5 h | — |
| Greenter/SUNAT (instalar, config, SunatService, SendInvoiceJob, tests) | 15 h | Sales, Purchases |
| PWA (vite-plugin-pwa, manifest, service worker, add-to-home-screen) | 4 h | Portal |
| Frontend tests (Vitest + Playwright setup, casos base) | 8 h | — |
| Estandarizar API response wrapper (unificar contrato data/wrapper) | 5 h | Todos los módulos |
| Docs + AGENTS.md v4 + perfil v4 + cierre documental | 4 h | Todo lo anterior |

---

## 3. Resumen de cargas

| Fase | Total horas | Días hábiles (3 h/día) | Semanas |
|---|---|---|---|
| Purchases Frontend | 23 h | 7.7 | 1.5 |
| Inventory Admin | 16 h | 5.3 | 1.1 |
| Portal Proveedor | 8 h | 2.7 | 0.5 |
| Logistics | 28 h | 9.3 | 1.9 |
| Finance Ola C | 28 h | 9.3 | 1.9 |
| Loyalty | 20 h | 6.7 | 1.3 |
| Cross-cutting | 55 h | 18.3 | 3.7 |
| **Subtotal** | **~178 h** | **~59.3** | **~11.9** |
| Buffer de holgura (15 %) | ~26 h | 8.7 | 1.7 |
| **TOTAL** | **~204 h** | **~68** | **~13.6** |

---

## 4. Gantt — Línea de tiempo (3 h/día)

### Leyenda

```
█████  Trabajo planificado
░░░░░  Buffer / holgura
⋯⋯⋯   PAUSA / sin actividad
>>>    Hito de revisión / auditoría
```

---

### 4.1 Junio 2026 — Semanas 1–4

```
SEMANA      L   M   M   J   V   S   D   | HORAS | FASE
──────────────────────────────────────────┼───────┼──────────────────────
Sem 01     █1  █2  █3  █4  █5  ·   ·   |  15   | (Histórico: Hitos 001-003)
Sem 02     █8  █9 █10 █11 █12 ·   ·    |  15   | (Histórico: Hitos 004-007)
           ⋯⋯⋯⋯⋯ PAUSA 1 ⋯⋯⋯⋯⋯⋯⋯⋯⋯⋯⋯⋯⋯⋯⋯⋯⋯⋯⋯⋯⋯⋯⋯⋯⋯⋯⋯⋯⋯⋯⋯
Sem 03     ⋯  ⋯  ⋯  ⋯  ⋯  ·   ·    |   0   | PAUSA (15-28 Jun)
Sem 04     ⋯  ⋯  ⋯  ⋯  ⋯  ·   ·    |   0   | PAUSA (15-28 Jun)
           ⋯⋯⋯⋯⋯ REANUDACIÓN ⋯⋯⋯⋯⋯⋯⋯⋯⋯⋯⋯⋯⋯⋯⋯⋯⋯⋯⋯⋯⋯⋯⋯⋯⋯⋯⋯⋯⋯⋯
```

**Pre-pausa: 10–12 Jun (3 días, 9 h)**
| Día | Actividad | h |
|---|---|---|
| Mié 10 | PurchaseListPage — DataGrid real con filtros, paginación, búsqueda | 6 |
| Jue 11 | PurchaseListPage finish + PurchaseDetailPage inicio | 3 |
| Vie 12 | PurchaseDetailPage — cabecera + items + timeline | 3 |

---

### 4.2 Julio 2026 — Semanas 5–9

```
SEMANA      L   M   M   J   V   S   D   | HORAS | HITO
──────────────────────────────────────────┼───────┼────────────────────
Sem 05  29█  30█  █1  █2  █3  ·   ·    |  15   | Purchases Frontend completo
Sem 06    █6  █7  █8  █9 █10 ·   ·    |  15   | Inventory Admin >>> AUD
Sem 07   █13 █14 █15 █16 █17 ·   ·    |  15   | Portal Proveedor + Logistics back
Sem 08   █20 █21 █22 █23 █24 ·   ·    |  15   | Logistics backend completo
Sem 09   █27 █28 █29 █30 █31 ·   ·    |  15   | Logistics frontend
```

**Julio — Detalle (20 días hábiles, 60 h)**

| Semana | Días | Actividad | h |
|---|---|---|---|
| **Sem 05** | Lun 29 — Vie 3 | PurchaseDetailPage finish (2h) + SupplierListPage (6h) + SupplierFormPage (6h) + buffer (1h) | 15 |
| ▶️ | Vie 3 | >>> **Auditoría HITO-005 Purchases Frontend** | — |
| **Sem 06** | Lun 6 — Vie 10 | InventoryStockPage (10h) + InventoryKardexPage (4h) + buffer (1h) | 15 |
| **Sem 07** | Lun 13 — Vie 17 | InventoryKardexPage finish (2h) + Portal Proveedor (8h) + Logistics back: migraciones+modelos (4h) | 14 |
| ▶️ | Vie 17 | >>> **Auditoría HITO-005 Inventory Admin** | — |
| **Sem 08** | Lun 20 — Vie 24 | Logistics back: models+services+controllers+tests (11h) + buffer (4h) | 15 |
| **Sem 09** | Lun 27 — Vie 31 | Logistics frontend: rutas+zonas+transportistas+despacho (12h) + buffer (3h) | 15 |

---

### 4.3 Agosto 2026 — Semanas 10–13

```
SEMANA      L   M   M   J   V   S   D   | HORAS | HITO
──────────────────────────────────────────┼───────┼────────────────────
Sem 10    █3  █4  █5  █6  █7  ·   ·    |  15   | Logistics frontend
Sem 11   █10 █11 █12 █13 █14 ·   ·    |  15   | Finance Ola C back
Sem 12   █17 █18 █19 █20 █21 ·   ·    |  15   | Finance Ola C back + front
Sem 13   █24 █25 █26 █27 █28 ·   ·    |  15   | Finance Ola C front >>> AUD
```

**Agosto — Detalle (21 días hábiles, 63 h)**

| Semana | Días | Actividad | h |
|---|---|---|---|
| **Sem 10** | Lun 3 — Vie 7 | Logistics frontend finish (12h done prior) + ajustes auditoría + buffer | 15 |
| ▶️ | Vie 7 | >>> **Auditoría HITO-005 Logistics** | — |
| **Sem 11** | Lun 10 — Vie 14 | Finance Ola C: migraciones (4h) + modelos (3h) + services (8h) | 15 |
| **Sem 12** | Lun 17 — Vie 21 | Finance Ola C: controllers+tests (6h) + frontend inicio (8h) | 14 |
| **Sem 13** | Lun 24 — Vie 28 | Finance Ola C frontend: asientos, libro diario, PLE export (10h) + buffer (5h) | 15 |
| ▶️ | Vie 28 | >>> **Auditoría HITO-005 Finance** | — |

---

### 4.4 Septiembre 2026 — Semanas 14–17

```
SEMANA      L   M   M   J   V   S   D   | HORAS | HITO
──────────────────────────────────────────┼───────┼────────────────────
Sem 14    █7  █8  █9 █10 █11 ·   ·    |  15   | Loyalty back + front
Sem 15   █14 █15 █16 █17 █18 ·   ·    |  15   | Loyalty frontend + Cross-cutting inicio
Sem 16   █21 █22 █23 █24 █25 ·   ·    |  15   | Cross-cutting (Notif + CI/CD + Sentry)
Sem 17   █28 █29 █30 ·  ·   ·   ·    |   9   | Greenter/SUNAT inicio
             (PAUSA 2: 01-08 Oct — NO IMPACTA)
```

**Septiembre — Detalle (18 días hábiles activos, 54 h)**

| Semana | Días | Actividad | h |
|---|---|---|---|
| **Sem 14** | Lun 7 — Vie 11 | Loyalty: migraciones (3h) + modelos+services (8h) + controllers+tests (4h) | 15 |
| **Sem 15** | Lun 14 — Vie 18 | Loyalty: frontend reglas+canjes+historial (8h) >>> **AUD** + Cross-cutting: Notifications (8h) | 16 |
| **Sem 16** | Lun 21 — Vie 25 | Cross-cutting: CI/CD (6h) + Sentry+Pulse (5h) + PWA (4h) | 15 |
| **Sem 17** | Lun 28 — Mié 30 | Greenter/SUNAT: instalación+config+SunatService inicio (9h) | 9 |
| ▶️ | Mié 30 | >>> **Auditoría HITO-006 Loyalty** | — |

> **Nota:** Sep 30 es el último día antes de PAUSA 2. Con 3 h/día, el proyecto completo (incluyendo buffer del 15 %) se estima terminado para esta fecha. Si hay retrasos, se usa la semana post-pausa (Oct 9+).

---

### 4.5 Octubre 2026 — Post-pausa (solo si aplica)

```
SEMANA      L   M   M   J   V   S   D   | HORAS | HITO
──────────────────────────────────────────┼───────┼────────────────────
           ⋯⋯⋯⋯⋯ PAUSA 2 ⋯⋯⋯⋯⋯⋯⋯⋯⋯⋯⋯⋯⋯⋯⋯⋯⋯⋯⋯⋯⋯⋯⋯⋯⋯⋯⋯⋯⋯⋯⋯
Sem 18     ⋯  ⋯  ⋯  ⋯  ⋯  ·   ·    |   0   | PAUSA (01-08 Oct)
           ⋯⋯⋯⋯⋯ REANUDACIÓN ⋯⋯⋯⋯⋯⋯⋯⋯⋯⋯⋯⋯⋯⋯⋯⋯⋯⋯⋯⋯⋯⋯⋯⋯⋯⋯⋯⋯⋯⋯
Sem 19    █9  █12 █13 █14 █15 ·   ·    |  15   | Greenter/SUNAT (cont.) + FE Tests
Sem 20   █19 █20 █21 █22 █23 ·   ·    |  15   | FE Tests + API Wrapper + Docs cierre
```

**Octubre — Detalle (solo si hay desviaciones, 12 días hábiles, 36 h)**

| Días | Actividad | h |
|---|---|---|
| Vie 9 | Greenter: SunatService + SendInvoiceJob (6h) | 3 |
| Lun 12 — Mié 14 | Greenter: tests + ajustes (6h) + FE tests Vitest inicio (4h) | 9 |
| Jue 15 — Vie 16 | FE tests Playwright (4h) + buffer | 6 |
| Lun 19 — Mié 21 | FE tests finish + API wrapper (5h) | 9 |
| Jue 22 — Vie 23 | Docs cierre + AGENTS.md v4 + perfil v4 (4h) | 6 |
| ▶️ | Vie 23 | >>> **Auditoría final + CIERRE DE PROYECTO** | — |

---

## 5. Ruta crítica y dependencias

```
PurchaseListPage ──→ PurchaseDetailPage
      │
      ├──→ SupplierListPage ──→ SupplierFormPage
      │
      ├──→ InventoryStockPage ──→ InventoryKardexPage
      │
Inventory OK ──→ Portal Proveedor
      │
Logistics ──→ Finance ──→ Loyalty
      │
Todo lo anterior ──→ Cross-cutting (Greenter/SUNAT, CI/CD, Monitoreo, PWA)
      │
                                ──→ Cierre documental
```

---

## 6. Hitos de auditoría calendarizados

| Hito | Fecha estimada | Gates que aplican |
|---|---|---|
| >>> HITO-005 Purchases Frontend | Vie 03 Jul 2026 | G-FE, G-API, G-TEST |
| >>> HITO-005 Inventory Admin | Vie 17 Jul 2026 | G-ARQ, G-RBAC, G-PERF |
| >>> HITO-005 Logistics | Vie 07 Ago 2026 | G-ARQ, G-MIG, G-TEST |
| >>> HITO-005 Finance | Vie 28 Ago 2026 | G-SUNAT, G-FORM, G-TX |
| >>> HITO-006 Loyalty | Mié 16 Sep 2026 | G-RBAC, G-EVT, G-TEST |
| >>> HITO-006 Cross-cutting | Mié 30 Sep 2026 | G-DEVOPS, G-OWASP, G-LOGS |
| >>> **CIERRE PROYECTO** | **Mié 30 Sep 2026** (est.) / **Vie 23 Oct 2026** (contingencia) | G-DOC, todos los gates |

---

## 7. Análisis de riesgos al plan

| Riesgo | Probabilidad | Impacto | Mitigación |
|---|---|---|---|
| Finance Ola C se cancela (⏸ pendiente validación) | Media | -28 h, adelanta 2 sem | Mover buffer a Loyalty o Greenter |
| Greenter/SUNAT más complejo de lo estimado | Alta | +5 a +10 h | Mitigado con buffer del 15 % |
| Logistics requiere más integración con Purchases | Media | +5 h | Ajustar buffer post-auditoría |
| Pausa 2 se extiende | Baja | +1 sem | Buffer absorbe |
| Se descubre deuda técnica no documentada | Media | +5 a +10 h por hito | Auditorías calendarizadas capturan temprano |
| Frontend tests (Playwright) flaky en CI | Alta | +5 h | Usar Docker containers para E2E |

---

## 8. Fechas clave

| Evento | Fecha | Confianza |
|---|---|---|
| ✅ Inicio proyecto | Lun 01 Jun 2026 | — |
| ✅ Estado actual | Mar 09 Jun 2026 | — |
| ➡️ Inicio fase planificada | Mié 10 Jun 2026 | — |
| 🔴 INICIO PAUSA 1 | Lun 15 Jun 2026 | — |
| 🔴 FIN PAUSA 1 | Dom 28 Jun 2026 | — |
| ➡️ Reanudación | Lun 29 Jun 2026 | — |
| 🟢 HITO-005 Purchases Frontend completado | Jue 02 Jul 2026 | Alta |
| 🟢 HITO-005 Inventory Admin completado | Jue 16 Jul 2026 | Alta |
| 🟢 Portal Proveedor completado | Jue 23 Jul 2026 | Alta |
| 🟢 HITO-005 Logistics completado | Vie 07 Ago 2026 | Media |
| 🟢 HITO-005 Finance Ola C completado | Vie 28 Ago 2026 | Media |
| 🟢 HITO-006 Loyalty completado | Mié 16 Sep 2026 | Media |
| 🟢 **CIERRE PROYECTO ESTIMADO** | **Mié 30 Sep 2026** | **Alta** (con 3 h/día + buffer) |
| 🟡 Cierre contingencia (si hay desviaciones) | Vie 23 Oct 2026 | Baja |

> **Nota:** Con 3 h/día, el proyecto completa las **178 h estimadas de trabajo** en ~60 días hábiles. El buffer del 15 % (~26 h, ~9 días) protege contra desviaciones. Esto ubica el cierre entre el 11 Sep (sin buffer) y el 30 Sep (con buffer). La PAUSA 2 (Oct 01-08) **no impacta** la fecha base.

---

## 9. Resumen visual del cronograma (3 h/día)

```
JUN 2026
  Sem 01-02 ██████████████████████████████████  (Histórico: Hitos 001-007)
  Sem 03-04 ⋯⋯⋯⋯⋯⋯⋯ PAUSA 1 ⋯⋯⋯⋯⋯⋯⋯⋯⋯
  
JUL 2026
  Sem 05 ████ Purchases Frontend (List + Detail + Suppliers)  >>> AUD 03 Jul
  Sem 06 ████ Inventory Admin (StockPage + KardexPage)
  Sem 07 ████ Portal Proveedor + Logistics Back (migraciones)
  Sem 08 ████ Logistics Back (services + controllers + tests)
  Sem 09 ████ Logistics Frontend (rutas + zonas + despacho)

AGO 2026
  Sem 10 ████ Logistics Frontend >>> AUD 07 Ago
  Sem 11 ████ Finance Ola C Backend (migraciones + servicios)
  Sem 12 ████ Finance Ola C Backend (controllers) + Frontend inicio
  Sem 13 ████ Finance Frontend (asientos + PLE)  >>> AUD 28 Ago

SEP 2026  
  Sem 14 ████ Loyalty Backend + Frontend
  Sem 15 ████ Loyalty Frontend >>> AUD + Notifications
  Sem 16 ████ CI/CD + Sentry + Pulse + PWA
  Sem 17 ████ Greenter/SUNAT (instalación + servicios)
  🟢 CIERRE ESTIMADO: Mié 30 Sep 2026 🟢
  
OCT 2026 (solo contingencia)
  Sem 18 ⋯⋯ PAUSA 2 ⋯⋯
  Sem 19-20 ████ Greenter tests + FE Tests + API Wrapper + Docs
```

---

## 10. Decisiones registradas en esta revisión

| Punto | Decisión |
|---|---|
| 1. Finance Ola C | ✅ Incluida en el plan (ADR-010, pendiente de validación) |
| 2. Greenter/SUNAT | ✅ En scope del MVP |
| 3. Portal Proveedor | ✅ En HITO-005 |
| 4. Logistics Leaflet/Google Maps | Pendiente de definir dependencia externa |
| 5. Capacidad real | ✅ **3 h/día** confirmado |

---

*Documento generado: 2026-06-09 · Próxima revisión: Al finalizar PAUSA 1 (28 Jun 2026)*
