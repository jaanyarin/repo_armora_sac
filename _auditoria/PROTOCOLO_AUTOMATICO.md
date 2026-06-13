# Protocolo Automático Arquitecto ↔ Auditor

**Versión:** 1.0  
**Objetivo:** Que el agente Arquitecto y el agente Auditor trabajen como 2 agentes independientes pero coordinados, asegurando que cada entrega pase los gates de calidad antes de considerarse completa.

---

## Arquitectura de la interacción

```
┌──────────────────────────────────────────────────────────────────┐
│                    ARQUITECTO (implementa)                        │
│  Perfil: _perfiles_tecnicos/senior-fullstack-erp-architect_v3.md │
│  Labor:   escribir código, crear migraciones, componentes, etc.  │
└──────────┬───────────────────────────────────────────┬───────────┘
           │ 1. Termina implementación                  │
           ▼                                            │
┌──────────────────────┐                                │
│ Layer 1: Mecánico    │ ◄── .\_auditoria\auditar.ps1   │
│ build + lint + test  │     (PS script, 100% automático)│
└──────────┬───────────┘                                │
           │ 2. ¿Pasa? → sí/no                          │
           ▼                                            │
┌──────────────────────────────────┐                     │
│ Layer 2: Gate Review (IA)        │ ◄── Task(ag auditor)│
│ Auditor inspecciona código       │     evalúa G-ARQ,   │
│ contra los 17 gates del checklist│     G-RBAC, G-FE... │
└──────────┬───────────────────────┘                     │
           │ 3. Hallazgos (🔴🟠🟡🟢)                      │
           ▼                                            │
┌──────────────────────────────────┐                     │
│ ¿Hallazgos Críticos/Altos?       │                     │
│   → sí: arquitecto remedia       │── bucle hasta ──────┘
│   → no: ✅ ENTREGA COMPLETA      │
└──────────────────────────────────┘
```

---

## Layer 1 — Chequeos Mecánicos (Script)

Ejecutado por el arquitecto **inmediatamente después de terminar la implementación**.

| Paso | Comando | Gate que valida |
|------|---------|----------------|
| 1 | `.\_auditoria\auditar.ps1 -Quick` | G-DEVOPS (build + lint) |
| 2 | `.\_auditoria\auditar.ps1` (completo) | G-TEST (tests backend + frontend) |

El script produce `_auditoria/auditar-resultado.json` con el detalle.

**Si falla:** el arquitecto debe corregir antes de invocar al auditor.  
**Si pasa:** el arquitecto invoca al auditor (Layer 2).

---

## Layer 2 — Gate Review (Agente Auditor)

El arquitecto **invoca al agente auditor** mediante el mecanismo de subagente (Task tool) con la siguiente plantilla:

### Comando de invocación

```
Task(
  subagent_type: "general",
  description: "auditar-entrega-{descripción}",
  prompt: @"
Actúa como el **Senior Code & Architecture Quality Auditor** definido en
`_auditoria/senior-code-architecture-quality-auditor.md`.

Tu misión es auditar la implementación más reciente contra los gates
aplicables del CHECKLIST MAESTRO (`_auditoria/CHECKLIST_MAESTRO.md`).

## Contexto de la entrega
- **Archivos tocados:** [lista de archivos modificados]
- **Propósito:** [breve descripción de qué se implementó]
- **Resultado Layer 1:** ver `_auditoria/auditar-resultado.json`

## Instrucciones
1. Lee los archivos modificados y evalúa cada gate aplicable:
   G-ARQ, G-RBAC, G-FORM, G-EVT, G-TX, G-TEST, G-DOC, G-API,
   G-SUNAT, G-OWASP, G-LOGS, G-FE, G-TS, G-PERF, G-MIG, G-DEVOPS.
2. Marca cada gate como ✅ / ⚠️ / ❌ / ➖ con evidencia concreta.
3. Reporta hallazgos con severidad (🔴 Crítico, 🟠 Alto, 🟡 Medio, 🟢 Bajo).
4. Incluye un veredicto final: 🟢 PASA / 🟡 PASA CON OBS / 🔴 RECHAZA.

Devuelve el reporte completo en formato markdown.
"@
)
```

### Lo que el auditor devuelve

El auditor produce un documento con:
- **Resumen ejecutivo** (semáforo + KPIs)
- **Gate por gate** (cada uno con check ✅/⚠️/❌/➖, evidencia, línea de código)
- **Hallazgos** clasificados por severidad
- **Veredicto** (PASA / PASA CON OBS / RECHAZA)

---

## Ciclo de remediación

```
┌────────────┐     ┌──────────────┐     ┌──────────────┐
│ Arquitecto │────▶│ Layer 1      │────▶│ ¿Pasa?       │
│ implementa │     │ (script)     │     │  → sí        │
└────────────┘     └──────────────┘     └──────┬───────┘
                                               │
                                        ┌──────▼───────┐
                                        │ Layer 2      │
                                        │ (auditor IA) │
                                        └──────┬───────┘
                                               │
                                        ┌──────▼───────┐
                                        │ ¿Críticos?   │
                                        │  → sí:       │─── bucle ──→ Arquitecto remedia
                                        │  → no: ✅    │
                                        └──────────────┘
```

- Si hay hallazgos **🔴 Críticos**: el arquitecto los corrige **inmediatamente** y reinicia desde Layer 1.
- Si hay hallazgos **🟠 Altos**: el arquitecto los corrige antes de pasar al siguiente hito.
- Si hay hallazgos **🟡 Medios / 🟢 Bajos**: se documentan en `MATRIZ_RIESGOS.md` y se difieren.

---

## Artefactos generados

| Archivo | Contenido |
|---------|-----------|
| `_auditoria/auditar-resultado.json` | Resultado del Layer 1 (build/lint/test) |
| `_auditoria/HITO-{NOMBRE}/auditoria.md` | Reporte completo del Layer 2 (gate review) |
| `_auditoria/HITO-{NOMBRE}/hallazgos.json` | Hallazgos estructurados |
| `_auditoria/MATRIZ_RIESGOS.md` | Deuda diferida acumulada |

---

## Ejemplo de uso (flujo completo)

```powershell
# 1. Arquitecto implementa
# (edita archivos, crea componentes, etc.)

# 2. Layer 1 — chequeos mecánicos
.\_auditoria\auditar.ps1

# 3. Si pasa → invocar al auditor (ver comando en sección Layer 2)
# 4. Si el auditor encuentra críticos → remediar y volver a paso 2
# 5. Sin críticos → ✅ ENTREGA COMPLETA
```

---

*Protocolo Automático Arquitecto ↔ Auditor v1.0 — 2026-06-12*
