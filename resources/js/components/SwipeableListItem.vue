<script setup lang="ts">
import { ref, onMounted, onUnmounted, computed } from 'vue';
import { useHaptic } from '@/composables/useHaptic';
import { Trash2 } from 'lucide-vue-next';

interface Props {
    /** Whether delete action is disabled */
    disabled?: boolean;
    /** Custom delete button width */
    deleteWidth?: number;
    /** Threshold to trigger auto-open (percentage of deleteWidth) */
    threshold?: number;
}

const props = withDefaults(defineProps<Props>(), {
    disabled: false,
    deleteWidth: 80,
    threshold: 0.5,
});

const emit = defineEmits<{
    delete: [];
}>();

const { trigger } = useHaptic();

const containerRef = ref<HTMLElement | null>(null);
const translateX = ref(0);
const isOpen = ref(false);
const isDragging = ref(false);

// Touch tracking
let startX = 0;
let startY = 0;
let currentX = 0;
let isHorizontalSwipe: boolean | null = null;

const containerStyle = computed(() => ({
    transform: `translateX(${translateX.value}px)`,
    transition: isDragging.value ? 'none' : 'transform 0.4s cubic-bezier(0.34, 1.56, 0.64, 1)',
}));

const deleteButtonStyle = computed(() => ({
    width: `${props.deleteWidth}px`,
    opacity: Math.min(Math.abs(translateX.value) / props.deleteWidth, 1),
    transform: `scale(${Math.min(Math.abs(translateX.value) / props.deleteWidth, 1)})`,
}));

const handleTouchStart = (e: TouchEvent) => {
    if (props.disabled) return;
    
    startX = e.touches[0].clientX;
    startY = e.touches[0].clientY;
    currentX = translateX.value;
    isHorizontalSwipe = null;
    isDragging.value = true;
};

const handleTouchMove = (e: TouchEvent) => {
    if (props.disabled || !isDragging.value) return;
    
    const touchX = e.touches[0].clientX;
    const touchY = e.touches[0].clientY;
    const diffX = touchX - startX;
    const diffY = touchY - startY;
    
    // Determine swipe direction on first significant movement
    if (isHorizontalSwipe === null) {
        if (Math.abs(diffX) > 10 || Math.abs(diffY) > 10) {
            isHorizontalSwipe = Math.abs(diffX) > Math.abs(diffY);
        }
        return;
    }
    
    // Only handle horizontal swipes
    if (!isHorizontalSwipe) return;
    
    // Prevent vertical scroll during horizontal swipe
    e.preventDefault();
    
    let newTranslate = currentX + diffX;
    
    // Limit swipe to left only and add resistance at edges
    if (newTranslate > 0) {
        newTranslate = newTranslate * 0.2; // Resistance when swiping right past origin
    } else if (newTranslate < -props.deleteWidth * 1.5) {
        // Add resistance past delete button
        const overflow = newTranslate + props.deleteWidth * 1.5;
        newTranslate = -props.deleteWidth * 1.5 + overflow * 0.2;
    }
    
    translateX.value = newTranslate;
    
    // Haptic feedback when crossing threshold
    if (!isOpen.value && Math.abs(newTranslate) >= props.deleteWidth * props.threshold) {
        trigger('light');
    }
};

const handleTouchEnd = () => {
    if (props.disabled) return;
    
    isDragging.value = false;
    
    // Determine final state based on position
    if (Math.abs(translateX.value) >= props.deleteWidth * props.threshold) {
        // Open delete action
        translateX.value = -props.deleteWidth;
        isOpen.value = true;
        trigger('medium');
    } else {
        // Snap back to closed
        translateX.value = 0;
        isOpen.value = false;
    }
};

const handleDelete = () => {
    trigger('heavy');
    emit('delete');
    close();
};

const close = () => {
    translateX.value = 0;
    isOpen.value = false;
};

// Close when clicking outside
const handleClickOutside = (e: MouseEvent) => {
    if (isOpen.value && containerRef.value && !containerRef.value.contains(e.target as Node)) {
        close();
    }
};

onMounted(() => {
    document.addEventListener('click', handleClickOutside);
});

onUnmounted(() => {
    document.removeEventListener('click', handleClickOutside);
});

// Expose close method
defineExpose({ close });
</script>

<template>
    <div ref="containerRef" class="swipeable-list-item relative overflow-hidden">
        <!-- Delete Action Button (behind content) -->
        <div 
            class="absolute right-0 top-0 bottom-0 flex items-center justify-center bg-destructive text-destructive-foreground"
            :style="deleteButtonStyle"
        >
            <button
                type="button"
                class="flex h-full w-full flex-col items-center justify-center gap-1 transition-transform press-effect"
                :disabled="disabled"
                @click="handleDelete"
            >
                <Trash2 class="h-5 w-5" />
                <span class="text-xs font-medium">Hapus</span>
            </button>
        </div>
        
        <!-- Main Content -->
        <div
            class="relative bg-card"
            :style="containerStyle"
            @touchstart="handleTouchStart"
            @touchmove="handleTouchMove"
            @touchend="handleTouchEnd"
            @touchcancel="handleTouchEnd"
        >
            <slot />
        </div>
    </div>
</template>

<style scoped>
.swipeable-list-item {
    touch-action: pan-y pinch-zoom;
}
</style>


