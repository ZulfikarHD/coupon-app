<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, router, useForm } from '@inertiajs/vue3';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import {
    BarChart3,
    TrendingUp,
    CheckCircle2,
    Ticket,
    Download,
    FileSpreadsheet,
    FileText,
    Loader2,
    Clock,
    Users,
    Eye
} from 'lucide-vue-next';
import { computed, ref } from 'vue';
import { type BreadcrumbItem } from '@/types';

interface SummaryStats {
    total_created: number;
    total_used: number;
    redemption_rate: number;
    currently_active: number;
    total_expired: number;
}

interface TopType {
    type: string;
    created_count: number;
    used_count: number;
    expired_count: number;
    usage_rate: number;
}

interface DailyUsage {
    date: string;
    count: number;
}

interface FrequentCustomer {
    customer_name: string;
    customer_phone: string;
    formatted_phone: string;
    total_coupons: number;
    total_used: number;
    usage_rate: number;
    last_coupon_date: string;
}

interface Props {
    summaryStats: SummaryStats;
    topTypes: TopType[];
    dailyUsage: DailyUsage[];
    frequentCustomers: FrequentCustomer[];
    filters: {
        date_from: string;
        date_to: string;
    };
}

const props = defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: '/dashboard',
    },
    {
        title: 'Laporan',
        href: '/reports',
    },
];

const form = useForm({
    date_from: props.filters.date_from,
    date_to: props.filters.date_to,
});

const isLoading = computed(() => form.processing);

const applyFilters = () => {
    form.get('/reports', {
        preserveState: true,
        preserveScroll: true,
    });
};

const resetFilters = () => {
    const today = new Date();
    const thirtyDaysAgo = new Date();
    thirtyDaysAgo.setDate(today.getDate() - 30);

    form.date_from = thirtyDaysAgo.toISOString().split('T')[0];
    form.date_to = today.toISOString().split('T')[0];
    applyFilters();
};

const statCards = computed(() => [
    {
        title: 'Total Kupon Dibuat',
        value: props.summaryStats.total_created,
        description: `Dalam periode ${formatDate(props.filters.date_from)} - ${formatDate(props.filters.date_to)}`,
        icon: Ticket,
        color: 'text-blue-600 dark:text-blue-400',
        bgColor: 'bg-blue-500/10 dark:bg-blue-500/20',
    },
    {
        title: 'Total Kupon Terpakai',
        value: props.summaryStats.total_used,
        description: 'Kupon yang sudah divalidasi dalam periode',
        icon: CheckCircle2,
        color: 'text-green-600 dark:text-green-400',
        bgColor: 'bg-green-500/10 dark:bg-green-500/20',
    },
    {
        title: 'Tingkat Penebusan',
        value: `${props.summaryStats.redemption_rate}%`,
        description: 'Persentase kupon yang digunakan',
        icon: TrendingUp,
        color: 'text-purple-600 dark:text-purple-400',
        bgColor: 'bg-purple-500/10 dark:bg-purple-500/20',
    },
    {
        title: 'Kupon Aktif Saat Ini',
        value: props.summaryStats.currently_active,
        description: 'Kupon yang masih dapat digunakan',
        icon: Ticket,
        color: 'text-orange-600 dark:text-orange-400',
        bgColor: 'bg-orange-500/10 dark:bg-orange-500/20',
    },
    {
        title: 'Total Kupon Kedaluwarsa',
        value: props.summaryStats.total_expired,
        description: `Kupon yang kedaluwarsa dalam periode`,
        icon: Clock,
        color: 'text-red-600 dark:text-red-400',
        bgColor: 'bg-red-500/10 dark:bg-red-500/20',
    },
]);

const formatDate = (dateString: string): string => {
    const date = new Date(dateString);
    return date.toLocaleDateString('id-ID', {
        day: 'numeric',
        month: 'short',
        year: 'numeric'
    });
};

const isExporting = ref(false);
const exportFormat = ref<'xlsx' | 'csv' | null>(null);

const exportToExcel = () => {
    isExporting.value = true;
    exportFormat.value = 'xlsx';
    const params = new URLSearchParams({
        format: 'xlsx',
        date_from: form.date_from,
        date_to: form.date_to,
    });
    
    // Create a temporary link to trigger download
    const link = document.createElement('a');
    link.href = `/reports/export?${params.toString()}`;
    link.download = '';
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
    
    // Reset loading state after a delay
    setTimeout(() => {
        isExporting.value = false;
        exportFormat.value = null;
    }, 2000);
};

const exportToCSV = () => {
    isExporting.value = true;
    exportFormat.value = 'csv';
    const params = new URLSearchParams({
        format: 'csv',
        date_from: form.date_from,
        date_to: form.date_to,
    });
    
    // Create a temporary link to trigger download
    const link = document.createElement('a');
    link.href = `/reports/export?${params.toString()}`;
    link.download = '';
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
    
    // Reset loading state after a delay
    setTimeout(() => {
        isExporting.value = false;
        exportFormat.value = null;
    }, 2000);
};

const viewCustomerCoupons = (phone: string) => {
    router.get('/coupons', {
        customer_phone: phone,
        preserveState: true,
        preserveScroll: true,
    });
};
</script>

<template>
    <Head title="Laporan" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4 md:p-6">
            <!-- Header -->
            <div class="space-y-1">
                <h1 class="text-2xl font-semibold tracking-tight md:text-3xl">
                    Laporan & Analitik
                </h1>
                <p class="text-sm text-muted-foreground md:text-base">
                    Analisis penggunaan kupon dan statistik bisnis
                </p>
            </div>

            <!-- Date Range Filter -->
            <Card class="border-2">
                <CardHeader>
                    <CardTitle class="flex items-center gap-2">
                        <BarChart3 class="h-5 w-5 text-primary" />
                        Filter Periode
                    </CardTitle>
                    <CardDescription>
                        Pilih rentang tanggal untuk melihat statistik
                    </CardDescription>
                </CardHeader>
                <CardContent>
                    <form @submit.prevent="applyFilters" class="space-y-4">
                        <div class="flex flex-col gap-4 sm:flex-row sm:items-end">
                            <div class="flex-1 space-y-2">
                                <Label for="date_from">Dari Tanggal</Label>
                                <Input
                                    id="date_from"
                                    v-model="form.date_from"
                                    type="date"
                                    :disabled="isLoading"
                                    class="w-full"
                                />
                            </div>
                            <div class="flex-1 space-y-2">
                                <Label for="date_to">Sampai Tanggal</Label>
                                <Input
                                    id="date_to"
                                    v-model="form.date_to"
                                    type="date"
                                    :disabled="isLoading"
                                    class="w-full"
                                />
                            </div>
                            <div class="flex gap-2">
                                <Button
                                    type="submit"
                                    :disabled="isLoading"
                                    class="gap-2"
                                >
                                    <Loader2 v-if="isLoading" class="h-4 w-4 animate-spin" />
                                    <BarChart3 v-else class="h-4 w-4" />
                                    {{ isLoading ? 'Memuat...' : 'Terapkan' }}
                                </Button>
                                <Button
                                    type="button"
                                    variant="outline"
                                    @click="resetFilters"
                                    :disabled="isLoading"
                                >
                                    Reset
                                </Button>
                            </div>
                        </div>
                    </form>
                </CardContent>
            </Card>

            <!-- Summary Stats Cards -->
            <div class="grid gap-4 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-5">
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

            <!-- Top Coupon Types Table -->
            <Card class="border-2">
                <CardHeader>
                    <div class="flex items-center justify-between">
                        <div>
                            <CardTitle class="flex items-center gap-2">
                                <TrendingUp class="h-5 w-5 text-primary" />
                                Top Tipe Kupon
                            </CardTitle>
                            <CardDescription>
                                Ranking tipe kupon berdasarkan jumlah dibuat dan digunakan
                            </CardDescription>
                        </div>
                        <div class="flex gap-2">
                            <Button
                                variant="outline"
                                size="sm"
                                @click="exportToExcel"
                                :disabled="isExporting"
                                class="gap-2"
                            >
                                <Loader2 v-if="isExporting && exportFormat === 'xlsx'" class="h-4 w-4 animate-spin" />
                                <FileSpreadsheet v-else class="h-4 w-4" />
                                {{ isExporting && exportFormat === 'xlsx' ? 'Mengekspor...' : 'Excel' }}
                            </Button>
                            <Button
                                variant="outline"
                                size="sm"
                                @click="exportToCSV"
                                :disabled="isExporting"
                                class="gap-2"
                            >
                                <Loader2 v-if="isExporting && exportFormat === 'csv'" class="h-4 w-4 animate-spin" />
                                <FileText v-else class="h-4 w-4" />
                                {{ isExporting && exportFormat === 'csv' ? 'Mengekspor...' : 'CSV' }}
                            </Button>
                        </div>
                    </div>
                </CardHeader>
                <CardContent>
                    <div v-if="topTypes.length === 0" class="py-8 text-center">
                        <Ticket class="mx-auto h-12 w-12 text-muted-foreground mb-4" />
                        <p class="text-sm text-muted-foreground">
                            Tidak ada data kupon dalam periode yang dipilih
                        </p>
                    </div>
                    <div v-else class="overflow-x-auto">
                        <table class="w-full border-collapse">
                            <thead>
                                <tr class="border-b border-border">
                                    <th class="text-left p-3 text-sm font-semibold text-muted-foreground">
                                        Tipe Kupon
                                    </th>
                                    <th class="text-right p-3 text-sm font-semibold text-muted-foreground">
                                        Dibuat
                                    </th>
                                    <th class="text-right p-3 text-sm font-semibold text-muted-foreground">
                                        Terpakai
                                    </th>
                                    <th class="text-right p-3 text-sm font-semibold text-muted-foreground">
                                        Kedaluwarsa
                                    </th>
                                    <th class="text-right p-3 text-sm font-semibold text-muted-foreground">
                                        Tingkat Penggunaan
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr
                                    v-for="(type, index) in topTypes"
                                    :key="index"
                                    class="border-b border-border/50 hover:bg-muted/50 transition-colors"
                                >
                                    <td class="p-3 text-sm font-medium">
                                        {{ type.type }}
                                    </td>
                                    <td class="p-3 text-sm text-right">
                                        {{ type.created_count }}
                                    </td>
                                    <td class="p-3 text-sm text-right">
                                        {{ type.used_count }}
                                    </td>
                                    <td class="p-3 text-sm text-right">
                                        <span class="text-red-600 dark:text-red-400 font-medium">
                                            {{ type.expired_count }}
                                        </span>
                                    </td>
                                    <td class="p-3 text-sm text-right">
                                        <span
                                            :class="{
                                                'text-green-600 dark:text-green-400': type.usage_rate >= 50,
                                                'text-orange-600 dark:text-orange-400': type.usage_rate >= 25 && type.usage_rate < 50,
                                                'text-red-600 dark:text-red-400': type.usage_rate < 25,
                                            }"
                                            class="font-semibold"
                                        >
                                            {{ type.usage_rate }}%
                                        </span>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </CardContent>
            </Card>

            <!-- Frequent Customers Table -->
            <Card class="border-2">
                <CardHeader>
                    <CardTitle class="flex items-center gap-2">
                        <Users class="h-5 w-5 text-primary" />
                        Pelanggan Sering
                    </CardTitle>
                    <CardDescription>
                        Top 20 pelanggan berdasarkan jumlah kupon yang diterima dalam periode
                    </CardDescription>
                </CardHeader>
                <CardContent>
                    <div v-if="frequentCustomers.length === 0" class="py-8 text-center">
                        <Users class="mx-auto h-12 w-12 text-muted-foreground mb-4" />
                        <p class="text-sm text-muted-foreground">
                            Tidak ada data pelanggan dalam periode yang dipilih
                        </p>
                    </div>
                    <div v-else class="overflow-x-auto">
                        <table class="w-full border-collapse">
                            <thead>
                                <tr class="border-b border-border">
                                    <th class="text-left p-3 text-sm font-semibold text-muted-foreground">
                                        Nama Pelanggan
                                    </th>
                                    <th class="text-left p-3 text-sm font-semibold text-muted-foreground">
                                        No. Telepon
                                    </th>
                                    <th class="text-right p-3 text-sm font-semibold text-muted-foreground">
                                        Total Kupon
                                    </th>
                                    <th class="text-right p-3 text-sm font-semibold text-muted-foreground">
                                        Terpakai
                                    </th>
                                    <th class="text-right p-3 text-sm font-semibold text-muted-foreground">
                                        Tingkat Penggunaan
                                    </th>
                                    <th class="text-left p-3 text-sm font-semibold text-muted-foreground">
                                        Kupon Terakhir
                                    </th>
                                    <th class="text-center p-3 text-sm font-semibold text-muted-foreground">
                                        Aksi
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr
                                    v-for="(customer, index) in frequentCustomers"
                                    :key="index"
                                    class="border-b border-border/50 hover:bg-muted/50 transition-colors"
                                >
                                    <td class="p-3 text-sm font-medium">
                                        {{ customer.customer_name }}
                                    </td>
                                    <td class="p-3 text-sm text-muted-foreground">
                                        {{ customer.formatted_phone }}
                                    </td>
                                    <td class="p-3 text-sm text-right font-medium">
                                        {{ customer.total_coupons }}
                                    </td>
                                    <td class="p-3 text-sm text-right">
                                        {{ customer.total_used }}
                                    </td>
                                    <td class="p-3 text-sm text-right">
                                        <span
                                            :class="{
                                                'text-green-600 dark:text-green-400': customer.usage_rate >= 50,
                                                'text-orange-600 dark:text-orange-400': customer.usage_rate >= 25 && customer.usage_rate < 50,
                                                'text-red-600 dark:text-red-400': customer.usage_rate < 25,
                                            }"
                                            class="font-semibold"
                                        >
                                            {{ customer.usage_rate }}%
                                        </span>
                                    </td>
                                    <td class="p-3 text-sm text-muted-foreground">
                                        {{ formatDate(customer.last_coupon_date) }}
                                    </td>
                                    <td class="p-3 text-center">
                                        <Button
                                            variant="ghost"
                                            size="sm"
                                            @click="viewCustomerCoupons(customer.customer_phone)"
                                            class="gap-2"
                                        >
                                            <Eye class="h-4 w-4" />
                                            Lihat Kupon
                                        </Button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </CardContent>
            </Card>

            <!-- Daily Usage Chart Placeholder -->
            <Card class="border-2">
                <CardHeader>
                    <CardTitle class="flex items-center gap-2">
                        <BarChart3 class="h-5 w-5 text-primary" />
                        Grafik Penggunaan Harian
                    </CardTitle>
                    <CardDescription>
                        Visualisasi jumlah validasi kupon per hari (opsional)
                    </CardDescription>
                </CardHeader>
                <CardContent>
                    <div class="py-8 text-center">
                        <BarChart3 class="mx-auto h-12 w-12 text-muted-foreground mb-4" />
                        <p class="text-sm text-muted-foreground">
                            Grafik akan ditampilkan di sini (opsional untuk implementasi masa depan)
                        </p>
                        <p class="text-xs text-muted-foreground mt-2">
                            Data tersedia: {{ dailyUsage.length }} hari
                        </p>
                    </div>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>
