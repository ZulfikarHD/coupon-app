import { ref, onMounted, onUnmounted, type Ref } from 'vue';
import { router } from '@inertiajs/vue3';

/**
 * iOS-style haptic feedback patterns
 */
type HapticPattern = 'light' | 'medium' | 'heavy' | 'success' | 'warning' | 'error';

const hapticPatterns: Record<HapticPattern, number | number[]> = {
    light: 10,
    medium: 20,
    heavy: 30,
    success: [10, 50, 10],
    warning: [20, 50, 20, 50, 20],
    error: [30, 100, 30],
};

/**
 * Trigger haptic feedback on supported devices
 */
export function useHaptic() {
    const isSupported = ref(false);

    onMounted(() => {
        isSupported.value = 'vibrate' in navigator;
    });

    const trigger = (pattern: HapticPattern = 'light') => {
        if (!isSupported.value) return;

        try {
            navigator.vibrate(hapticPatterns[pattern]);
        } catch {
            // Silently fail if vibration is not available
        }
    };

    const triggerOnClick = (pattern: HapticPattern = 'light') => {
        return () => trigger(pattern);
    };

    return {
        isSupported,
        trigger,
        triggerOnClick,
    };
}

/**
 * Swipe gesture detection for iOS-like navigation
 */
interface SwipeOptions {
    threshold?: number;
    onSwipeLeft?: () => void;
    onSwipeRight?: () => void;
    onSwipeUp?: () => void;
    onSwipeDown?: () => void;
}

export function useSwipeGesture(
    elementRef: Ref<HTMLElement | null>,
    options: SwipeOptions = {}
) {
    const { threshold = 50, onSwipeLeft, onSwipeRight, onSwipeUp, onSwipeDown } = options;

    const touchStart = ref({ x: 0, y: 0 });
    const touchEnd = ref({ x: 0, y: 0 });
    const isSwiping = ref(false);
    const swipeDirection = ref<'left' | 'right' | 'up' | 'down' | null>(null);

    const handleTouchStart = (e: TouchEvent) => {
        touchStart.value = {
            x: e.touches[0].clientX,
            y: e.touches[0].clientY,
        };
        isSwiping.value = true;
    };

    const handleTouchMove = (e: TouchEvent) => {
        if (!isSwiping.value) return;

        touchEnd.value = {
            x: e.touches[0].clientX,
            y: e.touches[0].clientY,
        };

        const diffX = touchEnd.value.x - touchStart.value.x;
        const diffY = touchEnd.value.y - touchStart.value.y;

        if (Math.abs(diffX) > Math.abs(diffY)) {
            swipeDirection.value = diffX > 0 ? 'right' : 'left';
        } else {
            swipeDirection.value = diffY > 0 ? 'down' : 'up';
        }
    };

    const handleTouchEnd = () => {
        if (!isSwiping.value) return;

        const diffX = touchEnd.value.x - touchStart.value.x;
        const diffY = touchEnd.value.y - touchStart.value.y;

        if (Math.abs(diffX) > threshold && Math.abs(diffX) > Math.abs(diffY)) {
            if (diffX > 0 && onSwipeRight) {
                onSwipeRight();
            } else if (diffX < 0 && onSwipeLeft) {
                onSwipeLeft();
            }
        } else if (Math.abs(diffY) > threshold && Math.abs(diffY) > Math.abs(diffX)) {
            if (diffY > 0 && onSwipeDown) {
                onSwipeDown();
            } else if (diffY < 0 && onSwipeUp) {
                onSwipeUp();
            }
        }

        isSwiping.value = false;
        swipeDirection.value = null;
    };

    onMounted(() => {
        const element = elementRef.value;
        if (!element) return;

        element.addEventListener('touchstart', handleTouchStart, { passive: true });
        element.addEventListener('touchmove', handleTouchMove, { passive: true });
        element.addEventListener('touchend', handleTouchEnd, { passive: true });
    });

    onUnmounted(() => {
        const element = elementRef.value;
        if (!element) return;

        element.removeEventListener('touchstart', handleTouchStart);
        element.removeEventListener('touchmove', handleTouchMove);
        element.removeEventListener('touchend', handleTouchEnd);
    });

    return {
        isSwiping,
        swipeDirection,
    };
}

/**
 * iOS-style swipe back navigation
 */
export function useSwipeBack(elementRef: Ref<HTMLElement | null>) {
    const { trigger } = useHaptic();

    return useSwipeGesture(elementRef, {
        threshold: 80,
        onSwipeRight: () => {
            trigger('light');
            // Go back in history
            if (window.history.length > 1) {
                router.visit(document.referrer || '/', {
                    preserveState: false,
                });
            }
        },
    });
}

/**
 * Pull to refresh functionality
 */
interface PullToRefreshOptions {
    threshold?: number;
    onRefresh: () => Promise<void>;
}

export function usePullToRefresh(
    elementRef: Ref<HTMLElement | null>,
    options: PullToRefreshOptions
) {
    const { threshold = 80, onRefresh } = options;
    const { trigger } = useHaptic();

    const isPulling = ref(false);
    const isRefreshing = ref(false);
    const pullDistance = ref(0);
    const startY = ref(0);

    const handleTouchStart = (e: TouchEvent) => {
        if (elementRef.value?.scrollTop === 0) {
            startY.value = e.touches[0].clientY;
            isPulling.value = true;
        }
    };

    const handleTouchMove = (e: TouchEvent) => {
        if (!isPulling.value || isRefreshing.value) return;

        const currentY = e.touches[0].clientY;
        const diff = currentY - startY.value;

        if (diff > 0) {
            pullDistance.value = Math.min(diff * 0.5, threshold * 1.5);

            if (pullDistance.value >= threshold) {
                trigger('medium');
            }
        }
    };

    const handleTouchEnd = async () => {
        if (!isPulling.value) return;

        if (pullDistance.value >= threshold && !isRefreshing.value) {
            isRefreshing.value = true;
            trigger('success');

            try {
                await onRefresh();
            } finally {
                isRefreshing.value = false;
            }
        }

        isPulling.value = false;
        pullDistance.value = 0;
    };

    onMounted(() => {
        const element = elementRef.value;
        if (!element) return;

        element.addEventListener('touchstart', handleTouchStart, { passive: true });
        element.addEventListener('touchmove', handleTouchMove, { passive: false });
        element.addEventListener('touchend', handleTouchEnd, { passive: true });
    });

    onUnmounted(() => {
        const element = elementRef.value;
        if (!element) return;

        element.removeEventListener('touchstart', handleTouchStart);
        element.removeEventListener('touchmove', handleTouchMove);
        element.removeEventListener('touchend', handleTouchEnd);
    });

    return {
        isPulling,
        isRefreshing,
        pullDistance,
        pullProgress: () => Math.min(pullDistance.value / threshold, 1),
    };
}

/**
 * Scroll-triggered spring animations with IntersectionObserver
 */
interface ScrollSpringOptions {
    /** Root margin for IntersectionObserver */
    rootMargin?: string;
    /** Threshold for triggering animation */
    threshold?: number;
    /** Stagger delay between items (ms) */
    staggerDelay?: number;
    /** Animation class to add */
    animationClass?: string;
    /** Whether to animate only once */
    once?: boolean;
}

export function useScrollSpring(options: ScrollSpringOptions = {}) {
    const {
        rootMargin = '0px 0px -50px 0px',
        threshold = 0.1,
        staggerDelay = 50,
        animationClass = 'animate-spring-up',
        once = true,
    } = options;

    const observedElements = ref<Set<Element>>(new Set());
    let observer: IntersectionObserver | null = null;

    const initObserver = () => {
        if (typeof window === 'undefined') return;

        observer = new IntersectionObserver(
            (entries) => {
                entries.forEach((entry, index) => {
                    if (entry.isIntersecting) {
                        const element = entry.target as HTMLElement;
                        
                        // Calculate stagger delay based on element index in viewport
                        const visibleElements = Array.from(observedElements.value).filter(
                            (el) => el.getBoundingClientRect().top < window.innerHeight
                        );
                        const elementIndex = visibleElements.indexOf(element);
                        const delay = elementIndex * staggerDelay;

                        // Apply animation with stagger
                        setTimeout(() => {
                            element.style.animationDelay = `${delay}ms`;
                            element.classList.remove('scroll-spring-initial');
                            element.classList.add(animationClass);
                        }, 0);

                        // Unobserve if once is true
                        if (once && observer) {
                            observer.unobserve(element);
                            observedElements.value.delete(element);
                        }
                    } else if (!once) {
                        // Reset animation when out of view
                        const element = entry.target as HTMLElement;
                        element.classList.add('scroll-spring-initial');
                        element.classList.remove(animationClass);
                    }
                });
            },
            {
                rootMargin,
                threshold,
            }
        );
    };

    const observe = (element: Element | null) => {
        if (!element) return;
        
        if (!observer) {
            initObserver();
        }

        if (observer && !observedElements.value.has(element)) {
            // Set initial state
            (element as HTMLElement).classList.add('scroll-spring-initial');
            observer.observe(element);
            observedElements.value.add(element);
        }
    };

    const observeAll = (elements: NodeListOf<Element> | Element[]) => {
        elements.forEach((element) => observe(element));
    };

    const unobserve = (element: Element | null) => {
        if (!element || !observer) return;
        observer.unobserve(element);
        observedElements.value.delete(element);
    };

    const disconnect = () => {
        if (observer) {
            observer.disconnect();
            observedElements.value.clear();
        }
    };

    onUnmounted(() => {
        disconnect();
    });

    return {
        observe,
        observeAll,
        unobserve,
        disconnect,
    };
}

/**
 * Vue directive for scroll-triggered spring animations
 * Usage: v-scroll-spring or v-scroll-spring="{ stagger: 50 }"
 */
export const vScrollSpring = {
    mounted(el: HTMLElement, binding: { value?: { stagger?: number; class?: string } }) {
        const stagger = binding.value?.stagger ?? 50;
        const animClass = binding.value?.class ?? 'animate-spring-up';

        el.classList.add('scroll-spring-initial');

        const observer = new IntersectionObserver(
            (entries) => {
                entries.forEach((entry) => {
                    if (entry.isIntersecting) {
                        // Get index from data attribute for stagger
                        const index = parseInt(el.dataset.scrollIndex || '0', 10);
                        const delay = index * stagger;

                        setTimeout(() => {
                            el.style.animationDelay = `${delay}ms`;
                            el.classList.remove('scroll-spring-initial');
                            el.classList.add(animClass);
                        }, 0);

                        observer.unobserve(el);
                    }
                });
            },
            {
                rootMargin: '0px 0px -30px 0px',
                threshold: 0.1,
            }
        );

        observer.observe(el);

        // Store observer for cleanup
        (el as any)._scrollSpringObserver = observer;
    },
    unmounted(el: HTMLElement) {
        const observer = (el as any)._scrollSpringObserver;
        if (observer) {
            observer.disconnect();
        }
    },
};

export default useHaptic;

