<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import PageHeader from '@/components/PageHeader.vue';
import EmptyState from '@/components/EmptyState.vue';
import PullToRefresh from '@/components/PullToRefresh.vue';
import { useHaptic } from '@/composables/useHaptic';
import { dashboard } from '@/routes';
import { type BreadcrumbItem } from '@/types';
import { Head, Link } from '@inertiajs/vue3';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import {
    Ticket,
    CheckCircle2,
    Clock,
    TrendingUp,
    Plus,
    ScanLine,
    Activity,
    User
} from 'lucide-vue-next';
import { ref, onMounted } from 'vue';

interface Stats {
    active_coupons: number;
    used_today: number;
    expiring_this_week: number;
    total_coupons: number;
}

interface RecentActivityItem {
    id: number;
    customer_name: string;
    coupon_type: string;
    coupon_code: string;
    validated_by: string;
    validated_at: string;
    time_ago: string;
}

interface Props {
    stats: Stats;
    recentActivity: RecentActivityItem[];
}

const props = defineProps<Props>();
const { trigger } = useHaptic();
const isLoaded = ref(false);

onMounted(() => {
    requestAnimationFrame(() => {
        isLoaded.value = true;
    });
});

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: dashboard(),
    },
];

const statCards = [
    {
        title: 'Kupon Aktif',
        value: props.stats.active_coupons,
        description: 'Kupon yang masih dapat digunakan',
        icon: Ticket,
        color: 'text-emerald-600 dark:text-emerald-400',
        bgColor: 'bg-emerald-500/10 dark:bg-emerald-500/20',
        gradient: 'from-emerald-500 to-teal-500',
    },
    {
        title: 'Terpakai Hari Ini',
        value: props.stats.used_today,
        description: 'Kupon yang sudah digunakan hari ini',
        icon: CheckCircle2,
        color: 'text-sky-600 dark:text-sky-400',
        bgColor: 'bg-sky-500/10 dark:bg-sky-500/20',
        gradient: 'from-sky-500 to-cyan-500',
    },
    {
        title: 'Kedaluwarsa Minggu Ini',
        value: props.stats.expiring_this_week,
        description: 'Kupon yang akan kedaluwarsa dalam 7 hari',
        icon: Clock,
        color: 'text-amber-600 dark:text-amber-400',
        bgColor: 'bg-amber-500/10 dark:bg-amber-500/20',
        gradient: 'from-amber-500 to-orange-500',
    },
    {
        title: 'Total Kupon',
        value: props.stats.total_coupons,
        description: 'Semua kupon yang pernah dibuat',
        icon: TrendingUp,
        color: 'text-violet-600 dark:text-violet-400',
        bgColor: 'bg-violet-500/10 dark:bg-violet-500/20',
        gradient: 'from-violet-500 to-purple-500',
    },
];

const handleActionClick = () => {
    trigger('medium');
};
</script>

<template>
    <Head title="Dashboard" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <PullToRefresh>
            <div class="flex h-full flex-1 flex-col gap-4 sm:gap-6 overflow-x-auto p-4 md:p-6">
                <!-- Header with spring animation -->
                <div
                    :class="[
                        'transition-all duration-700',
                        isLoaded ? 'translate-y-0 opacity-100' : 'translate-y-6 opacity-0',
                    ]"
                >
                <PageHeader
                    title="Dashboard"
                    description="Ringkasan aktivitas dan statistik kupon"
                />
            </div>

            <!-- Quick Actions with staggered animation -->
            <Card
                :class="[
                    'glass-strong border-0 rounded-2xl overflow-hidden',
                    'transition-all duration-700',
                    isLoaded ? 'translate-y-0 opacity-100' : 'translate-y-6 opacity-0',
                ]"
                :style="{ transitionDelay: '150ms' }"
            >
                <CardHeader class="pb-4">
                    <div class="flex items-center gap-2">
                        <div class="flex h-8 w-8 items-center justify-center rounded-xl bg-gradient-to-br from-sky-500 to-cyan-500">
                            <Activity class="h-4 w-4 text-white" />
                        </div>
                        <CardTitle class="text-base sm:text-lg font-semibold">Aksi Cepat</CardTitle>
                    </div>
                    <CardDescription class="text-sm mt-1">
                        Buat kupon baru atau scan kupon untuk validasi
                    </CardDescription>
                </CardHeader>
                <CardContent>
                    <div class="flex flex-col gap-3 sm:flex-row">
                        <Button
                            as-child
                            size="lg"
                            :class="[
                                'h-14 w-full gap-2 rounded-2xl',
                                'bg-gradient-to-r from-emerald-500 to-teal-500',
                                'hover:from-emerald-600 hover:to-teal-600',
                                'shadow-lg shadow-emerald-500/25',
                                'press-effect',
                                'sm:w-auto sm:flex-1',
                            ]"
                            @click="handleActionClick"
                        >
                            <Link href="/scan">
                                <ScanLine class="h-6 w-6" />
                                <span class="text-base font-semibold">Scan Kupon</span>
                            </Link>
                        </Button>
                        <Button
                            as-child
                            size="lg"
                            variant="outline"
                            :class="[
                                'h-12 w-full gap-2 rounded-2xl',
                                'border-2 border-border/50',
                                'press-effect',
                                'sm:w-auto sm:flex-1',
                            ]"
                            @click="handleActionClick"
                        >
                            <Link href="/coupons/create">
                                <Plus class="h-5 w-5" />
                                Buat Kupon Baru
                            </Link>
                        </Button>
                    </div>
                </CardContent>
            </Card>

            <!-- Stats Cards with staggered spring animation -->
            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                <Card
                    v-for="(stat, index) in statCards"
                    :key="index"
                    :class="[
                        'glass-strong border-0 rounded-2xl overflow-hidden card-hover mobile-card-press',
                        isLoaded ? 'animate-spring-up' : 'opacity-0 translate-y-6',
                    ]"
                    :style="{ animationDelay: `${300 + index * 100}ms` }"
                >
                    <CardContent class="p-4 sm:p-6">
                        <div class="flex items-start justify-between mb-3">
                            <div
                                :class="[
                                    'rounded-2xl p-3',
                                    'bg-gradient-to-br',
                                    stat.gradient,
                                ]"
                            >
                                <component
                                    :is="stat.icon"
                                    class="h-5 w-5 text-white"
                                />
                            </div>
                        </div>
                        <div class="text-3xl sm:text-4xl font-bold mb-1">{{ stat.value }}</div>
                        <CardTitle class="text-sm font-medium text-muted-foreground mb-1">
                            {{ stat.title }}
                        </CardTitle>
                        <p class="text-xs text-muted-foreground/70 leading-relaxed">
                            {{ stat.description }}
                        </p>
                    </CardContent>
                </Card>
            </div>

            <!-- Recent Activity Feed with spring animation -->
            <Card
                :class="[
                    'glass-strong border-0 rounded-2xl overflow-hidden',
                    'transition-all duration-700',
                    isLoaded ? 'translate-y-0 opacity-100' : 'translate-y-6 opacity-0',
                ]"
                :style="{ transitionDelay: '700ms' }"
            >
                <CardHeader class="pb-4">
                    <div class="flex items-center gap-2">
                        <div class="flex h-8 w-8 items-center justify-center rounded-xl bg-gradient-to-br from-violet-500 to-purple-500">
                            <Activity class="h-4 w-4 text-white" />
                        </div>
                        <CardTitle class="text-base sm:text-lg font-semibold">Aktivitas Terkini</CardTitle>
                    </div>
                    <CardDescription class="text-sm mt-1">
                        Validasi kupon terakhir (10 aktivitas terbaru)
                    </CardDescription>
                </CardHeader>
                <CardContent>
                    <EmptyState
                        v-if="recentActivity.length === 0"
                        :icon="Activity"
                        title="Belum ada aktivitas validasi"
                        description="Aktivitas validasi akan muncul di sini"
                    />
                    <div v-else class="space-y-3">
                        <div
                            v-for="(activity, actIndex) in recentActivity"
                            :key="activity.id"
                            :class="[
                                'flex items-start gap-3 rounded-2xl border border-border/50 p-4',
                                'bg-background/50',
                                'mobile-card-press card-hover',
                                isLoaded ? 'animate-spring-up' : 'opacity-0',
                            ]"
                            :style="{ animationDelay: `${800 + actIndex * 80}ms` }"
                        >
                            <div class="rounded-xl bg-gradient-to-br from-emerald-500 to-teal-500 p-2.5 flex-shrink-0">
                                <CheckCircle2 class="h-4 w-4 text-white" />
                            </div>
                            <div class="flex-1 space-y-1.5 min-w-0 overflow-hidden">
                                <div class="flex flex-col gap-1.5 sm:flex-row sm:items-center sm:gap-2">
                                    <span class="font-semibold text-foreground truncate">
                                        {{ activity.customer_name }}
                                    </span>
                                    <div class="flex items-center gap-2 flex-wrap">
                                        <Badge
                                            variant="outline"
                                            class="text-xs rounded-full border-sky-200 bg-sky-50 text-sky-700 dark:border-sky-800 dark:bg-sky-900/30 dark:text-sky-300"
                                        >
                                            {{ activity.coupon_type }}
                                        </Badge>
                                        <span class="text-xs text-muted-foreground font-mono truncate">
                                            {{ activity.coupon_code }}
                                        </span>
                                    </div>
                                </div>
                                <div class="flex items-center gap-1.5 text-xs sm:text-sm text-muted-foreground flex-wrap">
                                    <User class="h-3 w-3 flex-shrink-0" />
                                    <span class="truncate">Validasi oleh {{ activity.validated_by }}</span>
                                    <span class="hidden sm:inline">•</span>
                                    <span class="shrink-0">{{ activity.time_ago }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </CardContent>
            </Card>
            </div>
        </PullToRefresh>
    </AppLayout>
</template>
