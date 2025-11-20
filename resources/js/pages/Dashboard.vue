<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import PageHeader from '@/components/PageHeader.vue';
import EmptyState from '@/components/EmptyState.vue';
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
        color: 'text-green-600 dark:text-green-400',
        bgColor: 'bg-green-500/10 dark:bg-green-500/20',
    },
    {
        title: 'Terpakai Hari Ini',
        value: props.stats.used_today,
        description: 'Kupon yang sudah digunakan hari ini',
        icon: CheckCircle2,
        color: 'text-blue-600 dark:text-blue-400',
        bgColor: 'bg-blue-500/10 dark:bg-blue-500/20',
    },
    {
        title: 'Kedaluwarsa Minggu Ini',
        value: props.stats.expiring_this_week,
        description: 'Kupon yang akan kedaluwarsa dalam 7 hari',
        icon: Clock,
        color: 'text-orange-600 dark:text-orange-400',
        bgColor: 'bg-orange-500/10 dark:bg-orange-500/20',
    },
    {
        title: 'Total Kupon',
        value: props.stats.total_coupons,
        description: 'Semua kupon yang pernah dibuat',
        icon: TrendingUp,
        color: 'text-purple-600 dark:text-purple-400',
        bgColor: 'bg-purple-500/10 dark:bg-purple-500/20',
    },
];
</script>

<template>
    <Head title="Dashboard" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 overflow-x-auto p-4 md:p-6">
            <!-- Header -->
            <PageHeader
                title="Dashboard"
                description="Ringkasan aktivitas dan statistik kupon"
            />

            <!-- Stats Cards -->
            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                <Card
                    v-for="(stat, index) in statCards"
                    :key="index"
                    class="border transition-all duration-200 hover:shadow-md rounded-xl"
                >
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-3">
                        <CardTitle class="text-sm font-medium text-muted-foreground">
                            {{ stat.title }}
                        </CardTitle>
                        <div :class="[stat.bgColor, 'rounded-xl p-2.5']">
                            <component
                                :is="stat.icon"
                                :class="[stat.color, 'h-5 w-5']"
                            />
                        </div>
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-semibold mb-1">{{ stat.value }}</div>
                        <p class="text-xs text-muted-foreground">
                            {{ stat.description }}
                        </p>
                    </CardContent>
                </Card>
            </div>

            <!-- Quick Actions -->
            <Card class="border rounded-xl">
                <CardHeader class="pb-4">
                    <div class="flex items-center gap-2">
                        <Activity class="h-5 w-5 text-primary" />
                        <CardTitle class="text-lg font-semibold">Aksi Cepat</CardTitle>
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
                            class="h-12 w-full gap-2 rounded-xl sm:w-auto sm:flex-1"
                        >
                            <Link href="/coupons/create">
                                <Plus class="h-5 w-5" />
                                Buat Kupon Baru
                            </Link>
                        </Button>
                        <Button
                            as-child
                            size="lg"
                            variant="default"
                            class="h-12 w-full gap-2 rounded-xl bg-gradient-to-r from-green-500 to-emerald-500 hover:from-green-600 hover:to-emerald-600 sm:w-auto sm:flex-1"
                        >
                            <Link href="/scan">
                                <ScanLine class="h-5 w-5" />
                                Scan Kupon
                            </Link>
                        </Button>
                    </div>
                </CardContent>
            </Card>

            <!-- Recent Activity Feed -->
            <Card class="border rounded-xl">
                <CardHeader class="pb-4">
                    <div class="flex items-center gap-2">
                        <Activity class="h-5 w-5 text-primary" />
                        <CardTitle class="text-lg font-semibold">Aktivitas Terkini</CardTitle>
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
                            v-for="activity in recentActivity"
                            :key="activity.id"
                            class="flex items-start gap-4 rounded-xl border p-4 transition-all duration-200 hover:bg-muted/50 hover:shadow-sm"
                        >
                            <div class="rounded-full bg-primary/10 p-2.5 flex-shrink-0">
                                <CheckCircle2 class="h-5 w-5 text-primary" />
                            </div>
                            <div class="flex-1 space-y-1.5 min-w-0">
                                <div class="flex items-center gap-2 flex-wrap">
                                    <span class="font-medium text-foreground">{{ activity.customer_name }}</span>
                                    <Badge variant="outline" class="text-xs rounded-full">
                                        {{ activity.coupon_type }}
                                    </Badge>
                                    <span class="text-xs text-muted-foreground font-mono">
                                        {{ activity.coupon_code }}
                                    </span>
                                </div>
                                <div class="flex items-center gap-2 text-sm text-muted-foreground">
                                    <User class="h-3.5 w-3.5" />
                                    <span>Validasi oleh {{ activity.validated_by }}</span>
                                    <span>•</span>
                                    <span>{{ activity.time_ago }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>
