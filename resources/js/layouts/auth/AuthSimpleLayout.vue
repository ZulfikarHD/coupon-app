<script setup lang="ts">
import AppLogoIcon from '@/components/AppLogoIcon.vue';
import { useSwipeBack } from '@/composables/useHaptic';
import { home } from '@/routes';
import { Link } from '@inertiajs/vue3';
import { ref, onMounted } from 'vue';

defineProps<{
    title?: string;
    description?: string;
}>();

const containerRef = ref<HTMLElement | null>(null);
const isLoaded = ref(false);

// Enable swipe-back gesture
useSwipeBack(containerRef);

onMounted(() => {
    // Trigger entrance animations after mount
    requestAnimationFrame(() => {
        isLoaded.value = true;
    });
});
</script>

<template>
    <div
        ref="containerRef"
        class="relative flex min-h-svh flex-col items-center justify-center overflow-hidden p-6 md:p-10"
    >
        <!-- Animated Mesh Gradient Background -->
        <div class="bg-mesh-gradient fixed inset-0 -z-10" />

        <!-- Floating Decorative Elements -->
        <div
            class="animate-float pointer-events-none fixed left-[10%] top-[15%] h-64 w-64 rounded-full opacity-30 blur-3xl"
            style="
                background: radial-gradient(
                    circle,
                    var(--sky-400) 0%,
                    transparent 70%
                );
                animation-delay: 0s;
            "
        />
        <div
            class="animate-float pointer-events-none fixed right-[15%] top-[60%] h-48 w-48 rounded-full opacity-20 blur-3xl"
            style="
                background: radial-gradient(
                    circle,
                    var(--cyan-400) 0%,
                    transparent 70%
                );
                animation-delay: 2s;
            "
        />
        <div
            class="animate-float pointer-events-none fixed bottom-[20%] left-[20%] h-56 w-56 rounded-full opacity-25 blur-3xl"
            style="
                background: radial-gradient(
                    circle,
                    var(--sky-300) 0%,
                    transparent 70%
                );
                animation-delay: 4s;
            "
        />

        <!-- Main Content Container -->
        <div class="relative z-10 w-full max-w-md">
            <!-- Glass Card -->
            <div
                :class="[
                    'glass-strong rounded-3xl p-8 shadow-xl',
                    'transition-all duration-700',
                    isLoaded
                        ? 'translate-y-0 opacity-100'
                        : 'translate-y-8 opacity-0',
                ]"
                style="transition-timing-function: cubic-bezier(0.34, 1.56, 0.64, 1)"
            >
                <!-- Logo & Header -->
                <div class="flex flex-col items-center gap-6">
                    <Link
                        :href="home()"
                        :class="[
                            'group flex flex-col items-center gap-3',
                            'transition-all duration-500 delay-100',
                            isLoaded
                                ? 'translate-y-0 opacity-100'
                                : 'translate-y-4 opacity-0',
                        ]"
                    >
                        <!-- Logo with Glow -->
                        <div
                            class="relative flex h-16 w-16 items-center justify-center"
                        >
                            <!-- Glow Effect -->
                            <div
                                class="absolute inset-0 rounded-2xl opacity-0 blur-xl transition-opacity duration-300 group-hover:opacity-100"
                                style="background: var(--sky-400)"
                            />
                            <!-- Logo Container -->
                            <div
                                class="relative flex h-14 w-14 items-center justify-center rounded-2xl bg-gradient-to-br from-sky-500 to-cyan-500 shadow-lg transition-transform duration-300 group-hover:scale-105"
                            >
                                <AppLogoIcon class="h-8 w-8 text-white" />
                            </div>
                        </div>
                        <span class="sr-only">{{ title }}</span>
                    </Link>

                    <!-- Title & Description -->
                    <div
                        :class="[
                            'space-y-2 text-center',
                            'transition-all duration-500 delay-200',
                            isLoaded
                                ? 'translate-y-0 opacity-100'
                                : 'translate-y-4 opacity-0',
                        ]"
                    >
                        <h1
                            class="text-2xl font-semibold tracking-tight text-foreground"
                        >
                            {{ title }}
                        </h1>
                        <p class="text-sm text-muted-foreground">
                            {{ description }}
                        </p>
                    </div>
                </div>

                <!-- Form Slot with Staggered Animation -->
                <div
                    :class="[
                        'mt-8',
                        'transition-all duration-500 delay-300',
                        isLoaded
                            ? 'translate-y-0 opacity-100'
                            : 'translate-y-4 opacity-0',
                    ]"
                >
                    <slot />
                </div>
            </div>

            <!-- Footer Text -->
            <p
                :class="[
                    'mt-6 text-center text-xs text-muted-foreground/70',
                    'transition-all duration-500 delay-500',
                    isLoaded
                        ? 'translate-y-0 opacity-100'
                        : 'translate-y-4 opacity-0',
                ]"
            >
                Powered by secure authentication
            </p>
        </div>
    </div>
</template>
