# 🎨 Guía de Formateo con Laravel Pint

Este proyecto utiliza **Laravel Pint** para asegurar que todo el código siga los estándares de formateo consistentes.

## Configuración

- **Archivo de configuración**: `pint.json`
- **Preset**: Laravel (estándares PSR-12)
- **Línea máxima suave**: 100 caracteres

## Comandos disponibles

### Auto-formatear código
```bash
composer format
```
Formatea automáticamente todos los archivos PHP según las reglas de Pint.

### Verificar formato (sin cambios)
```bash
composer format:check
```
Verifica si el código cumple con los estándares sin hacer cambios.

### Análisis estático
```bash
composer stan
```
Ejecuta PHPStan para análisis estático del código.

### Control de calidad completo
```bash
composer quality
```
Ejecuta verificación de formato + análisis estático + pruebas unitarias.

## Pre-commit Hook

Se ha configurado un **pre-commit hook** automático que:
- ✅ Ejecuta Pint antes de cada commit
- ❌ Rechaza commits con código mal formateado
- 💡 Sugiere ejecutar `composer format` si es necesario

### Bypass (solo si es necesario)
```bash
git commit --no-verify
```

## Reglas principales

| Regla | Valor |
|-------|-------|
| Comillas simples | ✅ Habilitado |
| Imports no usados | ✅ Removidos |
| Espacios dentro de paréntesis | ❌ Deshabilitado |
| Comas finales en multiline | ✅ Habilitado |
| Líneas en blanco extras | ❌ Removidas |

## Integración CI/CD

En pipelines de CI/CD, añade el siguiente paso:
```bash
composer format:check
```

Esto fallará si el código no está correctamente formateado.

## Archivos excluidos

Pint ignora automáticamente:
- `build/`
- `node_modules/`
- `vendor/`
- `storage/`
- `bootstrap/cache/`
- `public/build/`
