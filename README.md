# Sistema de Ventas e Inventario

Sistema web integral para la gestión de ventas, compras, inventario, clientes y proveedores. Desarrollado con **Laravel 11** (API Backend) y **Vue 3** (Frontend), utilizando **PostgreSQL** como base de datos.

## 🚀 Características Principales

-   **Gestión de Productos**: Control de inventario, categorías, alertas de stock bajo y manejo de imágenes.
-   **Ventas**: Punto de venta (POS) con cálculo automático de totales, impuestos y descuentos. Soporte para ventas al contado y crédito.
-   **Compras**: Registro de compras a proveedores para reabastecimiento de stock.
-   **Clientes y Proveedores**: Gestión completa de terceros con historial de crédito.
-   **Seguridad (RBAC)**: Sistema de Roles y Permisos para controlar el acceso a los módulos.
-   **Reportes**: Generación de reportes en PDF para auditoría y control financiero.
-   **API RESTful**: Arquitectura moderna API-First con separación clara entre Backend y Frontend.

## 🛠️ Tecnologías

-   **Backend**: PHP 8.2+, Laravel 11.
-   **Frontend**: Vue.js 3, Bootstrap 5, Axios, SweetAlert2.
-   **Base de Datos**: PostgreSQL 16+.
-   **Herramientas**: Vite, Composer, NPM.

## ✅ Testing & Quality Gates

Este proyecto implementa múltiples capas de control de calidad:

### 🧪 Tests de Backend (PHPUnit)
```bash
php artisan test
```

### 🧪 Tests de Frontend (Vitest)
```bash
npm run test              # Ejecutar tests una vez
npm run test:watch        # Modo watch para desarrollo
npm run test:coverage     # Con reporte de cobertura
```

### 🎨 Formateo de Código
```bash
# PHP (Laravel Pint)
composer format       # Auto-formatear
composer format:check # Solo verificar

# JavaScript (ESLint)
npm run lint          # Verificar
npm run lint:fix      # Auto-corregir
```

### 🛡️ Análisis Estático
```bash
composer stan          # PHPStan análisis
composer stan:baseline # Generar baseline
```

### ✅ Control de Calidad Completo
```bash
composer quality       # PHP: format + stan + tests
```

### 🚫 Pre-commit Hook

El proyecto incluye un **pre-commit hook** automático que ejecuta:
1. Laravel Pint (formateo PHP)
2. PHPStan (análisis estático)
3. ESLint (linting Vue)
4. Vitest (tests de componentes)

Para bypassearlo temporalmente: `git commit --no-verify`

### 📊 Cobertura de Tests

Los reportes de cobertura se generan en:
- Backend: `coverage/` (PHPUnit)
- Frontend: `coverage/` (Vitest)

Consulta guías detalladas:
- [PINT_GUIDE.md](PINT_GUIDE.md) - Formateo PHP
- [VITEST_GUIDE.md](VITEST_GUIDE.md) - Tests de componentes Vue

## 🔄 Integración Continua (CI/CD)

Este proyecto está preparado para CI/CD. Pipeline recomendado:

### Backend (PHP)
1. `composer install --no-dev --optimize-autoloader`
2. `composer format:check` - Validar formato
3. `composer stan` - Análisis estático
4. `php artisan test` - Tests unitarios

### Frontend (JavaScript)
1. `npm ci` - Instalación limpia
2. `npm run lint` - Validar código
3. `npm run test` - Tests de componentes
4. `npm run build` - Build producción

Consulta [CI_CD_TUTORIAL.md](CI_CD_TUTORIAL.md) para configuración detallada.

Variables de entorno necesarias en CI:
- `DB_CONNECTION`, `DB_HOST`, `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD`
- `APP_KEY`, `APP_ENV=testing`

## 📋 Requisitos Previos

-   PHP >= 8.2
-   Composer
-   Node.js & NPM
-   Servidor PostgreSQL

## 🔧 Instalación

1. **Clonar el repositorio**

    ```bash
    git clone https://github.com/tu-usuario/sistema-venta.git
    cd sistema-venta
    ```

2. **Instalar dependencias de PHP**

    ```bash
    composer install
    ```

3. **Instalar dependencias de JavaScript**

    ```bash
    npm install
    ```

4. **Configurar entorno**

    ```bash
    cp .env.example .env
    php artisan key:generate
    ```

    _Configura tus credenciales de base de datos en el archivo `.env` (DB_HOST, DB_PORT, DB_DATABASE, DB_USERNAME, DB_PASSWORD)._

5. **Migración y Seeders**
   Este comando creará las tablas y poblará la base de datos con usuarios y datos iniciales.

    ```bash
    php artisan migrate --seed
    ```

6. **Crear enlace simbólico para imágenes**

    ```bash
    php artisan storage:link
    ```

7. **Compilación de Assets**
    ```bash
    npm run build
    ```
    _Para desarrollo:_ `npm run dev`

## 🏁 Ejecución

Para iniciar el servidor local de desarrollo:

```bash
php artisan serve
```

El sistema estará disponible en `http://localhost:8000`.

## 📄 Licencia

Este proyecto está bajo la licencia [MIT](LICENSE).
