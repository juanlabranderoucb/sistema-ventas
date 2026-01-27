import { describe, it, expect, beforeEach } from 'vitest';
import { mount } from '@vue/test-utils';
import Alert from '@/components/shared/Alert.vue';

describe('Alert Component', () => {
    let wrapper;

    beforeEach(() => {
        wrapper = mount(Alert, {
            props: {
                message: 'Test alert message',
                type: 'info',
                title: 'Alert Title',
            },
        });
    });

    it('renders alert component correctly', () => {
        expect(wrapper.find('.alert-component').exists()).toBe(true);
    });

    it('displays the message prop', () => {
        expect(wrapper.text()).toContain('Test alert message');
    });

    it('displays the title when provided', () => {
        expect(wrapper.text()).toContain('Alert Title');
    });

    it('applies correct alert type class', () => {
        expect(wrapper.find('.alert-info').exists()).toBe(true);
    });

    it('emits dismissed event when close button is clicked', async () => {
        await wrapper.find('.btn-close').trigger('click');
        expect(wrapper.emitted()).toHaveProperty('dismissed');
    });

    it('renders different alert types', async () => {
        const types = ['success', 'danger', 'warning', 'info'];

        for (const type of types) {
            const alertWrapper = mount(Alert, {
                props: {
                    message: 'Test',
                    type,
                },
            });
            expect(alertWrapper.find(`.alert-${type}`).exists()).toBe(true);
        }
    });

    it('does not show title when not provided', async () => {
        const alertWrapper = mount(Alert, {
            props: {
                message: 'Test message',
                type: 'info',
            },
        });
        expect(alertWrapper.find('strong').exists()).toBe(false);
    });

    it('renders close button with accessibility attributes', () => {
        const closeButton = wrapper.find('.btn-close');
        expect(closeButton.attributes('aria-label')).toBe('Close alert');
    });
});
