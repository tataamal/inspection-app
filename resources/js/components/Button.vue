<script setup lang="ts">
import { computed } from 'vue';

type ButtonVariant = 'primary' | 'secondary' | 'destructive' | 'icon';
type ButtonSize = 'sm' | 'md' | 'lg';

const props = withDefaults(defineProps<{
    variant?: ButtonVariant;
    icon?: string;
    label?: string;
    loadingText?: string;
    processing?: boolean;
    disabled?: boolean;
    size?: ButtonSize;
    fullWidth?: boolean;
}>(), {
    variant: 'primary',
    loadingText: 'Processing...',
    processing: false,
    disabled: false,
    size: 'md',
    fullWidth: false
});

const emit = defineEmits<{
    click: [];
}>();

const buttonClasses = computed(() => {
    const base = 'rounded-xl font-bold transition-all duration-300 flex items-center justify-center gap-2';
    
    // Size variants
    const sizes: Record<ButtonSize, string> = {
        sm: 'px-4 py-2 text-xs',
        md: 'px-6 py-3 text-sm',
        lg: 'px-8 py-4 text-base'
    };
    
    // Variant styles
    const variants: Record<ButtonVariant, string> = {
        primary: 'bg-gradient-to-r from-emerald-500 to-emerald-600 text-white hover:-translate-y-0.5 hover:shadow-[0_8px_20px_rgba(16,185,129,0.4)] active:translate-y-0 disabled:opacity-50 disabled:cursor-not-allowed disabled:hover:translate-y-0',
        secondary: 'bg-white/5 border border-white/10 text-white backdrop-blur-md hover:bg-white/10 hover:border-emerald-500/30 hover:-translate-y-0.5 active:translate-y-0 disabled:opacity-50 disabled:cursor-not-allowed',
        destructive: 'bg-red-500/10 border border-red-500/20 text-red-500 hover:bg-red-500/20 hover:-translate-y-0.5 hover:shadow-[0_4px_12px_rgba(239,68,68,0.3)] active:translate-y-0 disabled:opacity-50 disabled:cursor-not-allowed',
        icon: 'w-10 h-10 rounded-xl bg-white/5 border border-white/10 text-slate-400 hover:bg-white/10 hover:text-emerald-500 hover:border-emerald-500/30 hover:-translate-y-0.5 active:translate-y-0 disabled:opacity-50 disabled:cursor-not-allowed'
    };
    
    const width = props.fullWidth ? 'w-full' : 'max-w-max';
    const size = props.size || 'md';
    const variant = props.variant || 'primary';
    
    return `${base} ${sizes[size]} ${variants[variant]} ${width}`;
});

const handleClick = () => {
    if (!props.disabled && !props.processing) {
        emit('click');
    }
};
</script>

<template>
    <button
        :class="buttonClasses"
        :disabled="disabled || processing"
        @click="handleClick"
        :title="label"
    >
        <i 
            v-if="icon && !processing" 
            :class="icon"
        ></i>
        <i 
            v-if="processing" 
            class="fa-solid fa-circle-notch fa-spin"
        ></i>
        <span v-if="!processing && label">{{ label }}</span>
        <span v-else-if="processing">{{ loadingText }}</span>
    </button>
</template>

