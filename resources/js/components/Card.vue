<script setup lang="ts">
import { computed } from 'vue';

type CardVariant = 'standard' | 'interactive' | 'stat';
type CardPadding = 'sm' | 'md' | 'lg';

const props = withDefaults(defineProps<{
    variant?: CardVariant;
    clickable?: boolean;
    hover?: boolean;
    padding?: CardPadding;
}>(), {
    variant: 'standard',
    clickable: false,
    hover: true,
    padding: 'md'
});

const emit = defineEmits<{
    click: [];
}>();

const cardClasses = computed(() => {
    const base = 'bg-white/5 backdrop-blur-md border border-white/10 rounded-2xl transition-all duration-300';
    
    // Padding variants
    const paddings: Record<CardPadding, string> = {
        sm: 'p-4',
        md: 'p-6',
        lg: 'p-8'
    };
    
    // Variant specific styles
    let variantStyles = '';
    const variant = props.variant || 'standard';
    const padding = props.padding || 'md';
    
    if (variant === 'interactive' || props.clickable) {
        variantStyles = 'cursor-pointer overflow-hidden relative';
        
        if (props.hover) {
            variantStyles += ' hover:-translate-y-1 hover:bg-white/10 hover:border-emerald-500/30 hover:shadow-[0_20px_40px_rgba(16,185,129,0.15)]';
        }
        
        // Top border gradient on hover
        variantStyles += ' before:absolute before:top-0 before:left-0 before:right-0 before:h-[3px] before:bg-gradient-to-r before:from-emerald-500 before:to-emerald-300 before:scale-x-0 before:origin-left hover:before:scale-x-100 before:transition-transform before:duration-500';
    }
    
    if (variant === 'stat') {
        variantStyles = 'flex items-center gap-4 hover:-translate-y-1 hover:bg-white/10 hover:border-emerald-500/30 hover:shadow-[0_10px_30px_rgba(16,185,129,0.1)]';
    }
    
    return `${base} ${paddings[padding]} ${variantStyles}`;
});

const handleClick = () => {
    if (props.clickable || props.variant === 'interactive') {
        emit('click');
    }
};
</script>

<template>
    <div
        :class="cardClasses"
        @click="handleClick"
    >
        <slot />
    </div>
</template>

