<script setup lang="ts">
import { ref, onMounted, onUnmounted, computed } from 'vue';
import { router } from '@inertiajs/vue3';
import { useHaptic } from '@/composables/useHaptic';
import { Loader2, ArrowDown } from 'lucide-vue-next';

interface Props {
    /** Pull distance threshold to trigger refresh */
    threshold?: number;
    /** Maximum pull distance */
    maxPull?: number;
    /** Custom refresh handler (defaults to router.reload) */
    onRefresh?: () => Promise<void>;
    /** Whether to disable pull to refresh */
    disabled?: boolean;
}

const props = withDefaults(defineProps<Props>(), {
    threshold: 80,
    maxPull: 120,
    disabled: false,
});

const { trigger } = useHaptic();

const containerRef = ref<HTMLElement | null>(null);
const pullDistance = ref(0);
const isPulling = ref(false);
const isRefreshing = ref(false);
const hasTriggeredHaptic = ref(false);

let startY = 0;
let currentScrollTop = 0;

const pullProgress = computed(() => Math.min(pullDistance.value / props.threshold, 1));

const indicatorStyle = computed(() => ({
    transform: `translateY(${Math.min(pullDistance.value, props.maxPull) - 60}px) rotate(${pullProgress.value * 180}deg)`,
    opacity: pullProgress.value,
}));

const canPull = computed(() => {
    if (props.disabled || isRefreshing.value) return false;
    // Check if we're at the top of the scroll container
    return currentScrollTop <= 0;
});

const handleTouchStart = (e: TouchEvent) => {
    if (props.disabled || isRefreshing.value) return;
    
    // Get current scroll position
    const scrollContainer = containerRef.value?.querySelector('[data-pull-scroll]') || containerRef.value;
    currentScrollTop = scrollContainer?.scrollTop || 0;
    
    if (currentScrollTop <= 0) {
        startY = e.touches[0].clientY;
        isPulling.value = true;
        hasTriggeredHaptic.value = false;
    }
};

const handleTouchMove = (e: TouchEvent) => {
    if (!isPulling.value || isRefreshing.value || props.disabled) return;
    
    // Re-check scroll position during move
    const scrollContainer = containerRef.value?.querySelector('[data-pull-scroll]') || containerRef.value;
    currentScrollTop = scrollContainer?.scrollTop || 0;
    
    if (currentScrollTop > 0) {
        pullDistance.value = 0;
        return;
    }
    
    const currentY = e.touches[0].clientY;
    const diff = currentY - startY;
    
    if (diff > 0) {
        // Apply resistance for natural feel
        const resistance = 0.5;
        pullDistance.value = Math.min(diff * resistance, props.maxPull);
        
        // Haptic feedback when reaching threshold
        if (pullDistance.value >= props.threshold && !hasTriggeredHaptic.value) {
            trigger('medium');
            hasTriggeredHaptic.value = true;
        }
        
        // Prevent default scroll when pulling
        if (pullDistance.value > 10) {
            e.preventDefault();
        }
    }
};

const handleTouchEnd = async () => {
    if (!isPulling.value || isRefreshing.value) return;
    
    isPulling.value = false;
    
    if (pullDistance.value >= props.threshold) {
        isRefreshing.value = true;
        trigger('success');
        
        // Keep indicator visible during refresh
        pullDistance.value = 60;
        
        try {
            if (props.onRefresh) {
                await props.onRefresh();
            } else {
                // Default: use Inertia router to reload
                await new Promise<void>((resolve) => {
                    router.reload({
                        onFinish: () => resolve(),
                    });
                });
            }
        } finally {
            // Animate out
            pullDistance.value = 0;
            isRefreshing.value = false;
        }
    } else {
        // Snap back with spring animation
        pullDistance.value = 0;
    }
};

// Handle scroll to track position
const handleScroll = (e: Event) => {
    const target = e.target as HTMLElement;
    currentScrollTop = target.scrollTop;
};

onMounted(() => {
    const scrollContainer = containerRef.value?.querySelector('[data-pull-scroll]') || containerRef.value;
    scrollContainer?.addEventListener('scroll', handleScroll, { passive: true });
});

onUnmounted(() => {
    const scrollContainer = containerRef.value?.querySelector('[data-pull-scroll]') || containerRef.value;
    scrollContainer?.removeEventListener('scroll', handleScroll);
});
</script>

<template>
    <div
        ref="containerRef"
        class="pull-to-refresh relative"
        @touchstart.passive="handleTouchStart"
        @touchmove="handleTouchMove"
        @touchend="handleTouchEnd"
        @touchcancel="handleTouchEnd"
    >
        <!-- Pull Indicator -->
        <div
            class="pointer-events-none absolute left-1/2 top-0 z-50 flex h-14 w-14 -translate-x-1/2 items-center justify-center"
            :style="indicatorStyle"
        >
            <div
                :class="[
                    'flex h-10 w-10 items-center justify-center rounded-full',
                    'bg-primary text-primary-foreground shadow-lg',
                    'transition-all duration-300',
                    isRefreshing ? 'scale-100' : 'scale-90',
                ]"
            >
                <Loader2 v-if="isRefreshing" class="h-5 w-5 animate-spin" />
                <ArrowDown v-else class="h-5 w-5" :class="{ 'rotate-180': pullProgress >= 1 }" />
            </div>
        </div>
        
        <!-- Content -->
        <div
            :style="{
                transform: `translateY(${Math.min(pullDistance, maxPull)}px)`,
                transition: isPulling ? 'none' : 'transform 0.4s cubic-bezier(0.34, 1.56, 0.64, 1)',
            }"
        >
            <slot />
        </div>
    </div>
</template>

<style scoped>
.pull-to-refresh {
    touch-action: pan-y pinch-zoom;
    overflow: visible;
}
</style>


