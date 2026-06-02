# Infraestructura y Servidores — ARMORA Legacy

## 1. Proveedores de Servicios

| Servicio | Proveedor | Rol | Ubicación |
|---|---|---|---|
| **Hosting Web** | ON EMPRESAS S.A.C. (AS27843) | Servidor dedicado/VPS con cPanel | Lima, Perú |
| **DNS** | iPage (Newfold Digital) | Nameservers ns1/ns2.ipage.com | EE.UU. |
| **Correo** | iPage | MX: mx.armorasac.com (66.96.140.x) | EE.UU. |

---

## 2. Topología de Red Actual

```
                 ┌──────────────────────┐
                 │      Internet         │
                 └──────┬───────────────┘
                        │
              ┌─────────▼──────────┐
              │    DNS: iPage       │
              │  ns1/ns2.ipage.com  │
              └─────────┬──────────┘
                        │
              ┌─────────▼──────────┐
              │   Firewall / Router │
              │  ON EMPRESAS S.A.C. │
              │   38.210.245.233    │
              └─────────┬──────────┘
                        │
              ┌─────────▼──────────┐
              │    cPanel/WHM       │
              │  Apache + Tomcat    │
              │  + MySQL/PostgreSQL │
              └─────────┬──────────┘
                        │
        ┌───────────────┼───────────────┐
        │               │               │
  ┌─────▼─────┐   ┌────▼────┐   ┌──────▼───┐
  │  Web App  │   │   API   │   │   DB     │
  │  Java JSP │   │  REST   │   │ (local)  │
  │ jQuery/UI │   │ catalogo│   │          │
  └───────────┘   └─────────┘   └──────────┘
```

---

## 3. Servidor Web (38.210.245.233)

| Aspecto | Valor |
|---|---|
| **IP** | `38.210.245.233` |
| **ISP** | ON EMPRESAS S.A.C. (AS27843) |
| **Ubicación** | Lima, Perú (lat: -12.0432, lon: -77.0282) |
| **Panel** | cPanel/WHM (confirmado por `cpanel.armorasac.com`) |
| **Tipo** | Dedicado o VPS (cPanel descarta shared hosting básico) |
| **OS** | Linux (CentOS / AlmaLinux / CloudLinux — estándar cPanel) |
| **Web Server** | Apache HTTPD + Apache Tomcat (por JSESSIONID) |
| **SSL** | Let's Encrypt o AutoSSL de cPanel |
| **Base de Datos** | Local en el mismo servidor (MariaDB / PostgreSQL) |
| **IP directa** | Bloquea acceso con 403 Forbidden (configuración cPanel estándar) |

---

## 4. Registros DNS

| Registro | Tipo | Valor | TTL |
|---|---|---|---|
| `armorasac.com` | A | `38.210.245.233` | 3600 |
| `www.armorasac.com` | A | `38.210.245.233` | 3600 |
| `cpanel.armorasac.com` | A | `38.210.245.233` | 3600 |
| `mail.armorasac.com` | A | `66.96.147.112` | 3600 |
| `webmail.armorasac.com` | A | `66.96.147.20` | 3600 |
| `mx.armorasac.com` | A | `66.96.140.160` / `66.96.140.161` | 3600 |
| `armorasac.com` | MX | `mx.armorasac.com` (pref 30) | 3600 |
| `armorasac.com` | NS | `ns1.ipage.com` / `ns2.ipage.com` | 3600 |
| `armorasac.com` | TXT | `v=spf1 ip4:66.96.128.0/18 include:websitewelcome.com ?all` | 3600 |
| `armorasac.com` | SOA | `dnsadmin.ipage.com` (serial `2023072694`) | 3600 |

---

## 5. Seguridad Perimetral

| Aspecto | Estado | Detalle |
|---|---|---|
| **HSTS** | ✅ | `max-age=31536000; includeSubDomains` |
| **X-Frame-Options** | ✅ | `DENY` — protección contra clickjacking |
| **X-Content-Type-Options** | ✅ | `nosniff` — previene MIME sniffing |
| **X-XSS-Protection** | ✅ | `1; mode=block` (legacy browsers) |
| **CSP** | ❌ No implementado | Riesgo de inyección XSS |
| **WAF** | ❌ No detectado | Sin Cloudflare, sin ModSecurity visible |
| **CDN** | ❌ No tiene | Todo el tráfico golpea directo al servidor |
| **DDoS Protection** | ❌ No detectado | Sin protección a nivel de red |
| **SSL** | ⚠️ Problemas | Error de cadena de confianza desde clientes externos (posible cert vencido o cadena incompleta) |
| **IP directa** | ✅ 403 Forbidden | Configuración cPanel estándar bloquea acceso por IP |
| **Referrer-Policy** | ❌ No implementado | |
| **Permissions-Policy** | ❌ No implementado | |

---

## 6. Análisis de Riesgos

| Riesgo | Impacto | Descripción | Mitigación Propuesta |
|---|---|---|---|
| **Single Point of Failure** | 🔴 Alto | 1 servidor, 1 IP, 1 DB local. Si falla hardware, red o energía, todo el sistema cae | Migrar a cloud con alta disponibilidad (mínimo 2 servidores + RDS) |
| **Servidor + DB juntos** | 🟡 Medio | App y DB compiten por CPU, RAM y IO. Un pico de uso de la app ralentiza la DB y viceversa | Separar DB en instancia independiente (RDS / Cloud SQL) |
| **Sin CDN** | 🟡 Medio | Assets (JS, CSS, imágenes) se sirven desde el mismo servidor, sin caching distribuido | Agregar Cloudflare (gratuito) o servir assets desde S3 + CloudFront |
| **Sin WAF** | 🟡 Medio | Sin protección contra SQL injection, XSS, force browsing | Cloudflare WAF o AWS WAF |
| **SSL problemático** | 🟡 Medio | Usuarios pueden ver advertencia "Conexión no segura" en el navegador | Renovar cert y configurar cadena completa con Let's Encrypt / certbot |
| **cPanel expuesto** | 🟢 Bajo | `cpanel.armorasac.com` resuelve públicamente. Expuesto a fuerza bruta | Bloquear acceso público a cpanel (solo por IP autorizada o VPN) |
| **DDoS** | 🟡 Medio | Sin protección, un ataque DDoS moderado puede tumbar el servidor | Cloudflare (plan gratuito ya mitiga DDoS L3/L4) |

---

## 7. Recomendación: Arquitectura Cloud para el Nuevo Sistema

```
                    ┌───────────────────────────────────┐
                    │        Cloudflare (CDN + WAF)      │
                    │  DDoS protection + SSL + Caching    │
                    └──────────────┬────────────────────┘
                                   │
                    ┌──────────────▼────────────────────┐
                    │     Load Balancer (ALB / HAProxy)  │
                    └──┬──────────────────────────────┬─┘
                       │                              │
              ┌────────▼────────┐          ┌─────────▼─────────┐
              │   App Server 1  │          │   App Server 2    │
              │  Laravel + PHP  │          │  Laravel + PHP    │
              │  React (build)  │          │  React (build)    │
              └────────┬────────┘          └─────────┬─────────┘
                       │                              │
                       └──────────────┬───────────────┘
                                      │
                    ┌─────────────────▼─────────────────┐
                    │       PostgreSQL RDS / Cloud SQL   │
                    │  (instancia separada, multi-AZ)    │
                    └─────────────────┬─────────────────┘
                                      │
                    ┌─────────────────▼─────────────────┐
                    │         Redis / ElastiCache         │
                    │  (sesiones, cache, colas)           │
                    └───────────────────────────────────┘
```

### Proveedor Recomendado

| Proveedor | Servicio App | Servicio DB | CDN | Aprox. Costo/mes |
|---|---|---|---|---|
| **DigitalOcean** | App Platform o Droplets (2) | Managed DB | Cloudflare gratis | $50–$150 |
| **AWS** | EC2 (t3.medium ×2) o ECS | RDS PostgreSQL | CloudFront | $100–$300 |
| **Railway** | Railway Services | Railway PostgreSQL | Cloudflare gratis | $20–$80 |

### Migración por Fases

```
Fase 1: Cloudflare DNS + CDN (migrar DNS desde iPage)
  └── Sin downtime, solo cambiar NS apuntando a Cloudflare

Fase 2: Base de datos separada (RDS / Managed DB)
  └── App legacy sigue funcionando, DB migrada a cloud

Fase 3: Nuevo backend Laravel + frontend React
  └── Correr en paralelo (Strangler Fig), legacy sigue vivo

Fase 4: Desmantelar servidor legacy ON EMPRESAS
  └── Todo en cloud, eliminar servidor físico
```

---

> **Resumen**: ARMORA actual corre en un servidor único (Lima, ON EMPRESAS S.A.C.) con cPanel, Apache + Tomcat, y DNS/correo en iPage. No tiene redundancia, CDN, WAF ni balanceo. Para el nuevo sistema se recomienda migrar a cloud (DigitalOcean/AWS/Railway) con Cloudflare como CDN+WAF, base de datos separada, y al menos 2 servidores para alta disponibilidad.
