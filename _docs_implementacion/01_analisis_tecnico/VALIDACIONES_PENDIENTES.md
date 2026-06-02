# Validaciones Pendientes y Supuestos Técnicos

**Documento:** Lista de validaciones críticas que deben resolverse ANTES de iniciar la implementación  
**Fecha:** Junio 2026  
**Responsable:** Solutions Architect + Stakeholders  
**Estado:** ⏳ PENDIENTE APROBACIÓN  

---

## 1. Validaciones Técnicas Críticas (P1)

### 1.1 ✅ Base de Datos - PostgreSQL

**Supuesto:** El sistema actual utiliza PostgreSQL como base de datos primaria.

**Confianza:** 85-90%  
**Evidencias:**
- Stack Java (típicamente PostgreSQL)
- Complejidad de cálculos (IGV, ISC, detracciones)
- Transacciones multi-tabla (venta + items)
- Inferencia de full-text search

**VALIDACIÓN REQUERIDA:**
```
[ ] Confirmar con DBA/DevOps: ¿Qué motor de BD se utiliza?
    - PostgreSQL 12, 13, 14, 15 o 16?
    - MySQL/MariaDB 8?
    - Otro?

[ ] Acceso a: herramienta de administración (pgAdmin, MySQL Workbench)
[ ] Dump anónimizado de schema actual para análisis
[ ] Estadísticas de volumen: tamaño total de BD, registros por tabla
[ ] Versión y configuración actual (recursos, timeouts, índices)
```

**Decisión:** Si es PostgreSQL → Migrar a PostgreSQL 16 (proposed)  
**Decisión:** Si es MySQL → Migrar a PostgreSQL 16 (recommended) o mantener MySQL

---

### 1.2 ✅ Integración SUNAT - Greenter vs LibPHP

**Supuesto:** El sistema utiliza una librería PHP para firmas digitales y envío SOAP a SUNAT.

**Confianza:** 70% (requiere validación urgente)

**Opciones posibles:**
1. **Greenter 5.x** (PHP 8+, modern, mantenido)
2. **LibPHP SUNAT** (antiguo, legacy)
3. **Solución custom** (homegrown XML + SOAP)

**VALIDACIÓN REQUERIDA:**
```
[ ] Inspeccionar código backend: ¿Qué librería se utiliza?
    - Buscar imports de "Greenter", "LibPHP", "FacturaElectrónica"
    - Revisar Composer.json (si es Laravel actual)
    - Revisar pom.xml (si es Maven en Java)

[ ] Código de firma digital: ¿Cómo se firman los XMLs?
    - OpenSSL en shell?
    - Librería de criptografía?
    - Acceso a certificados digitales?

[ ] Testing: ¿Ambiente de prueba SUNAT funcional?
    - Credenciales de SolTest vs Producción?
    - Histórico de CDRs?

[ ] Gestión de certificados digitales:
    - ¿Quién emite los certificados? (DIGICERT, THAWTE, etc)
    - ¿Dónde se almacenan? (Sistema de archivos, Azure Key Vault, AWS Secrets)
    - ¿Cómo se renuevan? (Proceso automático o manual)
    - ¿Expiración próxima? (Revisar dates)
```

**Decisión:** → Migrar a Greenter 5.x (recomendado para Laravel)

---

### 1.3 ✅ Autenticación - Proveedores y OAuth2

**Supuesto:** Sistema solo utiliza login con usuario/contraseña (no OAuth, no SSO).

**Confianza:** 100% (confirmado en análisis)

**VALIDACIÓN REQUERIDA:**
```
[ ] ¿Se requiere OAuth2/SSO en futuro?
    - Google Login?
    - Azure AD / Okta?
    - Integración LDAP/Active Directory corporativo?

[ ] ¿MFA (2FA) es requisito?
    - TOTP (Google Authenticator, Authy)?
    - SMS?
    - Email?

[ ] ¿Autenticación con certificado digital SUNAT?
    - RUC + Certificado digital = Login?

[ ] ¿Sesión única para múltiples dispositivos?
    - Token invalidation cuando logout?
```

**Decisión:** → Implementar Sanctum JWT (escalable, permite futuro OAuth)

---

### 1.4 ✅ Proveedores de Pago

**Supuesto:** Sistema actual NOT maneja pagos (asume efectivo/transferencia bancaria).

**Confianza:** 95% (no se detectaron endpoints de pago)

**VALIDACIÓN REQUERIDA:**
```
[ ] ¿Se requiere integración de pagos en futuro?
    - Stripe?
    - MercadoPago?
    - Izipay (peruana)?
    - Acopio?
    - Ninguno (seguir con manual)?

[ ] ¿Soporte de múltiples métodos de pago?
    - Tarjeta de crédito/débito?
    - Billetera digital?
    - Transferencia bancaria?
    - Efectivo?

[ ] ¿Reconciliación bancaria automática?
```

**Decisión:** → Arquitectura lista para Stripe/MercadoPago (no implementar ahora)

---

### 1.5 ✅ Múltiples Empresas (Tenancy)

**Supuesto:** Sistema ACTUAL asume una sola empresa, pero PROPUESTO debe ser multi-empresa.

**Confianza:** 80% (inferencia de estructura)

**VALIDACIÓN REQUERIDA:**
```
[ ] ¿Se requiere soporte multi-empresa?
    - Múltiples RUCs en el mismo sistema?
    - Múltiples series de documentos?
    - Datos compartidos (productos) vs aislados (clientes)?

[ ] Modelo de tenancy:
    - Shared database (single schema con empresa_id)?
    - Database per tenant (PostgreSQL databases separadas)?
    - Hybrid?

[ ] Acceso inter-empresa:
    - ¿Un usuario puede acceder múltiples empresas?
    - ¿Datos de una empresa visibles en otra?
    - ¿Consolidado de reportes?

[ ] SUNAT compliance:
    - ¿Cada empresa con su certificado digital?
    - ¿Separación de libros contables por empresa?
```

**Decisión:** → Implementar multi-tenancy con shared schema (empresa_id field)

---

## 2. Validaciones de Negocio (P1-P2)

### 2.1 ✅ Detracciones (Impuesto Temporal de Liquidez)

**Supuesto:** Sistema implementa cálculo de detracciones (ITF).

**Confianza:** 80% (se detectó en estructura de datos estimada)

**VALIDACIÓN REQUERIDA:**
```
[ ] ¿Qué operaciones llevan detracción?
    - Solo ventas a personas jurídicas?
    - Exentas para personas naturales?
    - Tasa de detracción por sector?

[ ] ¿Cálculo automático o manual?
    - % configurables por tipo de operación?
    - Integración con BVD (Banco de la Nación)?

[ ] ¿Reportes de detracciones?
    - Resumen por comprobante?
    - Integración con PLE/SUNAT?

[ ] ¿Reversión de detracciones?
    - Cuando se anula documento?
    - Cuando se aplica nota de crédito?
```

**Decisión:** → Implementar en módulo Finance (detracciones.php)

---

### 2.2 ✅ Retenciones (Impuesto a la Renta)

**Supuesto:** Sistema implementa cálculo automático de retenciones.

**Confianza:** 70% (no confirmado explícitamente)

**VALIDACIÓN REQUERIDA:**
```
[ ] ¿Se retiene a proveedores?
    - Servicios: 5%?
    - Arrendamiento: 5%?
    - Otros: según SUNAT?

[ ] ¿Cálculo automático?
    - Basado en tipo de proveedor?
    - Basado en tipo de concepto?

[ ] ¿Reporte y depósito de retenciones?
    - Al SII (Sunat)?
    - Electrónico (PLE)?

[ ] ¿Documentos de retención?
    - Comprobante de retención electrónico?
```

**Decisión:** → Implementar en módulo Finance

---

### 2.3 ✅ Programa de Canje de Puntos

**Supuesto:** Sistema implementa programa de fidelización con puntos y premios.

**Confianza:** 95% (confirmado en endpoints)

**VALIDACIÓN REQUERIDA:**
```
[ ] Mecánica de puntos:
    - ¿Puntos por monto de venta?
    - ¿Tasa de conversión: 1 sol = X puntos?
    - ¿Diferentes tasas por tipo de cliente?

[ ] Vigencia de puntos:
    - ¿Caducidad de puntos? (365 días, perpetuos)
    - ¿Reset anual?
    - ¿Extensión si hay movimiento?

[ ] Tipos de premios:
    - ¿Dinero/descuento?
    - ¿Productos físicos?
    - ¿Servicios?
    - ¿Viajes/experiencias?

[ ] Canje:
    - ¿Mínimo de puntos requeridos?
    - ¿Máximo que puede canjear?
    - ¿Aprobación manual o automática?
    - ¿Envío del premio?

[ ] Reportes:
    - ¿Extracción de puntos por cliente?
    - ¿Análisis de canjes?
    - ¿Predicción de caducidad?
```

**Decisión:** → Implementar en módulo Loyalty (canje_premiador.php)

---

### 2.4 ✅ Ciclo Financiero y Cierre de Período

**Supuesto:** Sistema maneja año calendario (Jan-Dec) como ciclo fiscal.

**Confianza:** 60% (no confirmado)

**VALIDACIÓN REQUERIDA:**
```
[ ] Ciclo fiscal:
    - ¿Año calendario (Ene-Dic)?
    - ¿Año fiscal personalizado?
    - ¿Múltiples ejercicios simultáneamente?

[ ] Cierre de período:
    - ¿Automático o manual?
    - ¿Permite ediciones post-cierre?
    - ¿Lock de documentos?
    - ¿Generación de asientos de cierre?

[ ] Período de corrección:
    - ¿Cuántos meses atrás se puede editar?
    - ¿Requiere aprobación?

[ ] Reportes por período:
    - ¿Comparativas multi-período?
    - ¿Análisis de tendencias?

[ ] Integración SUNAT:
    - ¿PLE mensual o anual?
    - ¿Resumen diario (RD) para SUNAT?
```

**Decisión:** → Implementar con año calendario como default, configurable

---

## 3. Validaciones de Cumplimiento SUNAT (P1)

### 3.1 Facturación Electrónica

```
[ ] Ambientes:
    - [ ] ¿SolTest (prueba) funcional?
    - [ ] ¿Producción disponible?

[ ] Documentos soportados:
    - [ ] Factura (01)?
    - [ ] Boleta (03)?
    - [ ] Nota de Crédito (07)?
    - [ ] Nota de Débito (08)?
    - [ ] Otros?

[ ] Envío a SUNAT:
    - [ ] OSE integrado?
    - [ ] Guía de remisión electrónica (GRE)?
    - [ ] Comunicación de baja?

[ ] CDR (Comprobante de Recepción):
    - [ ] Almacenamiento de CDRs?
    - [ ] Reproceso automático si falla?

[ ] Validaciones SUNAT:
    - [ ] RUC del cliente válido?
    - [ ] Serie y número secuenciales?
    - [ ] Catálogos SUNAT vigentes?
```

**Decisión:** → Greenter 5.x con Laravel Job para procesar async

---

### 3.2 Libro de Ventas (Registro de Ventas)

```
[ ] Datos a registrar:
    - [ ] Fecha de emisión?
    - [ ] Fecha de vencimiento?
    - [ ] RUC y razón social cliente?
    - [ ] Documento de identidad cliente?
    - [ ] Tipo de documento?
    - [ ] Serie y número?
    - [ ] Valor de venta (exonerado, inafecto)?
    - [ ] IGV?
    - [ ] ISC?
    - [ ] Otros tributos?
    - [ ] Total?
    - [ ] Tipo de moneda?
    - [ ] Tasa de cambio?
    - [ ] Referencia de operación relacionada?
    - [ ] Estado del comprobante?
    - [ ] Otros (ctes., doc. identidad, etc.)?

[ ] PLE (Libro Electrónico):
    - [ ] Generación automática?
    - [ ] Formato XML?
    - [ ] Envío a SUNAT?
    - [ ] Plazo (próximo mes)?

[ ] Consultas:
    - [ ] Por período?
    - [ ] Filtro por estado (registrado, anulado)?
```

**Decisión:** → Tabla libro_venta con migraciones automáticas

---

### 3.3 Consulta RUC/DNI

```
[ ] Fuente de datos:
    - [ ] API de SUNAT directa?
    - [ ] Tercero (Apilk, Mastermind, etc)?
    - [ ] Caché local?

[ ] Información obtenida:
    - [ ] Nombre/razón social?
    - [ ] Domicilio?
    - [ ] Condición tributaria?
    - [ ] Actividad económica?

[ ] Integración:
    - [ ] Auto-completa en formulario cliente?
    - [ ] Validación al crear cliente?
    - [ ] Actualización periódica?

[ ] Errores:
    - [ ] Manejo de RUC/DNI no encontrado?
    - [ ] Reintentos?
    - [ ] Fallback a entrada manual?
```

**Decisión:** → API SUNAT si disponible, Apilk como fallback

---

## 4. Validaciones de Infraestructura (P2)

### 4.1 Hospedaje y Cloud

```
[ ] Provider actual:
    - [ ] On-premise (datacenter Perú)?
    - [ ] AWS?
    - [ ] Google Cloud?
    - [ ] Azure?
    - [ ] DigitalOcean?

[ ] Nuevo provider propuesto:
    - [ ] ¿Mantener same provider?
    - [ ] ¿Migrar a cloud nuevo?
    - [ ] ¿Hybrid (cloud + on-premise)?

[ ] SLA/Disponibilidad:
    - [ ] ¿99.9% requerido?
    - [ ] ¿99.99%?
    - [ ] ¿Backup geográfico?

[ ] Cumplimiento:
    - [ ] ¿Datos deben estar en Perú?
    - [ ] ¿Cumplimiento GDPR?
    - [ ] ¿Cumplimiento local (AEPD)?
```

**Decisión:** → AWS o DigitalOcean (Perú region), Kubernetes

---

### 4.2 Certificados Digitales y Almacenamiento

```
[ ] Certificados SUNAT:
    - [ ] ¿Dónde se almacenan? (PFX, PEM, etc)
    - [ ] ¿Contraseña protegida?
    - [ ] ¿Acceso a través de Key Vault (AWS/Azure)?
    - [ ] ¿Rotación automática?
    - [ ] ¿Expiración próxima?

[ ] Privacidad de datos:
    - [ ] ¿Campos de DNI/RUC encriptados?
    - [ ] ¿Logs con datos sensibles?
    - [ ] ¿Auditoría de accesos?

[ ] Cumplimiento:
    - [ ] ¿Borrado seguro de datos?
    - [ ] ¿Período de retención legal?
```

**Decisión:** → AWS Secrets Manager + Laravel Envault

---

## 5. Validaciones Pendientes - Checklist de Aprobación

### 5.1 Antes de Iniciar Fase 1 (Setup)

```
TÉCNICAS:
[ ] ✅ Confirmar PostgreSQL como BD
[ ] ✅ Confirmar Greenter 5.x para SUNAT
[ ] ✅ Acceso a código fuente Java actual
[ ] ✅ Acceso a BD (dump anónimizado)
[ ] ✅ Certificados SUNAT identificados
[ ] ✅ Ambiente SolTest SUNAT accesible

NEGOCIO:
[ ] ✅ Requerimientos de multi-empresa definidos
[ ] ✅ Detracciones y retenciones confirmadas
[ ] ✅ Programa de puntos completamente documentado
[ ] ✅ Ciclo fiscal confirmado (año calendario)
[ ] ✅ Tipos de operación y catálogos SUNAT confirmados

STAKEHOLDERS:
[ ] ✅ Sign-off en arquitectura propuesta
[ ] ✅ Presupuesto aprobado
[ ] ✅ Timeline acordado (4-6 meses)
[ ] ✅ Equipo asignado (4-5 personas)
[ ] ✅ Representante del negocio disponible
```

---

## 6. Matriz de Decisión Pendiente

| Supuesto | Opción A | Opción B | Recomendación | Estado |
|---|---|---|---|---|
| **Base de Datos** | PostgreSQL | MySQL | PostgreSQL 16 | ⏳ Validar |
| **Integración SUNAT** | Greenter 5.x | LibPHP | Greenter 5.x | ⏳ Validar |
| **Autenticación** | JWT (Sanctum) | Session-based | JWT (Sanctum) | ✅ OK |
| **Multi-empresa** | Shared DB (tenant_id) | Database per tenant | Shared DB | ⏳ Confirmar |
| **Puntos** | Vigencia 365 días | Perpetuos | 365 días | ⏳ Confirmar |
| **Cloud** | AWS | DigitalOcean | AWS/Kubernetes | ⏳ Validar |
| **Pagos** | Stripe | MercadoPago | Diseñar para ambas (no implementar ahora) | ⏳ Future |

---

## 7. Riesgos si NO se Valida

| Validación | Riesgo si Ignora | Impacto |
|---|---|---|
| Motor BD | Arquitectura incompatible | 2-3 semanas delay |
| Greenter versión | Incompatibilidad API | 1-2 semanas rework |
| Multi-empresa | Re-arquitectura mitad del proyecto | 4-6 semanas delay |
| Detracciones | Incumplimiento SUNAT | Multas + rechazo de doc |
| Ciclo fiscal | Errores en reportes contables | Re-auditoría |
| Certificados | No poder firmar electrónico | Sistema no funcional |

---

## 8. Plan de Validación (Próximas 2 Semanas)

**Semana 1:**
- [ ] Reunión con DBA → confirmar BD
- [ ] Inspección de código SUNAT → confirmar Greenter
- [ ] Acceso a SolTest SUNAT → verificar ambiente
- [ ] Entrevista con PM de negocio → requerimientos

**Semana 2:**
- [ ] Documento de supuestos validados
- [ ] Sign-off en decisiones arquitectónicas
- [ ] Kick-off Phase 1

---

**Documento preparado por:** Solutions Architect  
**Última revisión:** Junio 2026  
**Estado:** 🔴 CRÍTICO - REQUIERE APROBACIÓN INMEDIATA
