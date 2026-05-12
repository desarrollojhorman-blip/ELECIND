# 📦 STACK TÉCNICO — ELECIND

Stack basado en la guía oficial **Entreredes 2026** (Laravel + Livewire + Alpine.js) con adaptaciones para multi-tenant y firmas digitales.

**Filosofía:** PHP hace casi todo. JS solo para UI. Menos complejidad. Más velocidad. **100% gratis (open source).**

---

## 1. Base del proyecto

| Librería | Función |
|---|---|
| `laravel/framework` **12** | Núcleo del sistema |
| PHP **8.3+** | Lenguaje |
| `laravel/pint` | Formateo automático de código |
| `nunomaduro/larastan` | Análisis estático |

## 2. Autenticación

| Librería | Función |
|---|---|
| `laravel/sanctum` | Login, tokens API, autenticación SPA |
| Laravel Fortify | Login básico web |

**Login por `username`** (NO por email, porque permitimos emails duplicados).

**Tokens de firma por email**: implementación propia (UUID firmado + caducidad), no Sanctum.

## 3. Roles y permisos

| Librería | Función |
|---|---|
| `spatie/laravel-permission` | Roles + permisos granulares |

Integración con sistema de **niveles jerárquicos** propio (ver `PERMISOS.md`).

## 4. Multi-tenant

| Librería | Función |
|---|---|
| `stancl/tenancy` | Resolución de tenant por subdominio + BD separada por tenant |

Configurado desde Fase 0 con 1 solo tenant (Elecind). En Fase 6 se conecta con la API de LucusHost para alta automática.

## 5. Frontend

| Librería | Función |
|---|---|
| **Livewire 3** | Componentes reactivos PHP (formularios, tablas, modales) |
| Alpine.js | UI ligera (dropdowns, firma canvas) — incluido con Livewire |
| TailwindCSS | Estilos utility-first |
| Flowbite (opcional) | Componentes UI base |

### Tablas
**Componente Livewire reutilizable** `<livewire:data-table />` con:
- Filtros por columna
- Ordenación
- Paginación server-side
- Exportar Excel
- Botones CRUD configurables
- Seguridad server-side (no expone JSON)

### JS adicional
| Librería | Función |
|---|---|
| `sortablejs` | Drag & drop (futuro) |
| `chart.js` | Gráficos resumen mensual |
| `flatpickr` | Selector fechas |

### Firma digital
- **Canvas API nativa** del navegador
- **Alpine.js** para estado
- **Fetch nativo** para envío como PNG base64

## 6. Archivos / Media

| Librería | Función |
|---|---|
| `spatie/laravel-medialibrary` | Gestión polimórfica de logos, firmas, adjuntos, fotos |
| `intervention/image` | Manipulación si se necesita |

### Colecciones de media
- `logos` (configuración empresa)
- `firmas` (PNG trazo)
- `albaran_documentos` (PDFs/JPGs adjuntos al albarán — múltiples)
- `factura_documentos` (PDFs/JPGs adjuntos como factura — múltiples)
- `incidencia_fotos` (fotos de incidencias)

## 7. PDF

| Librería | Función |
|---|---|
| `carlos-meneses/laravel-mpdf` | Generación de PDF (albaranes, reportes) |

Elegido frente a DomPDF por:
- Mejor soporte UTF-8 (ñ, tildes, €)
- Mejor renderizado de tablas
- CSS más moderno

## 8. Excel

| Librería | Función |
|---|---|
| `maatwebsite/excel` | Import/export Excel y CSV |

Para: clientes, albaranes, horas, ausencias, materiales. Futuro: exportación en formato Factusol.

## 9. Auditoría y logs

| Librería | Función |
|---|---|
| `spatie/laravel-activitylog` | Historial de acciones (auditoría) |

Registra: login, CRUD de entidades, cambios de estado, firmas, desbloqueos.

## 10. Backups

| Librería | Función |
|---|---|
| `spatie/laravel-backup` | Backup BD + archivos + notificación de fallos |

## 11. Data / API

| Librería | Función |
|---|---|
| `spatie/laravel-data` | DTOs tipados |
| Laravel API Resources | Alternativa nativa |

Para integraciones futuras (Sage, Holded, Factusol).

## 12. Debug y observabilidad

### Local
| Librería | Función |
|---|---|
| `laravel/telescope` | Panel queries, logs, jobs |
| `spatie/laravel-ignition` | Páginas de error detalladas |
| `opcodesio/log-viewer` | Visor de logs |

### Producción
| Librería | Función |
|---|---|
| `laravel/pulse` | Métricas tiempo real |
| `spatie/laravel-health` | Health checks |

## 13. Utilidades

| Librería | Función |
|---|---|
| `cviebrock/eloquent-sluggable` | URLs limpias |
| `spatie/laravel-responsecache` | Cache de respuestas HTTP |

## 14. Testing

| Librería | Función |
|---|---|
| `laravel/dusk` | Tests de navegador |
| Pest / PHPUnit | Tests unitarios |

## 15. Notificaciones

- Laravel Mail + SMTP de LucusHost
- Plantillas en `resources/views/emails`
- Configurables por usuario y por tenant (toggles activar/desactivar)

---

## ❌ Librerías descartadas

| Librería | Razón |
|---|---|
| Tabulator.js | Rompe filosofía stack + expone JSON (menos seguro) → Livewire data-table |
| DomPDF | Peor soporte UTF-8 → mPDF |
| DataTables.js | Necesita jQuery → incompatible |
| Stripe / pasarelas pago | Los pagos los gestiona la web SaaS principal, no esta app |
| Pusher / WebSockets | No hay notificaciones push (solo email) |

---

## 💰 Coste total

**0 €/mes en licencias de software.** Todo open source MIT/Apache.

Solo se paga:
- Hosting LucusHost
- Dominio `getradi.es`