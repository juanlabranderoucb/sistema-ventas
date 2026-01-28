# 🚀 Guía de CI/CD - Sistema de Ventas

Sistema de integración continua y despliegue continuo con múltiples etapas de calidad y entornos segregados.

## 📊 Arquitectura del Pipeline

```
┌─────────────────────────────────────────────────────────────┐
│                    Push/Merge a Main                        │
└────────────────────────┬────────────────────────────────────┘
                         │
         ┌───────────────┴───────────────┐
         │                               │
    ┌────▼─────┐                    ┌────▼─────┐
    │  Build   │                    │ CI Tests │
    │ Artifacts│                    │  Quality │
    └────┬─────┘                    └────┬─────┘
         │                               │
         └───────────────┬───────────────┘
                         │
                    ✅ All Pass
                         │
         ┌───────────────▼───────────────┐
         │                               │
    ┌────▼──────────────┐        ┌───────▼────────┐
    │ Deploy Staging   │        │  Auto Health   │
    │ (SSH + rsync)    │        │    Check       │
    └────┬──────────────┘        └────────────────┘
         │
    ✅ Staging OK
         │
    ⏸️ Manual Approval
         │ (Release Manager)
         │
    ┌────▼────────────────┐
    │ Deploy Production   │
    │ - Atomic symlink    │
    │ - Backup ready      │
    │ - Rollback support  │
    └─────────────────────┘
```

## 🔄 Flujo por Etapa

### A. CI (Integración Continua)

**Trigger:** Push/Merge a `main`

#### 1️⃣ Build (Artifact único)
```bash
npm run build  # Genera public/build/
```
- Frontend compilado una sola vez
- Compartido con todos los jobs
- Almacenado 1 día

#### 2️⃣ Backend Quality Gates (Paralelo)
```bash
./vendor/bin/pint --test      # Formato PHP
./vendor/bin/phpstan analyse  # Análisis estático
```
- Sin dependencias de desarrollo
- Cache de Composer habilitado

#### 3️⃣ Backend Tests (Paralelo)
```bash
php artisan test  # Tests con PostgreSQL real
```
- Servicio PostgreSQL 16
- Migraciones automáticas
- Base de datos limpia

#### 4️⃣ Frontend Quality & Tests (Paralelo)
```bash
npm run lint         # ESLint Vue
npm run test         # Vitest
```

### B. Deploy a Staging

**Trigger:** CI exitoso automáticamente

#### Configuración:
```bash
# 1. Desempaquetado SSH + rsync
rsync -avz --exclude='.' . server:/releases/SHA8/

# 2. En servidor:
php artisan migrate --force   # Migraciones
php artisan cache:clear       # Limpiar cachés
php artisan config:cache      # Optimizar

# 3. Symlink atómico
ln -sfn /releases/SHA8 /current.tmp
mv -Tf /current.tmp /current

# 4. Health check
curl /api/health → 200 OK
```

#### Environment: `staging`
```bash
STAGING_HOST=staging.example.com
STAGING_USER=deploy
STAGING_PATH=/var/www/app
STAGING_URL=https://staging.example.com
```

#### Características:
- ✅ Despliegue automático
- ✅ Verificación post-deploy
- ✅ Notificación en commit
- ✅ Sin aprobación manual

### C. Deploy a Producción

**Trigger:** Manual (workflow_dispatch)

#### Validaciones previas:
1. Verificar que staging fue exitoso
2. Aprobación manual en GitHub Environments
3. Historial disponible en commit

#### Procedimiento:
```bash
# 1. Backup previo
ln -s $(readlink /current) /previous

# 2. Nuevo symlink
ln -sfn /releases/SHA8 /current.tmp
mv -Tf /current.tmp /current  # Atómico

# 3. Health check
curl /api/health → 200 OK

# 4. Si falla → Rollback automático
ln -sfn $(readlink /previous) /current
```

#### Environment: `production`
```bash
PRODUCTION_HOST=prod.example.com
PRODUCTION_USER=deploy
PRODUCTION_PATH=/var/www/app
PRODUCTION_URL=https://example.com
```

#### Características:
- ✅ Aprobación humana obligatoria
- ✅ Symlink atómico (cero downtime)
- ✅ Backup automático
- ✅ Rollback automático si falla
- ✅ Logs con contexto de release

---

## 🔐 Configuración de Secrets

### GitHub Environments

Ir a: **Settings → Environments → [staging|production]**

#### Staging Secrets:
| Secret | Valor | Ejemplo |
|--------|-------|---------|
| `STAGING_HOST` | IP/hostname del servidor | `staging.example.com` |
| `STAGING_USER` | Usuario SSH | `deploy` |
| `STAGING_PATH` | Ruta base en servidor | `/var/www/app` |
| `STAGING_URL` | URL de la app | `https://staging.example.com` |
| `STAGING_DEPLOY_KEY` | Clave SSH privada | (multiline) |

#### Production Secrets:
| Secret | Valor | Ejemplo |
|--------|-------|---------|
| `PRODUCTION_HOST` | IP/hostname | `prod.example.com` |
| `PRODUCTION_USER` | Usuario SSH | `deploy` |
| `PRODUCTION_PATH` | Ruta base | `/var/www/app` |
| `PRODUCTION_URL` | URL de la app | `https://example.com` |
| `PRODUCTION_DEPLOY_KEY` | Clave SSH privada | (multiline) |

### Generación de SSH Key

```bash
# En tu máquina local
ssh-keygen -t ed25519 -f deploy_key -N ""

# Copiar clave pública al servidor
ssh-copy-id -i deploy_key.pub deploy@server.com

# Usar deploy_key (privada) en GitHub Secrets
cat deploy_key | pbcopy  # macOS
# o
cat deploy_key | xclip -selection clipboard  # Linux
```

### Estructura de directorios en servidor

```bash
/var/www/app/
├── releases/
│   ├── 20260128-1/
│   ├── 20260127-2/
│   └── 20260127-1/
├── shared/
│   ├── .env.staging
│   ├── .env.production
│   └── storage/
├── current → releases/20260128-1  # Symlink activo
└── previous → releases/20260127-2  # Para rollback
```

---

## 📋 Configuración de Archivos .env

### .env.staging
```env
APP_ENV=staging
APP_DEBUG=false
DB_CONNECTION=pgsql
DB_HOST=db.staging.internal
DB_DATABASE=sistema_ventas_staging
DB_USERNAME=ventas_user
DB_PASSWORD=<SECURE_PASSWORD>
CACHE_DRIVER=redis
LOG_LEVEL=info
```

### .env.production
```env
APP_ENV=production
APP_DEBUG=false
DB_CONNECTION=pgsql
DB_HOST=db.production.internal
DB_DATABASE=sistema_ventas
DB_USERNAME=ventas_user
DB_PASSWORD=<VERY_SECURE_PASSWORD>
CACHE_DRIVER=redis
LOG_LEVEL=warning
```

---

## 🏥 Health Checks

### Endpoint: `/api/health`

**Request:**
```bash
curl -X GET https://example.com/api/health
```

**Response (200 OK):**
```json
{
  "status": "ok",
  "timestamp": "2026-01-28T10:30:00Z",
  "environment": "production"
}
```

El health check se ejecuta automáticamente post-deploy y debe retornar **HTTP 200** en menos de 2 minutos.

---

## 📝 Procedimiento de Despliegue Manual

### Desde GitHub UI:

1. **Ir a:** Actions → Deploy to Production
2. **Click:** Run workflow
3. **Seleccionar:** Rama `main`
4. **Esperar:** Validación de staging
5. **Aprobar:** En el prompt de Environment
6. **Monitor:** Ver logs en tiempo real

### Desde CLI (GitHub CLI):

```bash
# Listar workflows
gh workflow list

# Ejecutar deploy production
gh workflow run deploy_production.yml \
  -f environment=production

# Ver estatus
gh run list --workflow=deploy_production.yml

# Ver logs
gh run view <RUN_ID> --log
```

---

## 🔄 Rollback Manual

En caso de necesidad, desde el servidor:

```bash
ssh deploy@prod.example.com

# Ver releases disponibles
ls -la /var/www/app/releases/

# Ir a anterior
ln -sfn $(readlink /var/www/app/previous) /var/www/app/current

# Verificar
curl /api/health
```

---

## 📊 Observabilidad

### Logs en Backend

Cada deployment incluye contexto:

```php
Log::info('Application deployed', [
    'version' => env('APP_VERSION'),     // release ID
    'environment' => env('APP_ENV'),     // staging/production
    'deployed_by' => env('DEPLOYED_BY'), // GitHub actor
    'timestamp' => now(),
]);
```

### Logs de Deploy

```bash
# En servidor, ver logs de deployment
tail -f /var/www/app/shared/deploy.log

# Docker (si aplica)
docker logs app-container | grep -i deploy
```

### Monitoreo

Se recomienda configurar alertas para:
- ❌ Fallos en health check
- ⚠️ Rollback automático ejecutado
- 🔴 Downtime en /api/health > 30 segundos

---

## 🧪 Testing del Pipeline Localmente

### Simular CI localmente:

```bash
# Build
npm run build

# Quality checks
./vendor/bin/pint --test
./vendor/bin/phpstan analyse

# Tests
php artisan test
npm run test
npm run lint
```

### Simular Deploy a Staging:

```bash
# Necesita acceso SSH a servidor staging
./scripts/deploy/deploy.sh staging
```

---

## ⚠️ Buenas Prácticas

✅ **HACER:**
- Mergear a `main` solo código probado
- Revisar PRs antes de merge
- Usar GitHub Environments
- Monitorear health checks
- Documentar cambios en CHANGELOG

❌ **NO HACER:**
- Secrets en código o logs
- Despliegues manuales sin pipeline
- Saltarse healthchecks
- Deployments sin tests verdes
- Cambios directos en producción

---

## 🚨 Troubleshooting

| Problema | Solución |
|----------|----------|
| SSH timeout | Verificar security groups, firewall |
| rsync permission denied | Revisar SSH key permisos (600) |
| Health check falla | Ver logs: `tail -f storage/logs/` |
| Rollback automático | Revisar /previous symlink existe |
| Migraciones fallan | Estar en rama correcta, verificar BD |

---

## 📚 Referencias

- [GitHub Actions Docs](https://docs.github.com/actions)
- [SSH Best Practices](https://ssh.com/ssh/config)
- [Laravel Deployment](https://laravel.com/docs/deployment)
