<script setup lang="ts">
import AppLogoIcon from '@/components/AppLogoIcon.vue';
import { useHaptic, useSwipeGesture } from '@/composables/useHaptic';
import { dashboard, login, register } from '@/routes';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, onMounted } from 'vue';

withDefaults(
    defineProps<{
        canRegister: boolean;
    }>(),
    {
        canRegister: true,
    }
);

const { trigger } = useHaptic();
const isLoaded = ref(false);
const pageRef = ref<HTMLElement | null>(null);

// Swipe gesture for navigation
useSwipeGesture(pageRef, {
    threshold: 100,
    onSwipeLeft: () => {
        trigger('light');
        router.visit(login());
    },
});

onMounted(() => {
    requestAnimationFrame(() => {
        isLoaded.value = true;
    });
});

const features = [
    {
        icon: 'coupon',
        title: 'Kupon Pintar',
        description: 'Buat dan atur kupon digital tanpa ribet',
    },
    {
        icon: 'scan',
        title: 'Scan Cepat',
        description: 'Scan kode QR langsung tukar kupon',
    },
    {
        icon: 'analytics',
        title: 'Laporan',
        description: 'Pantau penggunaan dan performa langsung',
    },
];
</script>

<template>
    <Head title="Welcome">
        <link rel="preconnect" href="https://rsms.me/" />
        <link rel="stylesheet" href="https://rsms.me/inter/inter.css" />
    </Head>

    <div
        ref="pageRef"
        class="relative min-h-screen overflow-hidden"
    >
        <!-- Animated Mesh Gradient Background -->
        <div class="bg-mesh-gradient fixed inset-0 -z-10" />

        <!-- Floating Orbs -->
        <div
            class="animate-float pointer-events-none fixed left-[5%] top-[10%] h-96 w-96 rounded-full opacity-30 blur-3xl"
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
            class="animate-float pointer-events-none fixed right-[10%] bottom-[20%] h-80 w-80 rounded-full opacity-25 blur-3xl"
            style="
                background: radial-gradient(
                    circle,
                    var(--cyan-400) 0%,
                    transparent 70%
                );
                animation-delay: 3s;
            "
        />
        <div
            class="animate-float pointer-events-none fixed left-[40%] top-[60%] h-64 w-64 rounded-full opacity-20 blur-3xl"
            style="
                background: radial-gradient(
                    circle,
                    var(--sky-300) 0%,
                    transparent 70%
                );
                animation-delay: 5s;
            "
        />

        <!-- Glass Navigation Bar -->
        <header
            :class="[
                'glass-strong fixed left-0 right-0 top-0 z-50',
                'px-6 py-4',
                'transition-all duration-700',
                isLoaded ? 'translate-y-0 opacity-100' : '-translate-y-full opacity-0',
            ]"
        >
            <nav class="mx-auto flex max-w-6xl items-center justify-between">
                <!-- Logo -->
                <Link
                    href="/"
                    class="flex items-center gap-3 transition-transform duration-300 hover:scale-105 press-effect"
                    @click="trigger('light')"
                >
                    <div
                        class="flex h-10 w-10 items-center justify-center rounded-xl bg-gradient-to-br from-sky-500 to-cyan-500 shadow-lg"
                    >
                        <AppLogoIcon class="h-6 w-6 text-white" />
                    </div>
                    <span class="text-lg font-semibold text-foreground">
                        CouponApp
                    </span>
                </Link>

                <!-- Nav Links -->
                <div class="flex items-center gap-3">
                <Link
                    v-if="$page.props.auth.user"
                    :href="dashboard()"
                        :class="[
                            'btn-gradient rounded-xl px-5 py-2.5 text-sm font-medium',
                            'press-effect',
                        ]"
                        @click="trigger('light')"
                >
                    Dasbor
                </Link>
                <template v-else>
                    <Link
                        :href="login()"
                            class="rounded-xl px-5 py-2.5 text-sm font-medium text-foreground/80 transition-colors hover:text-foreground press-effect"
                            @click="trigger('light')"
                    >
                            Login
                    </Link>
                    <Link
                        v-if="canRegister"
                        :href="register()"
                            :class="[
                                'btn-gradient rounded-xl px-5 py-2.5 text-sm font-medium',
                                'press-effect',
                            ]"
                            @click="trigger('light')"
                        >
                            Ayo Mulai
                    </Link>
                </template>
                </div>
            </nav>
        </header>

        <!-- Hero Section -->
        <main class="relative z-10 flex min-h-screen flex-col items-center justify-center px-6 pt-20">
            <div class="mx-auto max-w-4xl text-center">
                <!-- Animated Badge -->
                <div
                    :class="[
                        'mb-8 inline-flex items-center gap-2 rounded-full',
                        'glass px-4 py-2',
                        'transition-all duration-700',
                        isLoaded ? 'translate-y-0 opacity-100' : 'translate-y-8 opacity-0',
                    ]"
                    style="transition-delay: 100ms"
                >
                    <span class="relative flex h-2 w-2">
                            <span
                            class="absolute inline-flex h-full w-full animate-ping rounded-full bg-sky-400 opacity-75"
                        />
                            <span
                            class="relative inline-flex h-2 w-2 rounded-full bg-sky-500"
                                    />
                                </span>
                    <span class="text-sm font-medium text-foreground/80">
                        Sekarang ada fitur scan QR
                            </span>
                </div>

                <!-- Hero Title -->
                <h1
                    :class="[
                        'mb-6 text-5xl font-bold tracking-tight text-foreground md:text-6xl lg:text-7xl',
                        'transition-all duration-700',
                        isLoaded ? 'translate-y-0 opacity-100' : 'translate-y-8 opacity-0',
                    ]"
                    style="transition-delay: 200ms"
                >
                    <span class="block">Kelola Kupon</span>
                    <span
                        class="block bg-gradient-to-r from-sky-500 via-cyan-500 to-sky-500 bg-clip-text text-transparent"
                    >
                        Dengan Modern
                    </span>
                </h1>

                <!-- Hero Description -->
                <p
                    :class="[
                        'mx-auto mb-10 max-w-2xl text-lg text-muted-foreground md:text-xl',
                        'transition-all duration-700',
                        isLoaded ? 'translate-y-0 opacity-100' : 'translate-y-8 opacity-0',
                    ]"
                    style="transition-delay: 300ms"
                >
                    Cara baru kelola kupon digital yang lebih gampang dan menyenangkan.
                    Antarmuka cantik, fitur lengkap, pengalaman super mulus.
                </p>

                <!-- CTA Buttons -->
                <div
                    :class="[
                        'flex flex-col items-center justify-center gap-4 sm:flex-row',
                        'transition-all duration-700',
                        isLoaded ? 'translate-y-0 opacity-100' : 'translate-y-8 opacity-0',
                    ]"
                    style="transition-delay: 400ms"
                >
                    <Link
                        v-if="!$page.props.auth.user"
                        :href="register()"
                        :class="[
                            'btn-gradient rounded-2xl px-8 py-4 text-base font-semibold',
                            'press-effect',
                            'flex items-center gap-2',
                        ]"
                        @click="trigger('medium')"
                    >
                        Coba Gratis
                        <svg
                            class="h-5 w-5"
                        fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M13 7l5 5m0 0l-5 5m5-5H6"
                            />
                        </svg>
                    </Link>
                    <Link
                        v-else
                        :href="dashboard()"
                        :class="[
                            'btn-gradient rounded-2xl px-8 py-4 text-base font-semibold',
                            'press-effect',
                            'flex items-center gap-2',
                        ]"
                        @click="trigger('medium')"
                    >
                        Lanjut ke Dasbor
                        <svg
                            class="h-5 w-5"
                        fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M13 7l5 5m0 0l-5 5m5-5H6"
                            />
                        </svg>
                    </Link>
                    <a
                        href="#features"
                        class="glass rounded-2xl px-8 py-4 text-base font-semibold text-foreground transition-all duration-300 hover:bg-white/20 press-effect dark:hover:bg-white/10"
                        @click="trigger('light')"
                    >
                        Lihat Fitur
                    </a>
                </div>
            </div>

            <!-- Feature Cards -->
            <div
                id="features"
                class="mx-auto mt-24 grid max-w-5xl gap-6 px-6 sm:grid-cols-2 lg:grid-cols-3"
            >
                <div
                    v-for="(feature, index) in features"
                    :key="feature.title"
                    :class="[
                        'glass-strong rounded-3xl p-6',
                        'card-hover cursor-default',
                        'transition-all duration-700',
                        isLoaded ? 'translate-y-0 opacity-100' : 'translate-y-8 opacity-0',
                    ]"
                    :style="{ transitionDelay: `${500 + index * 100}ms` }"
                >
                    <!-- Icon -->
                    <div
                        class="mb-4 flex h-12 w-12 items-center justify-center rounded-2xl bg-gradient-to-br from-sky-500 to-cyan-500"
                    >
                        <!-- Coupon Icon -->
                        <svg
                            v-if="feature.icon === 'coupon'"
                            class="h-6 w-6 text-white"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z"
                            />
                        </svg>
                        <!-- Scan Icon -->
                        <svg
                            v-else-if="feature.icon === 'scan'"
                            class="h-6 w-6 text-white"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"
                            />
                        </svg>
                        <!-- Analytics Icon -->
                        <svg
                            v-else-if="feature.icon === 'analytics'"
                            class="h-6 w-6 text-white"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"
                            />
                    </svg>
                    </div>

                    <!-- Content -->
                    <h3 class="mb-2 text-lg font-semibold text-foreground">
                        {{ feature.title }}
                    </h3>
                    <p class="text-sm text-muted-foreground">
                        {{ feature.description }}
                    </p>
                </div>
            </div>

            <!-- Scroll Indicator -->
            <div
                :class="[
                    'absolute bottom-8 left-1/2 -translate-x-1/2',
                    'flex flex-col items-center gap-2',
                    'transition-all duration-700',
                    isLoaded ? 'translate-y-0 opacity-100' : 'translate-y-4 opacity-0',
                ]"
                style="transition-delay: 800ms"
            >
                <span class="text-xs text-muted-foreground/60">Geser kiri untuk login</span>
                <div class="flex items-center gap-1">
                    <div class="h-1 w-6 rounded-full bg-sky-500/50" />
                    <div class="h-1 w-1 rounded-full bg-muted-foreground/30" />
                    <div class="h-1 w-1 rounded-full bg-muted-foreground/30" />
                </div>
                </div>
            </main>

        <!-- Footer -->
        <footer
            :class="[
                'glass-strong fixed bottom-0 left-0 right-0 z-40',
                'px-6 py-4',
                'transition-all duration-700',
                isLoaded ? 'translate-y-0 opacity-100' : 'translate-y-full opacity-0',
            ]"
            style="transition-delay: 600ms"
        >
            <div class="mx-auto flex max-w-6xl items-center justify-between">
                <p class="text-sm text-muted-foreground">
                    © 2024 CouponApp. Semua hak dilindungi.
                </p>
                <div class="flex items-center gap-4">
                    <a
                        href="#"
                        class="text-sm text-muted-foreground transition-colors hover:text-foreground"
                    >
                        Privasi
                    </a>
                    <a
                        href="#"
                        class="text-sm text-muted-foreground transition-colors hover:text-foreground"
                    >
                        Ketentuan
                    </a>
                </div>
        </div>
        </footer>
    </div>
</template>
