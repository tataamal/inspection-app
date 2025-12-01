<script setup lang="ts">
import { watch } from 'vue';

type ModalSize = 'sm' | 'md' | 'lg' | 'xl';

const props = withDefaults(defineProps<{
    show?: boolean;
    title?: string;
    closeOnBackdrop?: boolean;
    size?: ModalSize;
}>(), {
    show: false,
    title: '',
    closeOnBackdrop: true,
    size: 'md'
});

const emit = defineEmits<{
    'update:show': [value: boolean];
    close: [];
}>();

const sizeClasses: Record<ModalSize, string> = {
    sm: 'max-w-sm',
    md: 'max-w-md',
    lg: 'max-w-lg',
    xl: 'max-w-xl'
};

const close = () => {
    emit('update:show', false);
    emit('close');
};

const handleBackdropClick = (event: MouseEvent) => {
    if (props.closeOnBackdrop && event.target === event.currentTarget) {
        close();
    }
};

// Prevent body scroll when modal is open
watch(() => props.show, (isOpen) => {
    if (isOpen) {
        document.body.style.overflow = 'hidden';
    } else {
        document.body.style.overflow = '';
    }
});
</script>

<template>
    <Teleport to="body">
        <Transition name="modal">
            <div
                v-if="show"
                class="fixed inset-0 z-[100] bg-slate-900/80 backdrop-blur-sm flex items-center justify-center p-4"
                @click="handleBackdropClick"
            >
                <div
                    :class="[
                        'bg-slate-800/90 backdrop-blur-xl border border-white/10 rounded-3xl w-full overflow-hidden animate-fade-in-up',
                        sizeClasses[size]
                    ]"
                    @click.stop
                >
                    <!-- Modal Header -->
                    <div
                        v-if="title || $slots.header"
                        class="flex items-center justify-between p-6 border-b border-white/10"
                    >
                        <h3 v-if="title" class="text-xl font-bold text-white">
                            {{ title }}
                        </h3>
                        <slot name="header" />
                        <button
                            @click="close"
                            class="w-8 h-8 rounded-lg bg-white/5 flex items-center justify-center text-slate-400 hover:bg-white/10 hover:text-white transition-all"
                            aria-label="Close modal"
                        >
                            <i class="fa-solid fa-times"></i>
                        </button>
                    </div>

                    <!-- Modal Body -->
                    <div class="p-6 text-slate-300">
                        <slot />
                    </div>

                    <!-- Modal Footer -->
                    <div
                        v-if="$slots.footer"
                        class="flex items-center justify-end gap-3 p-6 border-t border-white/10"
                    >
                        <slot name="footer" />
                    </div>
                </div>
            </div>
        </Transition>
    </Teleport>
</template>

<style scoped>
.modal-enter-active,
.modal-leave-active {
    transition: opacity 0.3s ease;
}

.modal-enter-from,
.modal-leave-to {
    opacity: 0;
}

@keyframes fadeInUp {
    from {
        opacity: 0;
        transform: translateY(20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.animate-fade-in-up {
    animation: fadeInUp 0.3s ease-out;
}
</style>

