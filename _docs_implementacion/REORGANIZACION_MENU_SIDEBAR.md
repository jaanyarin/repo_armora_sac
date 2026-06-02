# 📋 Reorganización de Menú Sidebar - ARMORA

**Documento:** Propuesta de reorganización del menú sidebar por flujos de procesos  
**Fecha:** Junio 2026  
**Enfoque:** Acción → Consecuencias (flujo lógico)  
**Estado:** 🔄 PROPUESTA  

---

## 🎯 OBJETIVO

Reorganizar el menú sidebar de **30+ items dispersos** en **13 categorías lógicas** ordenadas por **flujos de procesos de negocio**, mejorando:
- 🧭 Navegación intuitiva
- 📊 Descubrimiento de funcionalidades
- ⚡ Velocidad de acceso
- 🎓 Curva de aprendizaje

---

## 📊 COMPARATIVA ANTES/DESPUÉS

### Antes (Actual)
```
❌ 30+ items en orden aleatorio
❌ Duplicación de funcionalidades
❌ Sin relación lógica entre items
❌ Difícil encontrar flujos relacionados
❌ Menú colapsado tarda en navegar
```

### Después (Propuesto)
```
✅ 13 categorías lógicas
✅ 2-7 items por categoría (máx 10)
✅ Flujo: Acción → Consecuencias
✅ Fácil encontrar procesos relacionados
✅ Experiencia de usuario mejorada
```

---

## 🏗️ ESTRUCTURA REORGANIZADA (13 CATEGORÍAS)

### **1. 🛒 COMPRAS** (Abastecimiento)

**Flujo:** Proveedores → Compras → Gestión → Reportes

```
├── Crear Proveedor
├── Crear Compra
├── Gestión de Compras
└── Reportes Compras
```

**Lógica de Acción:**
1. Crear Proveedor (acción: agregar proveedor)
2. Crear Compra (acción: hacer pedido)
3. Gestión de Compras (acción: administrar)
4. Reportes Compras (consecuencia: analizar)

**URLs:**
```
/app/proveedores/crear-proveedor
/app/compras/crear-compra
/app/compras/gestion-compras
(falta reportes compras en data original)
```

---

### **2. 👥 CLIENTES** (Base de datos de clientes)

**Flujo:** Crear → Gestionar → Habilitar → Controlar

```
├── Crear Cliente
├── Gestión Clientes
├── Habilitar Ventas Clientes
├── Cambio día Atención
└── Reportes Clientes
```

**Lógica de Acción:**
1. Crear Cliente (acción: registrar)
2. Gestión Clientes (acción: modificar/actualizar)
3. Habilitar Ventas Clientes (acción: activar)
4. Cambio día Atención (acción: configurar)
5. Reportes Clientes (consecuencia: analizar)

**URLs:**
```
/app/clientes/crear-cliente
/app/clientes/gestion-clientes
/app/clientes/habilitar-ventas-clientes
/app/clientes/cambio-dia-atencion
/app/clientes/reportes-clientes
```

---

### **3. 📦 CATÁLOGOS** (Maestros de datos - productos, servicios, precios)

**Flujo:** Clases → Productos → Servicios → Combos → Precios

```
Productos
├── Clases y Subclases
├── Crear Producto
├── Gestión Productos
└── Reportes Productos

Servicios
├── Crear Servicio
└── Gestión Servicios

Combos de Productos
├── Crear Combo
├── Gestión Combos
└── Reportes Combos

Listas de Precios
├── Crear Lista de Precios
├── Gestión Listas de Precios
├── Copiar Listas de Precios
└── Reportes Listas de Precios
```

**Lógica de Acción:**
1. Definir clases (estructura base)
2. Crear productos (acción principal)
3. Crear servicios (acción: alternativa)
4. Crear combos (acción: agrupar)
5. Crear precios (acción: valorizar)

**URLs:**
```
/app/productos/gestion-clases-subclases-productos
/app/productos/crear-producto
/app/productos/gestion-productos
/app/productos/reportes-productos
/app/servicios/crear-servicio
/app/servicios/gestion-servicios
/app/combos/crear-combo
/app/combos/gestion-combos
/app/combos/reportes-combos
/app/lista-precios/crear-lista-precios
/app/lista-precios/gestion-listas-precios
/app/lista-precios/copiar-lista-precios
/app/lista-precios/reportes-listas-precios
```

---

### **4. 💰 VENTAS** (Ciclo de venta principal)

**Flujo:** Preventas → Ventas → Entregas → Impresión → Reportes

```
Preventas
├── Gestión Preventas
└── Reportes Preventas

Ventas
├── Crear Venta Productos
├── Crear Venta Servicios
├── Gestión de Ventas
├── Puntos de Ventas
├── Gestión Notas Pedido
├── Entregas Parciales
├── Fileteo Automático
├── Impresión de Ventas
└── Reportes de Ventas
```

**Lógica de Acción:**
1. Gestión Preventas (acción: pre-venta)
2. Crear Venta Productos (acción: venta)
3. Crear Venta Servicios (acción: venta alternativa)
4. Gestión de Ventas (acción: administrar)
5. Puntos de Ventas (acción: ubicaciones)
6. Entregas Parciales (acción: despacho parcial)
7. Fileteo Automático (acción: división automática)
8. Impresión de Ventas (consecuencia: documentación)
9. Reportes de Ventas (consecuencia: análisis)

**URLs:**
```
/app/preventas/gestion-preventas
/app/preventas/reportes-preventas
/app/ventas/crear-venta
/app/ventas/crear-venta-servicio
/app/ventas/gestion-ventas
/app/ventas/puntos-ventas
/app/ventas/gestion-notas-pedido
/app/ventas/entregas-parciales
/app/ventas/fileteo-automatico
/app/ventas/impresion-ventas
/app/ventas/reportes-ventas
```

---

### **5. 🔄 POST-VENTA** (Ajustes y devoluciones - consecuencias de venta)

**Flujo:** Cambios → Canjes → Notas de Crédito

```
Cambios de Productos
├── Crear Cambio Productos
├── Asignar Fecha de Entrega
├── Gestión Cambios Producto
├── Impresión Cambios
├── Crear Req/Liq Cambios
├── Gestión Req/Liq Cambios
└── Reportes Cambios

Canjes de Productos
├── Crear Canje
├── Asignar Fecha de Entrega
├── Gestión Canjes
├── Impresión Canjes
├── Crear Req/Liq Canjes
├── Gestión Req/Liq Canjes
└── Reportes Canjes

Notas de Crédito
├── Gestión Notas Crédito
├── Devolución Transportista
├── Impresión Notas Crédito
└── Reportes Notas Crédito
```

**Lógica de Acción:**
1. Crear Cambio (acción: cambiar producto)
2. Crear Canje (acción: cambiar por otro)
3. Notas Crédito (consecuencia: ajuste financiero)

**URLs:**
```
/app/cambios-producto/crear-cambio-producto
/app/cambios-producto/asignar-entrega-cambios-producto
/app/cambios-producto/gestion-cambios-producto
/app/cambios-producto/impresion-cambio-producto
/app/cambios-producto/crear-reporte-cambios-producto
/app/cambios-producto/gestion-reportes-cambios-producto
/app/cambios-producto/reportes-cambios-producto
/app/canjes/crear-canje
/app/canjes/asignar-entrega-canjes
/app/canjes/gestion-canjes
/app/canjes/impresion-canjes
/app/canjes/crear-reportes-canjes
/app/canjes/gestion-reportes-canjes
/app/canjes/reportes-canjes
/app/notas-credito/gestion-notas-credito
/app/notas-credito/devoluciones-transportistas
/app/notas-credito/impresion-notas-credito
/app/notas-credito/reportes-notas-credito
```

---

### **6. 📦 INVENTARIO** (Control de stock y almacenes)

**Flujo:** Almacenes → Stock → Premios Canjes

```
Almacenes
├── Gestión Almacenes
├── Reportes Almacenes
├── Inventario de Stocks
└── Gestión de Inventarios

Premios Canjes
├── Requisitos de Canjes
├── Crear Premio Canje
├── Gestión Premio Canje
└── Reportes Premio Canje
```

**Lógica de Acción:**
1. Gestión Almacenes (acción: administrar)
2. Inventario Stocks (acción: contar)
3. Gestión Inventarios (acción: movimientos)
4. Premios Canjes (acción: inventario de regalos)

**URLs:**
```
/app/almacenes/gestion-almacenes
/app/almacenes/reportes-almacenes
/app/almacenes/inventario-stock
/app/almacenes/gestion-inventarios
/app/premio-canje/gestion-requesitos-canje
/app/premio-canje/crear-premio-canje
/app/premio-canje/gestion-premios-canjes
/app/premio-canje/reportes-premios-canjes
```

---

### **7. 🚚 LOGÍSTICA** (Distribución y transporte)

**Flujo:** Rutas → Mapas → Unidades → Transportistas

```
Zonas y Rutas
├── Gestión Zonas y Rutas
├── Habilitar Ventas en Rutas
└── Reportes Zonas y Rutas

Mapas de Rutas
├── Crear Mapa de Rutas
├── Gestión de Mapas de Rutas
└── Reportes de Mapas de Rutas

Unidades de Transporte
├── Crear Unidad Trans.
├── Gestión Unidades de Trans.
└── Reportes Unidades de Trans.

Transportistas (Portal específico)
├── Mis Devoluciones
├── Mis Requerimientos
└── Mis Liquidaciones
```

**Lógica de Acción:**
1. Gestión Zonas/Rutas (acción: definir geografía)
2. Mapas de Rutas (acción: planificar)
3. Unidades Transporte (acción: recursos)
4. Transportistas (acción: ejecución)

**URLs:**
```
/app/zonas-rutas/gestion-zonas-rutas
/app/zonas-rutas/habilitar-ventas-rutas
/app/zonas-rutas/reportes-zonas-rutas
/app/mapas-rutas/crear-mapa-rutas
/app/mapas-rutas/gestion-mapas-rutas
/app/mapas-rutas/reportes-mapas-rutas
/app/unidades-transporte/crear-unidad-transporte
/app/unidades-transporte/gestion-unidades-transporte
/app/unidades-transporte/reportes-unidades-transporte
/app/transportistas/mis-devoluciones
/app/transportistas/mis-requerimientos
/app/transportistas/mis-liquidaciones
```

---

### **8. 📋 OPERACIONES** (Reportes y control de procesos)

**Flujo:** Requerimientos → Liquidaciones → Informes → Comisiones → Cobertura

```
Requerimientos
├── Crear Requerimiento
├── Gestión Requerimientos
└── Reportes Requerimientos

Liquidaciones
├── Crear Liquidación
├── Gestión Liquidaciones
└── Reportes Liquidaciones

Informes
├── Informes de Requerimientos
├── Informes de Liquidaciones
├── Informes de Almacenes
└── Entregas Parciales

Comisiones
├── Crear Reporte Comisiones
├── Gestión Reporte Comisiones
└── Ajuste de Comisiones

Cobertura
├── Crear Reporte Cobertura
└── Gestión Reporte Cobertura
```

**Lógica de Acción:**
1. Requerimientos (acción: solicitar recursos)
2. Liquidaciones (acción: liquidar)
3. Informes (consecuencia: reportes ejecutivos)
4. Comisiones (consecuencia: cálculos de vendedores)
5. Cobertura (consecuencia: análisis de mercado)

**URLs:**
```
/app/requerimientos/crear-requerimiento
/app/requerimientos/gestion-requerimientos
/app/requerimientos/reportes-requerimientos
/app/liquidaciones/crear-liquidacion
/app/liquidaciones/gestion-liquidaciones
/app/liquidaciones/reportes-liquidaciones
/app/informes/requerimientos
/app/informes/liquidaciones
/app/informes/almacenes
/app/informes/entregas-parciales
/app/comisiones/crear-reporte-comisiones
/app/comisiones/gestion-comisiones
/app/comisiones/ajuste-comisiones
/app/cobertura/crear-reporte-cobertura
/app/cobertura/gestion-coberturas
```

---

### **9. 🧾 FINANZAS/SUNAT** (Cumplimiento regulatorio y cierre fiscal)

**Flujo:** Resúmenes → SUNAT → Bajas

```
Resúmenes Diarios
├── Crear Resumen
├── Gestión de Resúmenes
└── Reportes de Resúmenes

SUNAT
├── Envíos Pendientes SUNAT
├── Gestión Envíos SUNAT
└── Reportes Envíos SUNAT

Comunicación de Baja
├── Crear Baja
├── Gestión de Bajas
└── Reportes de Baja
```

**Lógica de Acción:**
1. Crear Resumen (acción: resumir operaciones del día)
2. SUNAT (acción: enviar a autoridad)
3. Comunicación Baja (acción: anular documentos)

**URLs:**
```
/app/resumen-diario/crear-resumen-diario
/app/resumen-diario/gestion-resumenes-diarios
/app/resumen-diario/reportes-resumenes-diarios
/app/sunat/envios-sunat
/app/sunat/gestion-envios-sunat
/app/sunat/reportes-envios-sunat
/app/comunicacion-baja/crear-comunicacion-baja
/app/comunicacion-baja/gestion-comunicaciones-baja
/app/comunicacion-baja/reportes-comunicaciones-baja
```

---

### **10. 👤 PERSONAL** (Gestión de empleados)

**Flujo:** Crear → Gestionar → Reportes

```
├── Crear Personal
├── Gestión Personal
└── Reportes Personal
```

**Lógica de Acción:**
1. Crear Personal (acción: registrar)
2. Gestión Personal (acción: administrar)
3. Reportes Personal (consecuencia: análisis)

**URLs:**
```
/app/personal/crear-personal
/app/personal/gestion-personal
/app/personal/reportes-personal
```

---

### **11. ⚙️ CONFIGURACIÓN** (Administración del sistema)

**Flujo:** Configuraciones base del sistema

```
├── Configuración Empresa
├── Configuración Impresión
├── Configuración SUNAT
└── Configuración Alertas
```

**Lógica de Acción:**
1. Configuración Empresa (setup: datos generales)
2. Configuración Impresión (setup: formatos)
3. Configuración SUNAT (setup: autoridad)
4. Configuración Alertas (setup: notificaciones)

**URLs:**
```
/app/configuracion/configuracion-empresa
/app/configuracion/configuracion-impresion
/app/configuracion/configuracion-sunat
/app/configuracion/configuracion-alertas
```

---

### **12. 💼 VENDEDOR** (Portal específico - rol limitado)

**Acceso limitado a funcionalidades clave del vendedor:**

```
Clientes
├── Crear Cliente
└── Clientes

Preventas
├── Crear Preventa
└── Preventas

Ventas
└── Ventas

Productos
├── Cambios de Productos
│   └── Crear Cambio Productos
├── Canjes
│   └── Crear Canje
└── Stock de Productos

Desempeño
├── Comisiones
└── Concursos
```

**Lógica:** Acciones que el vendedor necesita (sin admin)

**URLs:**
```
/app/vendedor/crear-cliente
/app/vendedor/clientes
/app/vendedor/crear-preventa
/app/vendedor/preventas
/app/vendedor/ventas
/app/vendedor/crear-cambio-producto
/app/vendedor/cambios-producto
/app/vendedor/crear-canje
/app/vendedor/canjes
/app/vendedor/stock-productos
/app/vendedor/comisiones
/app/vendedor/concursos
```

---

### **13. 🏠 GENERAL** (Accesos rápidos)

```
├── Perfil Personal
└── Home
```

**URLs:**
```
/app/general/perfil-personal
/app/home
```

---

## 📊 MATRIZ DE CAMBIOS

| Anterior | Nueva Ubicación | Razón |
|---|---|---|
| Compras (disperso) | Categoría 1: COMPRAS | Agrupar flujo abastecimiento |
| Clientes (disperso) | Categoría 2: CLIENTES | Agrupar flujo clientes |
| Productos (disperso) | Categoría 3: CATÁLOGOS | Agrupar maestros de datos |
| Servicios (disperso) | Categoría 3: CATÁLOGOS | Es un catálogo |
| Combos (disperso) | Categoría 3: CATÁLOGOS | Es un catálogo |
| Listas de Precios (disperso) | Categoría 3: CATÁLOGOS | Es un catálogo |
| Ventas (disperso) | Categoría 4: VENTAS | Agrupar ciclo venta |
| Preventas (disperso) | Categoría 4: VENTAS | Parte del ciclo venta |
| Cambios (disperso) | Categoría 5: POST-VENTA | Consecuencia de venta |
| Canjes (disperso) | Categoría 5: POST-VENTA | Consecuencia de venta |
| Notas Crédito (disperso) | Categoría 5: POST-VENTA | Consecuencia de venta |
| Almacenes (disperso) | Categoría 6: INVENTARIO | Agrupar control stock |
| Premios Canjes (disperso) | Categoría 6: INVENTARIO | Es inventario de regalos |
| Zonas y Rutas (disperso) | Categoría 7: LOGÍSTICA | Agrupar distribución |
| Mapas de Rutas (disperso) | Categoría 7: LOGÍSTICA | Agrupar distribución |
| Unidades de Transporte (disperso) | Categoría 7: LOGÍSTICA | Agrupar distribución |
| Transportistas (disperso) | Categoría 7: LOGÍSTICA | Agrupar distribución |
| Requerimientos (disperso) | Categoría 8: OPERACIONES | Agrupar reportes |
| Liquidaciones (disperso) | Categoría 8: OPERACIONES | Agrupar reportes |
| Informes (disperso) | Categoría 8: OPERACIONES | Agrupar reportes |
| Comisiones (disperso) | Categoría 8: OPERACIONES | Agrupar reportes |
| Cobertura (disperso) | Categoría 8: OPERACIONES | Agrupar reportes |
| Resúmenes Diarios (disperso) | Categoría 9: FINANZAS/SUNAT | Agrupar cierre fiscal |
| SUNAT (disperso) | Categoría 9: FINANZAS/SUNAT | Agrupar cierre fiscal |
| Comunicación Baja (disperso) | Categoría 9: FINANZAS/SUNAT | Agrupar cierre fiscal |
| Personal (disperso) | Categoría 10: PERSONAL | Agrupar gestión RRHH |
| Configuración (disperso) | Categoría 11: CONFIGURACIÓN | Agrupar setup |
| Vendedor (disperso) | Categoría 12: VENDEDOR | Portal específico |
| General (disperso) | Categoría 13: GENERAL | Accesos rápidos |

---

## 🎯 BENEFICIOS DE LA REORGANIZACIÓN

### Para Usuarios
| Beneficio | Impacto |
|---|---|
| **Navegación Intuitiva** | Encuentra lo que busca en 2-3 clicks |
| **Menos Scrolling** | Menú más compacto y lógico |
| **Flujos Claros** | Entiende orden de operaciones |
| **Descubrimiento** | Ve funcionalidades relacionadas |
| **Menos Errores** | Menos confusión de ubicación |

### Para Negocio
| Beneficio | Impacto |
|---|---|
| **Adopción Rápida** | Usuarios aprenden más rápido |
| **Menor Soporte** | Menos preguntas de "¿dónde está?" |
| **Errores Operacionales ↓** | Menos acciones en orden incorrecto |
| **Eficiencia ↑** | Menos tiempo buscando funciones |
| **Satisfacción ↑** | Experiencia mejorada |

---

## 🔄 MATRIZ DE FLUJOS (Acción → Consecuencias)

```
COMPRAS:        Proveedor → Compra → Gestión → Reportes ✓
CLIENTES:       Crear → Gestionar → Habilitar → Reportes ✓
CATÁLOGOS:      Clases → Productos → Servicios → Combos → Precios ✓
VENTAS:         Preventa → Venta → Entregas → Impresión → Reportes ✓
POST-VENTA:     Cambios → Canjes → Notas Crédito ✓
INVENTARIO:     Almacenes → Stock → Premios ✓
LOGÍSTICA:      Rutas → Mapas → Unidades → Transportistas ✓
OPERACIONES:    Requerimientos → Liquidaciones → Informes → Comisiones → Cobertura ✓
FINANZAS:       Resúmenes → SUNAT → Bajas ✓
PERSONAL:       Crear → Gestionar → Reportes ✓
CONFIG:         Setup base del sistema ✓
VENDEDOR:       Portal limitado para vendedores ✓
GENERAL:        Accesos rápidos ✓
```

---

## 📈 IMPLEMENTACIÓN

### Fase 1: Validación (Esta semana)
```
[ ] Revisar con stakeholders
[ ] Validar URLs existentes
[ ] Confirmar orden lógico
[ ] Identificar gaps (ej: reportes compras)
```

### Fase 2: Desarrollo (Próxima semana)
```
[ ] Actualizar estructura HTML/CSS del sidebar
[ ] Reorganizar items según nueva estructura
[ ] Mantener URLs existentes
[ ] Validar enlaces rotos
```

### Fase 3: Testing (Semana 3)
```
[ ] Probar navegación en todos los roles
[ ] Validar collapsos/expandibles
[ ] Testing en móvil (responsive)
[ ] User acceptance testing
```

### Fase 4: Deployment (Semana 4)
```
[ ] Desplegar a producción
[ ] Monitorear errores
[ ] Recopilar feedback de usuarios
[ ] Ajustes si es necesario
```

---

## 🔍 VALIDACIONES NECESARIAS

### URLs Faltantes (No encontradas en data original)
```
❌ Reportes Compras
❌ (Verificar si existen otros reportes)
```

### Posibles Optimizaciones
```
⚠️ ¿Mover "Premios Canjes" a POST-VENTA?
⚠️ ¿Agrupar "Transportistas" solo en LOGÍSTICA?
⚠️ ¿Simplificar "Cambios/Canjes" (mucha repetición)?
⚠️ ¿Crear categoría "REPORTES" consolidado?
```

---

## 📝 NOTAS FINALES

✅ **Estructura:** Reorganizada por flujos de procesos (acción → consecuencias)  
✅ **Categorías:** 13 agrupaciones lógicas  
✅ **Items:** 2-7 por categoría (máximo 10)  
✅ **Flujos:** Todos los procesos tienen order lógico  
✅ **URLs:** Mantienen las URLs existentes  

**Listo para:** Validación con stakeholders y desarrollo

---

**Documento:** Reorganización de Menú Sidebar  
**Fecha:** Junio 2026  
**Estado:** 🔄 PROPUESTA LISTA PARA VALIDACIÓN

