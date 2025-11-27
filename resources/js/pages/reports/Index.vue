<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import PageHeader from '@/components/PageHeader.vue';
import EmptyState from '@/components/EmptyState.vue';
import PullToRefresh from '@/components/PullToRefresh.vue';
import { useHaptic } from '@/composables/useHaptic';
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
    Eye,
    ArrowUpDown,
    ArrowUp,
    ArrowDown,
    ChevronLeft,
    ChevronRight
} from 'lucide-vue-next';
import { computed, ref, onMounted } from 'vue';
import { type BreadcrumbItem } from '@/types';
import CustomerCouponsModal from '@/components/CustomerCouponsModal.vue';
import DailyUsageChart from '@/components/DailyUsageChart.vue';

const { trigger } = useHaptic();
const isLoaded = ref(false);

onMounted(() => {
    requestAnimationFrame(() => {
        isLoaded.value = true;
    });
});

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
    topTypes: TopType[] | {
        data: TopType[];
        current_page: number;
        last_page: number;
        per_page: number;
        total: number;
    };
    dailyUsage: DailyUsage[];
    frequentCustomers: FrequentCustomer[] | {
        data: FrequentCustomer[];
        current_page: number;
        last_page: number;
        per_page: number;
        total: number;
    };
    filters: {
        date_from: string;
        date_to: string;
        top_types_page?: number;
        customers_page?: number;
        per_page?: number;
        top_types_sort?: string;
        top_types_direction?: string;
        customers_sort?: string;
        customers_direction?: string;
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
    top_types_page: props.filters.top_types_page || 1,
    customers_page: props.filters.customers_page || 1,
    per_page: props.filters.per_page || 10,
    top_types_sort: props.filters.top_types_sort || 'created_count',
    top_types_direction: props.filters.top_types_direction || 'desc',
    customers_sort: props.filters.customers_sort || 'total_coupons',
    customers_direction: props.filters.customers_direction || 'desc',
});

const isLoading = computed(() => form.processing);

const isTopTypesPaginated = computed(() => {
    return typeof props.topTypes === 'object' && 'data' in props.topTypes;
});

const isCustomersPaginated = computed(() => {
    return typeof props.frequentCustomers === 'object' && 'data' in props.frequentCustomers;
});

const topTypesData = computed(() => {
    return isTopTypesPaginated.value ? (props.topTypes as any).data : props.topTypes;
});

const customersData = computed(() => {
    return isCustomersPaginated.value ? (props.frequentCustomers as any).data : props.frequentCustomers;
});

const topTypesPagination = computed(() => {
    return isTopTypesPaginated.value ? props.topTypes as any : null;
});

const customersPagination = computed(() => {
    return isCustomersPaginated.value ? props.frequentCustomers as any : null;
});

const sortTopTypes = (column: string) => {
    const currentSort = form.top_types_sort || 'created_count';
    const currentDirection = form.top_types_direction || 'desc';
    const newDirection = currentSort === column && currentDirection === 'asc' ? 'desc' : 'asc';
    form.top_types_sort = column;
    form.top_types_direction = newDirection;
    form.top_types_page = 1; // Reset to first page when sorting
    applyFilters();
};

const sortCustomers = (column: string) => {
    const currentSort = form.customers_sort || 'total_coupons';
    const currentDirection = form.customers_direction || 'desc';
    const newDirection = currentSort === column && currentDirection === 'asc' ? 'desc' : 'asc';
    form.customers_sort = column;
    form.customers_direction = newDirection;
    form.customers_page = 1; // Reset to first page when sorting
    applyFilters();
};

const getTopTypesSortIcon = (column: string) => {
    const currentSort = form.top_types_sort;
    const currentDirection = form.top_types_direction;
    if (currentSort !== column) {
        return ArrowUpDown;
    }
    return currentDirection === 'asc' ? ArrowUp : ArrowDown;
};

const getCustomersSortIcon = (column: string) => {
    const currentSort = form.customers_sort;
    const currentDirection = form.customers_direction;
    if (currentSort !== column) {
        return ArrowUpDown;
    }
    return currentDirection === 'asc' ? ArrowUp : ArrowDown;
};

const buildPaginationQuery = (type: 'top_types' | 'customers', page: number): string => {
    const params = new URLSearchParams();
    params.set('date_from', form.date_from);
    params.set('date_to', form.date_to);
    params.set('per_page', String(form.per_page));

    if (type === 'top_types') {
        params.set('top_types_page', String(page));
        if (form.top_types_sort) params.set('top_types_sort', form.top_types_sort);
        if (form.top_types_direction) params.set('top_types_direction', form.top_types_direction);
        if (form.customers_page) params.set('customers_page', String(form.customers_page));
        if (form.customers_sort) params.set('customers_sort', form.customers_sort);
        if (form.customers_direction) params.set('customers_direction', form.customers_direction);
    } else {
        params.set('customers_page', String(page));
        if (form.top_types_page) params.set('top_types_page', String(form.top_types_page));
        if (form.top_types_sort) params.set('top_types_sort', form.top_types_sort);
        if (form.top_types_direction) params.set('top_types_direction', form.top_types_direction);
        if (form.customers_sort) params.set('customers_sort', form.customers_sort);
        if (form.customers_direction) params.set('customers_direction', form.customers_direction);
    }

    return params.toString();
};

const getPageNumbers = (pagination: any): (number | string)[] => {
    if (!pagination) return [];
    const current = pagination.current_page;
    const last = pagination.last_page;
    const pages: (number | string)[] = [];

    if (last <= 7) {
        for (let i = 1; i <= last; i++) {
            pages.push(i);
        }
    } else {
        if (current <= 3) {
            for (let i = 1; i <= 4; i++) pages.push(i);
            pages.push('...');
            pages.push(last);
        } else if (current >= last - 2) {
            pages.push(1);
            pages.push('...');
            for (let i = last - 3; i <= last; i++) pages.push(i);
        } else {
            pages.push(1);
            pages.push('...');
            for (let i = current - 1; i <= current + 1; i++) pages.push(i);
            pages.push('...');
            pages.push(last);
        }
    }

    return pages;
};

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
    form.top_types_page = 1;
    form.customers_page = 1;
    form.top_types_sort = 'created_count';
    form.top_types_direction = 'desc';
    form.customers_sort = 'total_coupons';
    form.customers_direction = 'desc';
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

const showCouponsModal = ref(false);
const selectedCustomer = ref<{ name: string; phone: string } | null>(null);

const viewCustomerCoupons = (customer: FrequentCustomer) => {
    selectedCustomer.value = {
        name: customer.customer_name,
        phone: customer.customer_phone,
    };
    showCouponsModal.value = true;
};
</script>

<template>
    <Head title="Laporan" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <PullToRefresh>
            <div class="flex h-full flex-1 flex-col gap-4 sm:gap-6 overflow-x-auto p-4 md:p-6">
                <!-- Header with spring animation -->
                <div
                    :class="[
                        'transition-all duration-500',
                        isLoaded ? 'translate-y-0 opacity-100' : 'translate-y-4 opacity-0',
                    ]"
                >
                    <PageHeader
                        title="Laporan & Analitik"
                        description="Analisis penggunaan kupon dan statistik bisnis"
                    />
                </div>

            <!-- Date Range Filter with spring animation -->
            <Card
                :class="[
                    'border rounded-xl',
                    'transition-all duration-500 delay-100',
                    isLoaded ? 'translate-y-0 opacity-100' : 'translate-y-4 opacity-0',
                ]"
            >
                <CardHeader class="pb-4">
                    <div class="flex items-center gap-2">
                        <BarChart3 class="h-5 w-5 text-primary" />
                        <CardTitle class="text-lg font-semibold">Filter Periode</CardTitle>
                    </div>
                    <CardDescription class="text-sm mt-1">
                        Pilih rentang tanggal untuk melihat statistik
                    </CardDescription>
                </CardHeader>
                <CardContent>
                    <form @submit.prevent="applyFilters" class="space-y-4">
                        <div class="flex flex-col gap-4 sm:flex-row sm:items-end">
                            <div class="flex-1 space-y-2">
                                <Label for="date_from" class="text-sm font-medium">Dari Tanggal</Label>
                                <Input
                                    id="date_from"
                                    v-model="form.date_from"
                                    type="date"
                                    :disabled="isLoading"
                                    class="w-full h-11 text-base rounded-xl ios-input-focus md:h-10 md:text-sm"
                                    @change="form.top_types_page = 1; form.customers_page = 1; applyFilters()"
                                />
                            </div>
                            <div class="flex-1 space-y-2">
                                <Label for="date_to" class="text-sm font-medium">Sampai Tanggal</Label>
                                <Input
                                    id="date_to"
                                    v-model="form.date_to"
                                    type="date"
                                    :disabled="isLoading"
                                    class="w-full h-11 text-base rounded-xl ios-input-focus md:h-10 md:text-sm"
                                    @change="form.top_types_page = 1; form.customers_page = 1; applyFilters()"
                                />
                            </div>
                            <div class="flex gap-2">
                                <Button
                                    type="button"
                                    variant="outline"
                                    @click="trigger('light'); resetFilters()"
                                    :disabled="isLoading"
                                    class="h-11 w-full sm:w-auto rounded-xl press-effect"
                                >
                                    Reset
                                </Button>
                            </div>
                        </div>
                        <div v-if="isLoading" class="flex items-center gap-2 text-sm text-muted-foreground">
                            <Loader2 class="h-4 w-4 animate-spin" />
                            <span>Memuat data...</span>
                        </div>
                    </form>
                </CardContent>
            </Card>

            <!-- Summary Stats Cards with staggered spring animation -->
            <div class="grid gap-4 grid-cols-2 sm:grid-cols-3 md:grid-cols-3 lg:grid-cols-5">
                <Card
                    v-for="(stat, index) in statCards"
                    :key="index"
                    :class="[
                        'border rounded-xl card-hover mobile-card-press',
                        'transition-all duration-500',
                        isLoaded ? 'animate-spring-up' : 'opacity-0',
                    ]"
                    :style="{ animationDelay: `${200 + index * 50}ms` }"
                >
                    <CardContent class="p-4 sm:p-6">
                        <div class="flex items-start justify-between mb-3">
                            <div :class="[stat.bgColor, 'rounded-xl p-2.5']">
                                <component
                                    :is="stat.icon"
                                    :class="[stat.color, 'h-5 w-5']"
                                />
                            </div>
                        </div>
                        <div class="text-2xl sm:text-3xl font-bold mb-1">{{ stat.value }}</div>
                        <CardTitle class="text-xs sm:text-sm font-medium text-muted-foreground mb-1">
                            {{ stat.title }}
                        </CardTitle>
                        <p class="text-xs text-muted-foreground leading-relaxed">
                            {{ stat.description }}
                        </p>
                    </CardContent>
                </Card>
            </div>

            <!-- Top Coupon Types Table with spring animation -->
            <Card
                :class="[
                    'border rounded-xl',
                    'transition-all duration-500 delay-300',
                    isLoaded ? 'translate-y-0 opacity-100' : 'translate-y-4 opacity-0',
                ]"
            >
                <CardHeader class="pb-4">
                    <div class="flex items-center justify-between">
                        <div>
                            <div class="flex items-center gap-2">
                                <TrendingUp class="h-5 w-5 text-primary" />
                                <CardTitle class="text-lg font-semibold">Top Tipe Kupon</CardTitle>
                            </div>
                            <CardDescription class="text-sm mt-1">
                                Ranking tipe kupon berdasarkan jumlah dibuat dan digunakan
                            </CardDescription>
                        </div>
                        <div class="flex flex-col gap-2 sm:flex-row">
                            <Button
                                variant="outline"
                                size="sm"
                                @click="trigger('medium'); exportToExcel()"
                                :disabled="isExporting"
                                class="w-full sm:w-auto gap-2 rounded-xl press-effect"
                            >
                                <Loader2 v-if="isExporting && exportFormat === 'xlsx'" class="h-4 w-4 animate-spin" />
                                <FileSpreadsheet v-else class="h-4 w-4" />
                                {{ isExporting && exportFormat === 'xlsx' ? 'Mengekspor...' : 'Excel' }}
                            </Button>
                            <Button
                                variant="outline"
                                size="sm"
                                @click="trigger('medium'); exportToCSV()"
                                :disabled="isExporting"
                                class="w-full sm:w-auto gap-2 rounded-xl press-effect"
                            >
                                <Loader2 v-if="isExporting && exportFormat === 'csv'" class="h-4 w-4 animate-spin" />
                                <FileText v-else class="h-4 w-4" />
                                {{ isExporting && exportFormat === 'csv' ? 'Mengekspor...' : 'CSV' }}
                            </Button>
                        </div>
                    </div>
                </CardHeader>
                <CardContent>
                    <EmptyState
                        v-if="topTypesData.length === 0"
                        :icon="Ticket"
                        title="Tidak ada data kupon"
                        description="Tidak ada data kupon dalam periode yang dipilih"
                    />
                    <template v-else>
                        <!-- Mobile View: Cards with staggered animation -->
                        <div class="block space-y-3 md:hidden">
                            <Card
                                v-for="(type, index) in topTypesData"
                                :key="index"
                                :class="[
                                    'border rounded-xl p-4 mobile-card-press',
                                    isLoaded ? 'animate-spring-up' : 'opacity-0',
                                ]"
                                :style="{ animationDelay: `${350 + index * 50}ms` }"
                            >
                                <div class="space-y-3">
                                    <div class="flex items-start justify-between">
                                        <div class="flex-1">
                                            <p class="font-semibold text-foreground">{{ type.type }}</p>
                                        </div>
                                    </div>
                                    <div class="grid grid-cols-2 gap-3 text-sm">
                                        <div>
                                            <p class="text-muted-foreground">Dibuat</p>
                                            <p class="font-medium">{{ type.created_count }}</p>
                                        </div>
                                        <div>
                                            <p class="text-muted-foreground">Terpakai</p>
                                            <p class="font-medium">{{ type.used_count }}</p>
                                        </div>
                                        <div>
                                            <p class="text-muted-foreground">Kedaluwarsa</p>
                                            <p class="font-medium text-red-600">{{ type.expired_count }}</p>
                                        </div>
                                        <div>
                                            <p class="text-muted-foreground">Tingkat</p>
                                            <p class="font-semibold" :class="{
                                                'text-green-600': type.usage_rate >= 50,
                                                'text-orange-600': type.usage_rate >= 25 && type.usage_rate < 50,
                                                'text-red-600': type.usage_rate < 25,
                                            }">
                                                {{ type.usage_rate }}%
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </Card>
                        </div>
                        <!-- Desktop View: Table -->
                        <div class="hidden md:block overflow-x-auto">
                        <table class="w-full border-collapse">
                            <thead>
                                <tr class="border-b border-border">
                                    <th class="text-left p-3 text-sm font-semibold text-muted-foreground">
                                        <button
                                            @click="sortTopTypes('type')"
                                            class="flex items-center gap-2 hover:text-foreground transition-colors"
                                        >
                                            Tipe Kupon
                                            <component :is="getTopTypesSortIcon('type')" class="h-3 w-3" />
                                        </button>
                                    </th>
                                    <th class="text-right p-3 text-sm font-semibold text-muted-foreground">
                                        <button
                                            @click="sortTopTypes('created_count')"
                                            class="flex items-center justify-end gap-2 hover:text-foreground transition-colors ml-auto"
                                        >
                                            Dibuat
                                            <component :is="getTopTypesSortIcon('created_count')" class="h-3 w-3" />
                                        </button>
                                    </th>
                                    <th class="text-right p-3 text-sm font-semibold text-muted-foreground">
                                        <button
                                            @click="sortTopTypes('used_count')"
                                            class="flex items-center justify-end gap-2 hover:text-foreground transition-colors ml-auto"
                                        >
                                            Terpakai
                                            <component :is="getTopTypesSortIcon('used_count')" class="h-3 w-3" />
                                        </button>
                                    </th>
                                    <th class="text-right p-3 text-sm font-semibold text-muted-foreground">
                                        <button
                                            @click="sortTopTypes('expired_count')"
                                            class="flex items-center justify-end gap-2 hover:text-foreground transition-colors ml-auto"
                                        >
                                            Kedaluwarsa
                                            <component :is="getTopTypesSortIcon('expired_count')" class="h-3 w-3" />
                                        </button>
                                    </th>
                                    <th class="text-right p-3 text-sm font-semibold text-muted-foreground">
                                        <button
                                            @click="sortTopTypes('usage_rate')"
                                            class="flex items-center justify-end gap-2 hover:text-foreground transition-colors ml-auto"
                                        >
                                            Tingkat Penggunaan
                                            <component :is="getTopTypesSortIcon('usage_rate')" class="h-3 w-3" />
                                        </button>
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr
                                    v-for="(type, index) in topTypesData"
                                    :key="index"
                                    :class="[
                                        'border-b border-border/50 list-row-interactive',
                                        isLoaded ? 'animate-fade-up' : 'opacity-0',
                                    ]"
                                    :style="{ animationDelay: `${350 + index * 30}ms` }"
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
                    </template>

                    <!-- Pagination for Top Types -->
                    <div v-if="topTypesPagination && topTypesPagination.last_page > 1" class="border-t p-4">
                        <div class="flex flex-col items-center justify-between gap-4 sm:flex-row">
                            <p class="text-xs sm:text-sm text-muted-foreground text-center sm:text-left">
                                Menampilkan {{ (topTypesPagination.current_page - 1) * topTypesPagination.per_page + 1 }} sampai
                                {{ Math.min(topTypesPagination.current_page * topTypesPagination.per_page, topTypesPagination.total) }} dari
                                {{ topTypesPagination.total }} tipe
                            </p>
                            <div class="flex items-center gap-1 sm:gap-2">
                                <Button
                                    variant="outline"
                                    size="sm"
                                    class="h-9 w-9 rounded-xl p-0 active:scale-[0.98] transition-transform"
                                    :disabled="topTypesPagination.current_page === 1"
                                    @click="router.get(`/reports?${buildPaginationQuery('top_types', topTypesPagination.current_page - 1)}`, { preserveState: true, preserveScroll: true })"
                                >
                                    <ChevronLeft class="h-4 w-4" />
                                </Button>

                                <div class="hidden xs:flex gap-1">
                                    <template v-for="page in getPageNumbers(topTypesPagination)" :key="page">
                                        <Button
                                            v-if="page !== '...'"
                                            variant="outline"
                                            size="sm"
                                            class="h-9 min-w-[2.5rem] rounded-xl text-xs active:scale-[0.98] transition-transform"
                                            :class="{ 'bg-primary text-primary-foreground': page === topTypesPagination.current_page }"
                                            @click="router.get(`/reports?${buildPaginationQuery('top_types', page as number)}`, { preserveState: true, preserveScroll: true })"
                                        >
                                            {{ page }}
                                        </Button>
                                        <span v-else class="px-2 py-1 text-xs text-muted-foreground">...</span>
                                    </template>
                                </div>

                                <Button
                                    variant="outline"
                                    size="sm"
                                    class="h-9 w-9 rounded-xl p-0 active:scale-[0.98] transition-transform"
                                    :disabled="topTypesPagination.current_page === topTypesPagination.last_page"
                                    @click="router.get(`/reports?${buildPaginationQuery('top_types', topTypesPagination.current_page + 1)}`, { preserveState: true, preserveScroll: true })"
                                >
                                    <ChevronRight class="h-4 w-4" />
                                </Button>
                            </div>
                        </div>
                    </div>
                </CardContent>
            </Card>

            <!-- Frequent Customers Table with spring animation -->
            <Card
                :class="[
                    'border rounded-xl',
                    'transition-all duration-500 delay-400',
                    isLoaded ? 'translate-y-0 opacity-100' : 'translate-y-4 opacity-0',
                ]"
            >
                <CardHeader class="pb-4">
                    <div class="flex items-center gap-2">
                        <Users class="h-5 w-5 text-primary" />
                        <CardTitle class="text-lg font-semibold">Pelanggan Sering</CardTitle>
                    </div>
                    <CardDescription class="text-sm mt-1">
                        Top 20 pelanggan berdasarkan jumlah kupon yang diterima dalam periode
                    </CardDescription>
                </CardHeader>
                <CardContent>
                    <EmptyState
                        v-if="customersData.length === 0"
                        :icon="Users"
                        title="Tidak ada data pelanggan"
                        description="Tidak ada data pelanggan dalam periode yang dipilih"
                    />
                    <template v-else>
                        <!-- Mobile View: Cards with staggered animation -->
                        <div class="block space-y-3 md:hidden">
                            <Card
                                v-for="(customer, index) in customersData"
                                :key="index"
                                :class="[
                                    'border rounded-xl p-4 mobile-card-press',
                                    isLoaded ? 'animate-spring-up' : 'opacity-0',
                                ]"
                                :style="{ animationDelay: `${450 + index * 50}ms` }"
                            >
                                <div class="space-y-3">
                                    <div>
                                        <p class="font-semibold text-foreground">{{ customer.customer_name }}</p>
                                        <p class="text-sm text-muted-foreground mt-1">{{ customer.formatted_phone }}</p>
                                    </div>
                                    <div class="grid grid-cols-2 gap-3 text-sm">
                                        <div>
                                            <p class="text-muted-foreground">Total Kupon</p>
                                            <p class="font-medium">{{ customer.total_coupons }}</p>
                                        </div>
                                        <div>
                                            <p class="text-muted-foreground">Terpakai</p>
                                            <p class="font-medium">{{ customer.total_used }}</p>
                                        </div>
                                        <div>
                                            <p class="text-muted-foreground">Tingkat</p>
                                            <p class="font-semibold" :class="{
                                                'text-green-600': customer.usage_rate >= 50,
                                                'text-orange-600': customer.usage_rate >= 25 && customer.usage_rate < 50,
                                                'text-red-600': customer.usage_rate < 25,
                                            }">
                                                {{ customer.usage_rate }}%
                                            </p>
                                        </div>
                                        <div>
                                            <p class="text-muted-foreground">Terakhir</p>
                                            <p class="font-medium text-xs">{{ formatDate(customer.last_coupon_date) }}</p>
                                        </div>
                                    </div>
                                    <Button
                                        variant="outline"
                                        size="sm"
                                        @click="trigger('light'); viewCustomerCoupons(customer)"
                                        class="w-full gap-2 rounded-xl press-effect"
                                    >
                                        <Eye class="h-4 w-4" />
                                        Lihat Kupon
                                    </Button>
                                </div>
                            </Card>
                        </div>
                        <!-- Desktop View: Table -->
                        <div class="hidden md:block overflow-x-auto">
                        <table class="w-full border-collapse">
                            <thead>
                                <tr class="border-b border-border">
                                    <th class="text-left p-3 text-sm font-semibold text-muted-foreground">
                                        <button
                                            @click="sortCustomers('customer_name')"
                                            class="flex items-center gap-2 hover:text-foreground transition-colors"
                                        >
                                            Nama Pelanggan
                                            <component :is="getCustomersSortIcon('customer_name')" class="h-3 w-3" />
                                        </button>
                                    </th>
                                    <th class="text-left p-3 text-sm font-semibold text-muted-foreground">
                                        No. Telepon
                                    </th>
                                    <th class="text-right p-3 text-sm font-semibold text-muted-foreground">
                                        <button
                                            @click="sortCustomers('total_coupons')"
                                            class="flex items-center justify-end gap-2 hover:text-foreground transition-colors ml-auto"
                                        >
                                            Total Kupon
                                            <component :is="getCustomersSortIcon('total_coupons')" class="h-3 w-3" />
                                        </button>
                                    </th>
                                    <th class="text-right p-3 text-sm font-semibold text-muted-foreground">
                                        <button
                                            @click="sortCustomers('total_used')"
                                            class="flex items-center justify-end gap-2 hover:text-foreground transition-colors ml-auto"
                                        >
                                            Terpakai
                                            <component :is="getCustomersSortIcon('total_used')" class="h-3 w-3" />
                                        </button>
                                    </th>
                                    <th class="text-right p-3 text-sm font-semibold text-muted-foreground">
                                        <button
                                            @click="sortCustomers('usage_rate')"
                                            class="flex items-center justify-end gap-2 hover:text-foreground transition-colors ml-auto"
                                        >
                                            Tingkat Penggunaan
                                            <component :is="getCustomersSortIcon('usage_rate')" class="h-3 w-3" />
                                        </button>
                                    </th>
                                    <th class="text-left p-3 text-sm font-semibold text-muted-foreground">
                                        <button
                                            @click="sortCustomers('last_coupon_date')"
                                            class="flex items-center gap-2 hover:text-foreground transition-colors"
                                        >
                                            Kupon Terakhir
                                            <component :is="getCustomersSortIcon('last_coupon_date')" class="h-3 w-3" />
                                        </button>
                                    </th>
                                    <th class="text-center p-3 text-sm font-semibold text-muted-foreground">
                                        Aksi
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr
                                    v-for="(customer, index) in customersData"
                                    :key="index"
                                    :class="[
                                        'border-b border-border/50 list-row-interactive',
                                        isLoaded ? 'animate-fade-up' : 'opacity-0',
                                    ]"
                                    :style="{ animationDelay: `${450 + index * 30}ms` }"
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
                                            @click="trigger('light'); viewCustomerCoupons(customer)"
                                            class="gap-2 rounded-xl press-effect"
                                        >
                                            <Eye class="h-4 w-4" />
                                            Lihat Kupon
                                        </Button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                        </div>
                    </template>

                    <!-- Pagination for Frequent Customers -->
                    <div v-if="customersPagination && customersPagination.last_page > 1" class="border-t p-4">
                        <div class="flex flex-col items-center justify-between gap-4 sm:flex-row">
                            <p class="text-xs sm:text-sm text-muted-foreground text-center sm:text-left">
                                Menampilkan {{ (customersPagination.current_page - 1) * customersPagination.per_page + 1 }} sampai
                                {{ Math.min(customersPagination.current_page * customersPagination.per_page, customersPagination.total) }} dari
                                {{ customersPagination.total }} pelanggan
                            </p>
                            <div class="flex items-center gap-1 sm:gap-2">
                                <Button
                                    variant="outline"
                                    size="sm"
                                    class="h-9 w-9 rounded-xl p-0 active:scale-[0.98] transition-transform"
                                    :disabled="customersPagination.current_page === 1"
                                    @click="router.get(`/reports?${buildPaginationQuery('customers', customersPagination.current_page - 1)}`, { preserveState: true, preserveScroll: true })"
                                >
                                    <ChevronLeft class="h-4 w-4" />
                                </Button>

                                <div class="hidden xs:flex gap-1">
                                    <template v-for="page in getPageNumbers(customersPagination)" :key="page">
                                        <Button
                                            v-if="page !== '...'"
                                            variant="outline"
                                            size="sm"
                                            class="h-9 min-w-[2.5rem] rounded-xl text-xs active:scale-[0.98] transition-transform"
                                            :class="{ 'bg-primary text-primary-foreground': page === customersPagination.current_page }"
                                            @click="router.get(`/reports?${buildPaginationQuery('customers', page as number)}`, { preserveState: true, preserveScroll: true })"
                                        >
                                            {{ page }}
                                        </Button>
                                        <span v-else class="px-2 py-1 text-xs text-muted-foreground">...</span>
                                    </template>
                                </div>

                                <Button
                                    variant="outline"
                                    size="sm"
                                    class="h-9 w-9 rounded-xl p-0 active:scale-[0.98] transition-transform"
                                    :disabled="customersPagination.current_page === customersPagination.last_page"
                                    @click="router.get(`/reports?${buildPaginationQuery('customers', customersPagination.current_page + 1)}`, { preserveState: true, preserveScroll: true })"
                                >
                                    <ChevronRight class="h-4 w-4" />
                                </Button>
                            </div>
                        </div>
                    </div>
                </CardContent>
            </Card>

            <!-- Daily Usage Chart with spring animation -->
            <Card
                :class="[
                    'border rounded-xl',
                    'transition-all duration-500 delay-500',
                    isLoaded ? 'translate-y-0 opacity-100' : 'translate-y-4 opacity-0',
                ]"
            >
                <CardHeader class="pb-4">
                    <div class="flex items-center gap-2">
                        <BarChart3 class="h-5 w-5 text-primary" />
                        <CardTitle class="text-lg font-semibold">Grafik Penggunaan Harian</CardTitle>
                    </div>
                    <CardDescription class="text-sm mt-1">
                        Visualisasi jumlah validasi kupon per hari (opsional)
                    </CardDescription>
                </CardHeader>
                <CardContent>
                    <EmptyState
                        v-if="dailyUsage.length === 0"
                        :icon="BarChart3"
                        title="Tidak ada data"
                        description="Tidak ada data validasi kupon dalam periode yang dipilih"
                    />
                    <DailyUsageChart v-else :data="dailyUsage" />
                </CardContent>
            </Card>
            </div>
        </PullToRefresh>

        <!-- Customer Coupons Modal -->
        <CustomerCouponsModal
            v-if="selectedCustomer"
            :is-open="showCouponsModal"
            :customer-name="selectedCustomer.name"
            :customer-phone="selectedCustomer.phone"
            @update:is-open="showCouponsModal = $event"
        />
    </AppLayout>
</template>
