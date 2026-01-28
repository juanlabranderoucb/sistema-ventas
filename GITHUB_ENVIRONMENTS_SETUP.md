# 🔐 Configuración de GitHub Environments

## Paso 1: Crear SSH Key para Deploy

```bash
# Generar clave SSH Ed25519 (más segura)
ssh-keygen -t ed25519 -f deploy_key -N "" -C "deploy@sistema-ventas"

# Listar archivos generados
ls -la deploy_key*
# deploy_key (privada - 🔒 para GitHub Secrets)
# deploy_key.pub (pública - para servidor)
```

## Paso 2: Configurar servidor (Staging)

```bash
# En servidor staging
ssh deploy@staging.example.com

# Agregar clave pública
mkdir -p ~/.ssh
cat << 'EOF' >> ~/.ssh/authorized_keys
ssh-ed25519 AAAAC3NzaC1lZDI1NTE5AAAAI... deploy@sistema-ventas
EOF

chmod 600 ~/.ssh/authorized_keys

# Crear estructura de directorios
sudo mkdir -p /var/www/app/{releases,shared}
sudo chown deploy:deploy /var/www/app
chmod 755 /var/www/app

# Copiar archivos de configuración
sudo mkdir -p /var/www/app/shared
echo "APP_ENV=staging" | sudo tee /var/www/app/shared/.env.staging > /dev/null
# ... agregar resto de variables
```

## Paso 3: Crear GitHub Environment - STAGING

1. **Ir a:** Repository → Settings → Environments
2. **Click:** New environment
3. **Name:** `staging`
4. **Deployment branches:** Main
5. **Click:** Create environment

### Agregar Secrets a Staging

En la página del environment, agregar estos secrets:

| Nombre | Valor |
|--------|-------|
| `STAGING_HOST` | `staging.example.com` |
| `STAGING_USER` | `deploy` |
| `STAGING_PATH` | `/var/www/app` |
| `STAGING_URL` | `https://staging.example.com` |
| `STAGING_DEPLOY_KEY` | (contenido de `deploy_key` privada) |

**Para copiar la clave privada:**
```bash
cat deploy_key | pbcopy  # macOS
# o
cat deploy_key | xclip -selection clipboard  # Linux
# Pegar en GitHub Secret textbox
```

## Paso 4: Configurar servidor (Production)

```bash
# En servidor production (repetir proceso)
ssh deploy@prod.example.com

mkdir -p ~/.ssh
cat << 'EOF' >> ~/.ssh/authorized_keys
ssh-ed25519 AAAAC3NzaC1lZDI1NTE5AAAAI... deploy@sistema-ventas
EOF

chmod 600 ~/.ssh/authorized_keys

# Estructura de directorios
sudo mkdir -p /var/www/app/{releases,shared}
sudo chown deploy:deploy /var/www/app
chmod 755 /var/www/app

# Copiar archivos de configuración
echo "APP_ENV=production" | sudo tee /var/www/app/shared/.env.production > /dev/null
# ... resto de variables
```

## Paso 5: Crear GitHub Environment - PRODUCTION

1. **Ir a:** Repository → Settings → Environments
2. **Click:** New environment
3. **Name:** `production`
4. **Deployment branches:** Main (en production)
5. **Required reviewers:** ✅ Habilitar (mínimo 1 revisor)
6. **Click:** Create environment

### Agregar Secrets a Production

| Nombre | Valor |
|--------|-------|
| `PRODUCTION_HOST` | `prod.example.com` |
| `PRODUCTION_USER` | `deploy` |
| `PRODUCTION_PATH` | `/var/www/app` |
| `PRODUCTION_URL` | `https://example.com` |
| `PRODUCTION_DEPLOY_KEY` | (contenido de `deploy_key` privada) |

### Asignar Reviewers

En "Deployment branches" → "Add deployment branch rule":
- Branch: `main`
- Required reviewers: Seleccionar usuarios autorizados para production

## Paso 6: Preparar Variables de Entorno en Servidores

### Staging - `/var/www/app/shared/.env.staging`

```env
APP_NAME="Sistema de Ventas"
APP_ENV=staging
APP_DEBUG=false
APP_URL=https://staging.example.com
APP_KEY=base64:...

LOG_CHANNEL=stack
LOG_LEVEL=info

DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=sistema_ventas_staging
DB_USERNAME=ventas_user
DB_PASSWORD=password_aqui

CACHE_DRIVER=redis
CACHE_PREFIX=staging_

REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379

MAIL_DRIVER=smtp
MAIL_HOST=smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=your_username
MAIL_PASSWORD=your_password
```

### Production - `/var/www/app/shared/.env.production`

```env
APP_NAME="Sistema de Ventas"
APP_ENV=production
APP_DEBUG=false
APP_URL=https://example.com
APP_KEY=base64:...

LOG_CHANNEL=stack
LOG_LEVEL=warning

DB_CONNECTION=pgsql
DB_HOST=db.internal
DB_PORT=5432
DB_DATABASE=sistema_ventas
DB_USERNAME=ventas_user
DB_PASSWORD=super_secure_password

CACHE_DRIVER=redis
CACHE_PREFIX=prod_

REDIS_HOST=redis.internal
REDIS_PASSWORD=redis_password
REDIS_PORT=6379

MAIL_DRIVER=smtp
MAIL_HOST=smtp.sendgrid.net
MAIL_PORT=587
MAIL_USERNAME=apikey
MAIL_PASSWORD=sg_key_here
```

## Paso 7: Verificar Conectividad

### Test SSH desde GitHub Actions (local simulation)

```bash
# Simular que GitHub Actions prueba la conexión
ssh -i deploy_key -o ConnectTimeout=5 deploy@staging.example.com "echo '✅ SSH Connection OK'"
ssh -i deploy_key -o ConnectTimeout=5 deploy@prod.example.com "echo '✅ SSH Connection OK'"
```

## Paso 8: Validar Setup

### Checklist Final

- [ ] SSH key generada (ed25519)
- [ ] Clave pública instalada en staging
- [ ] Clave pública instalada en production
- [ ] GitHub Environment `staging` creado
- [ ] GitHub Environment `production` creado
- [ ] Secrets agregados a ambos environments
- [ ] Required reviewers configurados en production
- [ ] `.env.staging` en `/var/www/app/shared/`
- [ ] `.env.production` en `/var/www/app/shared/`
- [ ] SSH conectividad verificada
- [ ] Permisos de directorios correctos (755/600)

## Paso 9: Test del Pipeline

### Test 1: Push a main

```bash
git checkout -b test-deploy
echo "test" >> README.md
git add .
git commit -m "Test deploy"
git push origin test-deploy
```

### Test 2: Create Pull Request y Merge

- Ir a GitHub → Pull Requests
- Crear PR contra main
- Esperar CI exitoso
- Merge a main

### Test 3: Verificar Deployments

- Ir a GitHub → Actions
- Ver workflow `Deploy to Staging` ejecutándose
- Verificar logs en tiempo real
- Una vez exitoso, podrás disparar `Deploy to Production` manualmente

## Troubleshooting

### Error: "Host key verification failed"

**Solución:**
```bash
# En servidor, verificar que está en known_hosts
ssh -o StrictHostKeyChecking=accept-new deploy@staging.example.com "exit"

# O agregar manualmente
ssh-keyscan staging.example.com >> ~/.ssh/known_hosts
```

### Error: "Permission denied (publickey)"

**Solución:**
1. Verificar que la clave pública está en `~/.ssh/authorized_keys`
2. Verificar permisos: `chmod 600 ~/.ssh/authorized_keys`
3. Verificar permisos de `~/.ssh`: `chmod 700 ~/.ssh`
4. Reiniciar SSH: `sudo systemctl restart ssh`

### Error: "Cannot connect to host"

**Solución:**
1. Verificar que el host es accesible: `ping staging.example.com`
2. Verificar que el puerto SSH es 22 (o el correcto)
3. Verificar firewall permite SSH: `sudo ufw allow 22/tcp`

## Recursos Adicionales

- [GitHub Environments](https://docs.github.com/actions/deployment/targeting-different-environments)
- [SSH Ed25519 Keys](https://wiki.archlinux.org/title/SSH_keys)
- [GitHub Actions Secrets](https://docs.github.com/actions/security-guides/encrypted-secrets)
