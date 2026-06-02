# Checklist de Validación para Stakeholders

**Documento:** Validaciones que deben completarse ANTES de firmar y proceder con la implementación  
**Audiencia:** Project Manager, CTO, Representante del Negocio  
**Fecha:** Junio 2026  
**Plazo:** ESTA SEMANA  

---

## 🎯 Validación Pre-Implementación

Este checklist debe ser completado por stakeholders antes de autorizar el inicio de la Fase 1 (Setup).

### ✅ SECCIÓN 1: REVISIÓN DE DOCUMENTACIÓN

```
[ ] He leído RESUMEN_EJECUTIVO.md (1 página, 5 minutos)
[ ] Entiendo la estrategia de migración (Strangler Fig, 4-6 meses)
[ ] Entiendo el ROI (12-18 meses para recuperar inversión)
[ ] Entiendo los riesgos principales (data migration, SUNAT, timeline)
[ ] He identificado responsables para responder 8 validaciones pendientes
```

---

## 🔒 SECCIÓN 2: VALIDACIONES TÉCNICAS CRÍTICAS

### V1: Base de Datos

**Estado Actual:** PostgreSQL (inferido 85%)

```
[ ] Confirmar: ¿Es PostgreSQL? (12, 13, 14, 15 o 16?)
    Respuesta: _______________________
    Responsable: _______________________
    Fecha: _______________________

[ ] Si NO es PostgreSQL, confirmar cuál es:
    Respuesta: MySQL / Oracle / Otro: _______________________

[ ] Proporcionar: Dump anónimizado de schema actual
    Entregado por: _______________________
    Fecha: _______________________

[ ] Confirmar: Acceso a herramienta de admin BD para DBA
    Responsable: _______________________
    Credenciales: [Enviar por secure channel]
```

---

### V2: Integración SUNAT - Facturación Electrónica

**Estado Actual:** Greenter vs LibPHP SUNAT (desconocido)

```
[ ] Confirmar librería actual:
    [ ] Greenter 5.x
    [ ] LibPHP SUNAT
    [ ] Custom (homegrown)
    [ ] Otra: _______________________

[ ] Confirmar ambiente SUNAT:
    [ ] SolTest (prueba) - funcional ✓
    [ ] Producción - funcional ✓
    Credenciales SolTest: [Secure channel]

[ ] Confirmar certificados digitales:
    [ ] Ubicación actual: _______________________
    [ ] Contraseña del PFX/PKCS12: [Secure channel]
    [ ] Expiración: _______________________
    [ ] Autoridad que emitió: _______________________

[ ] Confirmar: ¿Cuántas empresas necesitan certificados?
    Respuesta: _______________________

[ ] Confirmar: ¿Gestión de certificados automática o manual?
    Respuesta: _______________________
```

**Recomendación:** Migrar a Greenter 5.x (moderno, PHP, mantenido)

---

### V3: Autenticación y Acceso

**Estado Actual:** Usuario/contraseña + JSESSIONID (session-based)

```
[ ] ¿Se requiere OAuth2/SSO en futuro?
    [ ] No, solo usuario/contraseña
    [ ] Sí, Google Login
    [ ] Sí, Microsoft 365
    [ ] Sí, Azure AD / Okta
    [ ] Sí, LDAP corporativo
    Responsable: _______________________

[ ] ¿Se requiere MFA (2FA)?
    [ ] No, no es necesario
    [ ] Sí, TOTP (Google Authenticator)
    [ ] Sí, SMS
    [ ] Sí, Email
    Responsable: _______________________

[ ] ¿Se requiere login con certificado digital SUNAT?
    [ ] No
    [ ] Sí (RUC + Certificado)
    Responsable: _______________________
```

---

### V4: Proveedores de Pago

**Estado Actual:** No detectado (probablemente manual: efectivo/transferencia)

```
[ ] ¿Se requiere integración de pagos en futuro (próximos 12 meses)?
    [ ] No, mantener manual
    [ ] Sí, Stripe
    [ ] Sí, MercadoPago
    [ ] Sí, Izipay
    [ ] Sí, Otro: _______________________

[ ] Si la respuesta es SÍ, confirmar:
    [ ] Responsable de integración: _______________________
    [ ] Timeline deseado: _______________________
    [ ] Cuenta sandbox lista: [ ] Sí [ ] No [ ] TBD
```

---

### V5: Multi-Empresa (Tenancy)

**Estado Actual:** Aparentemente una sola empresa por instalación

```
[ ] ¿Se requiere soporte MULTI-EMPRESA en mismo sistema?
    [ ] No, una sola empresa por siempre
    [ ] Sí, AHORA (2-3 empresas)
    [ ] Sí, en FUTURO (6-12 meses)
    [ ] Sí, siempre (modelo SaaS)
    Responsable: _______________________

Si respuesta es SÍ:

[ ] ¿Cuántas empresas inicialmente?
    Respuesta: _______________________

[ ] ¿Datos compartidos o aislados?
    [ ] Productos compartidos (una lista precios para todas)
    [ ] Productos aislados (cada empresa su catálogo)
    [ ] Clientes compartidos o aislados
    Especificar: _______________________

[ ] ¿Un usuario puede acceder múltiples empresas?
    [ ] Sí
    [ ] No
    Especificar: _______________________

[ ] ¿Se requieren reportes consolidados entre empresas?
    [ ] Sí
    [ ] No
```

**Recomendación:** Diseñar multi-tenancy desde inicio (shared database, empresa_id field)

---

### V6: Detracciones (Impuesto Temporal de Liquidez)

**Estado Actual:** Probablemente implementado (no confirmado)

```
[ ] ¿Sistema actual calcula detracciones?
    [ ] Sí
    [ ] No
    [ ] No sé
    Responsable: _______________________

[ ] ¿Cuáles operaciones llevan detracción?
    [ ] Ventas a jurídicas solamente
    [ ] Ventas a personas naturales también
    [ ] Según sector específico
    Especificar: _______________________

[ ] ¿Tasa de detracción fija o variable?
    [ ] Fija: _____ %
    [ ] Variable según tipo operación
    Detalles: _______________________

[ ] ¿Se requiere integración con BVD (Banco de la Nación)?
    [ ] Sí
    [ ] No
```

---

### V7: Retenciones (Impuesto a la Renta)

**Estado Actual:** Probablemente implementado (no confirmado)

```
[ ] ¿Sistema actual calcula retenciones a proveedores?
    [ ] Sí
    [ ] No
    [ ] No sé
    Responsable: _______________________

[ ] ¿Cuáles servicios/tipos llevan retención?
    [ ] Servicios: 5%
    [ ] Arrendamiento: 5%
    [ ] Otros (especificar): _______________________

[ ] ¿Cálculo automático o manual?
    [ ] Automático (sistema calcula)
    [ ] Manual (usuario especifica)
    [ ] Ambos (según caso)

[ ] ¿Se requiere reporte de retenciones a SII/SUNAT?
    [ ] Sí (PLE incluir retenciones)
    [ ] No
```

---

### V8: Ciclo Financiero y Cierre de Período

**Estado Actual:** Año calendario (inferido, no confirmado)

```
[ ] ¿Ciclo fiscal del negocio?
    [ ] Año calendario (Ene-Dic)
    [ ] Año fiscal personalizado: _______________________
    Responsable: _______________________

[ ] ¿Permite ediciones después de cierre?
    [ ] No, cierre es final
    [ ] Sí, con aprobación
    [ ] Sí, período de corrección de ___ meses
    Especificar: _______________________

[ ] ¿Se requieren reportes comparativos multi-período?
    [ ] Sí (período actual vs anterior)
    [ ] No
```

---

## 💼 SECCIÓN 3: VALIDACIONES DE NEGOCIO

### Confirmación de Funcionalidades

```
[ ] Programa de Canje/Puntos:
    [ ] Vigencia de puntos: 365 días
    [ ] Vigencia de puntos: perpetuos
    [ ] Vigencia de puntos: otra: _______________________
    [ ] Tasa: 1 sol = X puntos: _______________________
    Responsable: _______________________

[ ] Tipos de nota de crédito:
    [ ] Error (descuento, devolución)
    [ ] Total (anulación completa)
    [ ] Parcial (devolución parcial)
    Todos confirmados: [ ] Sí

[ ] Estructura de rutas/zonas:
    [ ] Necesaria para distribución
    [ ] Numero actual de zonas: _______________________
    [ ] Vendedores por zona: _______________________
```

---

## 🏗️ SECCIÓN 4: ARQUITECTURA Y DECISIONES

```
[ ] He revisado architecture-decisions.md (ADRs)
[ ] Entiendo las decisiones tomadas (PostgreSQL, Laravel, React, etc.)
[ ] Estoy de acuerdo con las decisiones arquitectónicas
[ ] Tengo preguntas sobre decisiones (especificar abajo):

    Preguntas/Dudas:
    1. _______________________________
    2. _______________________________
    3. _______________________________
```

---

## 📅 SECCIÓN 5: TIMELINE Y RECURSOS

```
[ ] Timeline propuesto (4-6 meses) es aceptable
    [ ] Sí
    [ ] No, necesito más rápido (especificar: _______)
    [ ] No, puedo esperar más tiempo

[ ] Presupuesto estimado ($52K-$79K) es aceptable
    [ ] Sí
    [ ] No, tengo presupuesto diferente: $_______

[ ] Equipo disponible para proyecto (4-5 personas):
    [ ] Sí, equipo confirmado
    [ ] No, necesito buscar recursos
    [ ] Parcial, confirmar roles específicos

[ ] Representante del negocio disponible (full-time):
    [ ] Sí, asignado: _______________________
    [ ] No, requiere arranjos
```

---

## 🚀 SECCIÓN 6: RIESGOS Y MITIGATION

```
[ ] Tengo claros los riesgos principales:
    [ ] Sí (ver RESUMEN_EJECUTIVO.md, sección Riesgos)
    [ ] No, necesito más detalles

[ ] Risk principal más crítico para el negocio:
    Respuesta: _______________________
    Plan de mitigación: _______________________

[ ] Estoy cómodo con estrategia Strangler Fig (migración gradual sin downtime)
    [ ] Sí
    [ ] No (explicar): _______________________
```

---

## ✅ SECCIÓN 7: FIRMAS Y APROBACIÓN

```
ANALISTA/ARQUITECTO RESPONSABLE:
Nombre: _______________________
Firma: _________________________ Fecha: __________

PROJECT MANAGER:
Nombre: _______________________
Firma: _________________________ Fecha: __________

CTO/TECHNICAL LEADER:
Nombre: _______________________
Firma: _________________________ Fecha: __________

BUSINESS REPRESENTATIVE:
Nombre: _______________________
Firma: _________________________ Fecha: __________

FINANCIAL/BUDGET APPROVAL:
Nombre: _______________________
Firma: _________________________ Fecha: __________
```

---

## 🔴 BLOQUEOS Y CONTINGENCIAS

```
Si ALGUNA validación no puede ser respondida o resulta en bloqueador:

1. Validación No Respondida: ___________________________
   Responsable asignado: ___________________________
   Fecha límite de respuesta: ___________________________
   Plan B si no responde: ___________________________

2. Validación No Respondida: ___________________________
   Responsable asignado: ___________________________
   Fecha límite de respuesta: ___________________________
   Plan B si no responde: ___________________________

NOTA: Todas las validaciones deben estar respondidas antes de 
      autorizar el inicio de Fase 1.
```

---

## 📋 CHECKLIST FINAL PREVIO A INICIO

```
[ ] Todas las 8 validaciones técnicas completadas y respondidas
[ ] Todas las validaciones de negocio completadas
[ ] Documentación revisada por stakeholders principales
[ ] Arquitectura aprobada por CTO
[ ] Presupuesto y timeline aprobados
[ ] Equipo asignado y disponible
[ ] Representante del negocio confirmado
[ ] Riesgos identificados y planes de mitigación en lugar
[ ] Todas las firmas obtenidas en sección anterior
[ ] No hay bloqueos pendientes

→ SI TODOS LOS CHECKBOXES ESTÁN MARCADOS:
  PROCEDER A KICK-OFF MEETING Y FASE 1
```

---

## 📞 Próximos Pasos

1. **Esta semana (por PM):**
   - Distribuir este checklist a todos los stakeholders
   - Agendar reunión de validación

2. **Próxima semana (por Team):**
   - Completar todas las respuestas
   - Resolver bloqueos
   - Obtener todas las firmas

3. **Antes del kick-off (por Arquitecto):**
   - Validar respuestas
   - Ajustar documentación según nueva información
   - Preparar kick-off presentation

---

## 📧 Distribución

```
Para: CTO, PM, Stakeholders principales
CC: Tech Lead, Representante Negocio
Asunto: [URGENT] Validación Pre-Implementación ARMORA Modernización
Fecha Plazo: FIN DE ESTA SEMANA
```

---

**Documento preparado por:** Solutions Architect  
**Fecha:** Junio 2026  
**Estado:** 🔴 REQUIERE RESPUESTA INMEDIATA

---

## 📎 Documentación de Referencia Rápida

- **Análisis Actual:** `_perfiles_tecnicos/analisis-armorasac-app.md`
- **Resumen Ejecutivo:** `_docs_implementacion/01_analisis_tecnico/RESUMEN_EJECUTIVO.md`
- **Arquitectura Propuesta:** `_docs_implementacion/05_especificaciones_tecnicas/architecture-decisions.md`
- **Validaciones Completas:** `_docs_implementacion/01_analisis_tecnico/VALIDACIONES_PENDIENTES.md`
- **Timeline:** `_docs_implementacion/04_migracion_estrategia/phasing-plan.md`

