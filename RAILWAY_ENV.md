# RAILWAY DEPLOYMENT GUIDE

## Required Environment Variables in Railway

Configure these in your Railway project settings:

### Essential
```bash
APP_NAME="Sistema Ventas"
APP_ENV=production
APP_KEY=base64:YOUR_KEY_HERE  # Generate with: php artisan key:generate --show
APP_DEBUG=false
APP_URL=https://your-app.up.railway.app

# Database (Railway provides these automatically when you add PostgreSQL)
DB_CONNECTION=pgsql
DB_HOST=${PGHOST}
DB_PORT=${PGPORT}
DB_DATABASE=${PGDATABASE}
DB_USERNAME=${PGUSER}
DB_PASSWORD=${PGPASSWORD}

# Cache/Session (Railway provides these automatically when you add Redis)
CACHE_DRIVER=redis
SESSION_DRIVER=redis
REDIS_HOST=${REDIS_HOST}
REDIS_PASSWORD=${REDIS_PASSWORD}
REDIS_PORT=${REDIS_PORT}

# Queue
QUEUE_CONNECTION=redis

# Logging
LOG_CHANNEL=stderr
LOG_LEVEL=error
```

### Optional Release Info (set by CI/CD)
```bash
APP_RELEASE_VERSION=
APP_RELEASE_COMMIT=
APP_RELEASE_BRANCH=
APP_RELEASE_TIMESTAMP=
```

## Setup Steps

1. Create Railway project
2. Add PostgreSQL database service
3. Add Redis service
4. Configure environment variables above
5. Deploy from GitHub

## Troubleshooting

If health check fails:
- Check Railway logs
- Verify all environment variables are set
- Ensure PostgreSQL and Redis are running
- Check `/api/health` endpoint manually
