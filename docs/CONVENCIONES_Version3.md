# 📐 CONVENCIONES — ELECIND

## 🌿 Estrategia de ramas

```
main          ← producción estable (release)
  ↑
develop       ← integración
  ↑
fase-X-nombre ← desarrollo por fase
feature/...   ← features dentro de una fase (opcional)
hotfix/...    ← arreglos urgentes en main
```

### Reglas
- **Nunca commits directos a `main` ni `develop`**.
- Cada fase → su rama desde `develop`.
- Cuando una fase está lista → PR a `develop`.
- En hitos estables → PR de `develop` a `main` + tag de versión.
- Hotfixes urgentes → rama desde `main`, PR a `main` y `develop`.

## 📝 Convención de commits

Usar **Conventional Commits**:

```
<tipo>(<scope opcional>): <descripción breve>
```

### Tipos
| Tipo | Uso |
|---|---|
| `feat` | Nueva funcionalidad |
| `fix` | Corrección de bug |
| `docs` | Solo documentación |
| `style` | Formato (no afecta lógica) |
| `refactor` | Refactor sin cambio funcional |
| `perf` | Mejora de rendimiento |
| `test` | Tests |
| `chore` | Tareas de mantenimiento (deps, config) |

### Ejemplos
```
feat(albaranes): crear albarán normal desde móvil
fix(firmas): corregir generación de token caducado
docs(readme): añadir guía instalación XAMPP
chore(deps): actualizar Livewire a 3.5
```

## 📛 Naming

### PHP / Laravel
- **Modelos**: `PascalCase`, singular → `Albaran`, `MaterialLote`.
- **Controladores**: `PascalCase`, sufijo Controller → `AlbaranController`.
- **Tablas**: `snake_case`, plural → `albaranes`, `material_lotes`, `albaran_lineas_personal`.
- **Columnas**: `snake_case` → `fecha_inicio`, `usuario_id`.
- **Métodos**: `camelCase` → `crearAlbaran()`.
- **Variables**: `camelCase` → `$albaranActual`.
- **Constantes**: `UPPER_SNAKE` → `MAX_ARCHIVOS_ALBARAN`.

### Blade / Livewire
- **Componentes Livewire**: `PascalCase` → `AlbaranesTable`.
- **Vistas**: `kebab-case` → `crear-albaran.blade.php`.

### Rutas y URLs
- **URLs**: `kebab-case` → `/parte-de-trabajo`.
- **Nombres de ruta**: `dot.notation` → `albaranes.crear`.

### JS / CSS
- **Variables JS**: `camelCase`.
- **Clases CSS personalizadas**: `kebab-case` (Tailwind ya lo es).

## 🗂️ Estructura de Pull Requests

### Título
`<tipo>: <descripción concisa>`

### Plantilla
```markdown
## 🎯 Objetivo
Qué resuelve esta PR.

## ✅ Cambios
- Cambio 1
- Cambio 2

## 🧪 Cómo probar
1. Paso 1
2. Paso 2

## 📸 Capturas (si aplica)

## ✏️ Checklist
- [ ] Tests pasando
- [ ] Pint aplicado
- [ ] Larastan sin nuevos errores
- [ ] Documentación actualizada
```

## 🧪 Calidad de código

### Antes de cada commit
```bash
./vendor/bin/pint        # Formato
./vendor/bin/phpstan     # Análisis estático
php artisan test         # Tests
```

### CI/CD (GitHub Actions)
Workflow `.github/workflows/ci.yml` ejecuta automáticamente:
- Pint check
- Larastan
- Tests Pest/PHPUnit

## 🌍 Idiomas y traducciones

- Default: **español (es)**.
- Preparado para inglés (en).
- Todos los textos visibles → `__('clave')` en lugar de hardcode.
- Archivos en `resources/lang/{es|en}/...`.

## 📐 Estructura visual estándar (todas las secciones)

```
┌─────────────────────────────────────────────────┐
│ [Sidebar]   [Título sección]                    │
│             ┌─────────────────────────────────┐ │
│             │ [+ Añadir] [✎ Modificar] [🗑]   │ │
│             │              [Excel] [Imprimir] │ │
│             └─────────────────────────────────┘ │
│             ┌─────────────────────────────────┐ │
│             │ Col1 ▲ │ Col2 ▲ │ Col3 ▲ │ ...  │ │
│             │ [filtro][filtro][filtro]        │ │
│             ├─────────────────────────────────┤ │
│             │ ... filas ...                   │ │
│             └─────────────────────────────────┘ │
└─────────────────────────────────────────────────┘
```

Implementado con componente único `<livewire:data-table />`.

## 🔢 Versionado

Semantic Versioning: `MAJOR.MINOR.PATCH`

- `MAJOR`: cambios incompatibles.
- `MINOR`: funcionalidad nueva compatible.
- `PATCH`: correcciones compatibles.

Tags en cada release:
```
v0.1.0 → Fase 0 (setup)
v0.2.0 → Fase 1 (MVP base)
v0.3.0 → Fase 2 (albaranes)
...
v1.0.0 → MVP completo (Fase 5)
```

## 🔐 Seguridad

- **Nunca** commitear `.env`, claves, tokens.
- Usar `.env.example` como plantilla.
- Variables sensibles → en `.env` + protegidas en producción.
- Validar siempre input del usuario (Form Requests + Livewire validation).
- Policies + Permission checks server-side.
- No exponer JSON sin auth → Livewire por defecto.

## 📂 Archivos a NO commitear (.gitignore)

```
/vendor
/node_modules
.env
.env.local
.env.*.local
/storage/app/public/*
/storage/framework/cache
/storage/framework/sessions
/storage/framework/views
/storage/logs/*.log
.phpunit.result.cache
.idea
.vscode
```