<script setup lang="ts">
const props = withDefaults(defineProps<{
    show?: boolean;
    message?: string;
    fullScreen?: boolean;
}>(), {
    show: false,
    message: 'Loading...',
    fullScreen: false
});
</script>

<template>
    <Transition name="fade">
        <div
            v-if="show"
            :class="[
                'z-20 bg-slate-900/60 backdrop-blur-sm flex flex-col items-center justify-center text-emerald-400 transition-all',
                fullScreen ? 'fixed inset-0' : 'absolute inset-0'
            ]"
        >
            <i class="fa-solid fa-circle-notch fa-spin text-3xl mb-2"></i>
            <span class="text-xs font-bold uppercase tracking-widest animate-pulse">
                {{ message }}
            </span>
        </div>
    </Transition>
</template>

<style scoped>
.fade-enter-active,
.fade-leave-active {
    transition: opacity 0.3s ease;
}

.fade-enter-from,
.fade-leave-to {
    opacity: 0;
}
</style>

