<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
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
        <div class="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4 md:p-6">
            <!-- Header -->
            <div class="space-y-1">
                <h1 class="text-2xl font-semibold tracking-tight md:text-3xl">
                    Dashboard
                </h1>
                <p class="text-sm text-muted-foreground md:text-base">
                    Ringkasan aktivitas dan statistik kupon
                </p>
            </div>

            <!-- Stats Cards -->
            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                <Card
                    v-for="(stat, index) in statCards"
                    :key="index"
                    class="border-2 transition-shadow hover:shadow-md"
                >
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium text-muted-foreground">
                            {{ stat.title }}
                        </CardTitle>
                        <div :class="[stat.bgColor, 'rounded-lg p-2']">
                            <component
                                :is="stat.icon"
                                :class="[stat.color, 'h-5 w-5']"
                            />
                        </div>
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold">{{ stat.value }}</div>
                        <p class="text-xs text-muted-foreground mt-1">
                            {{ stat.description }}
                        </p>
                    </CardContent>
                </Card>
            </div>

            <!-- Quick Actions -->
            <Card class="border-2">
                <CardHeader>
                    <CardTitle class="flex items-center gap-2">
                        <Activity class="h-5 w-5 text-primary" />
                        Aksi Cepat
                    </CardTitle>
                    <CardDescription>
                        Buat kupon baru atau scan kupon untuk validasi
                    </CardDescription>
                </CardHeader>
                <CardContent>
                    <div class="flex flex-col gap-3 sm:flex-row">
                        <Button
                            as-child
                            size="lg"
                            class="h-12 w-full gap-2 text-base sm:w-auto sm:flex-1"
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
                            class="h-12 w-full gap-2 bg-gradient-to-r from-green-500 to-orange-500 text-base hover:from-green-600 hover:to-orange-600 sm:w-auto sm:flex-1"
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
            <Card class="border-2">
                <CardHeader>
                    <CardTitle class="flex items-center gap-2">
                        <Activity class="h-5 w-5 text-primary" />
                        Aktivitas Terkini
                    </CardTitle>
                    <CardDescription>
                        Validasi kupon terakhir (10 aktivitas terbaru)
                    </CardDescription>
                </CardHeader>
                <CardContent>
                    <div v-if="recentActivity.length === 0" class="py-8 text-center">
                        <Activity class="mx-auto h-12 w-12 text-muted-foreground mb-4" />
                        <p class="text-sm text-muted-foreground">
                            Belum ada aktivitas validasi
                        </p>
                    </div>
                    <div v-else class="space-y-3">
                        <div
                            v-for="activity in recentActivity"
                            :key="activity.id"
                            class="flex items-start gap-4 rounded-lg border border-border/50 p-4 transition-colors hover:bg-muted/50"
                        >
                            <div class="rounded-full bg-primary/10 p-2">
                                <CheckCircle2 class="h-5 w-5 text-primary" />
                            </div>
                            <div class="flex-1 space-y-1">
                                <div class="flex items-center gap-2 flex-wrap">
                                    <span class="font-medium">{{ activity.customer_name }}</span>
                                    <Badge variant="outline" class="text-xs">
                                        {{ activity.coupon_type }}
                                    </Badge>
                                    <span class="text-xs text-muted-foreground">
                                        ({{ activity.coupon_code }})
                                    </span>
                                </div>
                                <div class="flex items-center gap-2 text-sm text-muted-foreground">
                                    <User class="h-3 w-3" />
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
