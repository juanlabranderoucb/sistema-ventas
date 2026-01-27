<template>
    <div class="alert-component">
        <button
            class="btn-close"
            @click="dismissAlert"
            aria-label="Close alert"
        >
            ×
        </button>
        <div :class="`alert alert-${type}`" role="alert">
            <strong v-if="title">{{ title }}:</strong>
            {{ message }}
        </div>
    </div>
</template>

<script>
export default {
    name: 'Alert',
    props: {
        message: {
            type: String,
            required: true,
        },
        type: {
            type: String,
            default: 'info',
            validator: (value) => ['success', 'danger', 'warning', 'info'].includes(value),
        },
        title: {
            type: String,
            default: null,
        },
    },
    emits: ['dismissed'],
    methods: {
        dismissAlert() {
            this.$emit('dismissed');
        },
    },
};
</script>

<style scoped>
.alert-component {
    position: relative;
}

.btn-close {
    position: absolute;
    top: 5px;
    right: 5px;
    background: none;
    border: none;
    font-size: 1.5rem;
    cursor: pointer;
    color: inherit;
}

.btn-close:hover {
    opacity: 0.7;
}

.alert {
    padding: 12px 20px;
    border-radius: 4px;
    margin-bottom: 0;
}

.alert-success {
    background-color: #d4edda;
    border: 1px solid #c3e6cb;
    color: #155724;
}

.alert-danger {
    background-color: #f8d7da;
    border: 1px solid #f5c6cb;
    color: #721c24;
}

.alert-warning {
    background-color: #fff3cd;
    border: 1px solid #ffeeba;
    color: #856404;
}

.alert-info {
    background-color: #d1ecf1;
    border: 1px solid #bee5eb;
    color: #0c5460;
}
</style>
