# 🧪 Guía de Testing con Vitest para Componentes Vue

Este proyecto utiliza **Vitest** para tests unitarios de componentes Vue 3, con soporte para cobertura de código.

## Configuración

- **Test Runner**: Vitest
- **Componentes**: Vue 3 Test Utils
- **Cobertura**: v8 (80% target)
- **Archivos de test**: `resources/js/__tests__/**/*.spec.js`

## Comandos disponibles

### Ejecutar tests (una sola vez)
```bash
npm run test
```

### Ejecutar tests en modo watch (desarrollo)
```bash
npm run test:watch
```

### Generar reporte de cobertura
```bash
npm run test:coverage
```

Genera reporte HTML en `coverage/` con detalles de líneas cubiertas.

## Estructura de tests

### Ubicación
```
resources/js/
├── __tests__/
│   ├── setup.js          # Setup global (mocks, polyfills)
│   ├── App.spec.js       # Tests del App principal
│   ├── Alert.spec.js     # Tests de componente Alert
│   └── ...
├── components/
│   ├── shared/
│   │   ├── Alert.vue     # Componente de ejemplo
│   │   └── ...
│   └── ...
└── ...
```

### Ejemplo: Test de componente simple

```javascript
import { describe, it, expect, beforeEach } from 'vitest';
import { mount } from '@vue/test-utils';
import MyComponent from '../components/MyComponent.vue';

describe('MyComponent', () => {
    let wrapper;

    beforeEach(() => {
        wrapper = mount(MyComponent, {
            props: { title: 'Test' },
        });
    });

    it('renders the title prop', () => {
        expect(wrapper.text()).toContain('Test');
    });

    it('emits event on button click', async () => {
        await wrapper.find('button').trigger('click');
        expect(wrapper.emitted('clicked')).toBeTruthy();
    });
});
```

## Patrones comunes de testing

### Testing Props
```javascript
it('accepts and renders props', () => {
    const wrapper = mount(MyComponent, {
        props: {
            title: 'Hello',
            disabled: true,
        },
    });
    expect(wrapper.props('title')).toBe('Hello');
});
```

### Testing Events
```javascript
it('emits event with payload', async () => {
    const wrapper = mount(MyComponent);
    await wrapper.find('button').trigger('click');
    expect(wrapper.emitted('save')[0]).toEqual([{ id: 1 }]);
});
```

### Testing Slots
```javascript
it('renders slot content', () => {
    const wrapper = mount(MyComponent, {
        slots: {
            default: '<span>Slot content</span>',
        },
    });
    expect(wrapper.text()).toContain('Slot content');
});
```

### Testing Computed Properties & Methods
```javascript
it('computes total correctly', () => {
    const wrapper = mount(MyComponent, {
        props: { items: [{ price: 10 }, { price: 20 }] },
    });
    expect(wrapper.vm.total).toBe(30);
});

it('calls method on interaction', async () => {
    const wrapper = mount(MyComponent);
    await wrapper.vm.fetchData();
    expect(wrapper.vm.data).toBeDefined();
});
```

## Aliases y rutas

Usa el alias `@` para importes limpios:
```javascript
// ✅ Bueno
import Alert from '@/components/shared/Alert.vue';

// ❌ Evitar
import Alert from '../../../components/shared/Alert.vue';
```

## Mocks y Stubs

El archivo `setup.js` contiene:
- Mock de `window.matchMedia`
- Mock de `localStorage` y `sessionStorage`
- Suppressión de console warnings en tests

Usa en tests:
```javascript
import { vi } from 'vitest';

// Mock una función
const mockFetch = vi.fn().mockResolvedValue({ data: [] });

// Mock un módulo
vi.mock('@/services/api', () => ({
    fetchData: vi.fn(),
}));
```

## CI/CD Integration

En pipelines, ejecuta:
```bash
npm run test                 # Tests
npm run test:coverage       # Con reporte
npm run lint                # ESLint (ya configurado)
```

## Targets de cobertura

| Métrica    | Target |
|-----------|--------|
| Líneas    | 80%    |
| Funciones | 80%    |
| Ramas     | 80%    |
| Statements| 80%    |

Aumenta los targets en `vitest.config.js` según necesidad.

## Recursos

- [Vitest Docs](https://vitest.dev/)
- [Vue Test Utils](https://test-utils.vuejs.org/)
- [Testing Vue 3](https://vuejs.org/guide/scaling-up/testing.html)
