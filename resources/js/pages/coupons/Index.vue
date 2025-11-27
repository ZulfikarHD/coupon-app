<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import PageHeader from '@/components/PageHeader.vue';
import EmptyState from '@/components/EmptyState.vue';
import StatusBadge from '@/components/StatusBadge.vue';
import PullToRefresh from '@/components/PullToRefresh.vue';
import { useStatusColors } from '@/composables/useStatusColors';
import { useHaptic } from '@/composables/useHaptic';
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Badge } from '@/components/ui/badge';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Checkbox } from '@/components/ui/checkbox';
import { Label } from '@/components/ui/label';
import { Collapsible, CollapsibleContent, CollapsibleTrigger } from '@/components/ui/collapsible';
import { Plus, Search, Filter, Eye, X, ChevronDown, ChevronUp, Ticket, ArrowUpDown, ArrowUp, ArrowDown, ChevronLeft, ChevronRight } from 'lucide-vue-next';
import { computed, ref, onMounted } from 'vue';

interface Coupon {
    id: number;
    code: string;
    type: string;
    customer_name: string;
    first_name?: string;
    last_name?: string;
    customer_phone: string;
    status: 'active' | 'used' | 'expired';
    created_at: string;
    expires_at: string | null;
    formatted_phone?: string;
}

interface Props {
    coupons: {
        data: Coupon[];
        current_page: number;
        last_page: number;
        per_page: number;
        total: number;
    };
    filters: {
        status?: string | string[];
        search?: string;
        customer_name?: string;
        first_name?: string;
        last_name?: string;
        customer_phone?: string;
        coupon_type?: string;
        date_from?: string;
        date_to?: string;
        expires_from?: string;
        expires_to?: string;
        sort?: string;
        direction?: string;
    };
}

const props = defineProps<Props>();

// iOS Enhancements
const { trigger } = useHaptic();
const isLoaded = ref(false);

onMounted(() => {
    requestAnimationFrame(() => {
        isLoaded.value = true;
    });
});

// Parse status filter - can be string or array
const parseStatusFilter = (status: string | string[] | undefined): string[] => {
    if (!status) return [];
    if (Array.isArray(status)) return status;
    if (status === 'all') return [];
    return [status];
};

const form = useForm({
    status: parseStatusFilter(props.filters.status),
    search: props.filters.search || '',
    customer_name: props.filters.customer_name || '',
    first_name: props.filters.first_name || '',
    last_name: props.filters.last_name || '',
    customer_phone: props.filters.customer_phone || '',
    coupon_type: props.filters.coupon_type || '',
    date_from: props.filters.date_from || '',
    date_to: props.filters.date_to || '',
    expires_from: props.filters.expires_from || '',
    expires_to: props.filters.expires_to || '',
    sort: props.filters.sort || 'created_at',
    direction: props.filters.direction || 'desc',
});

const showAdvancedSearch = ref(false);
const { statusLabels } = useStatusColors();

const currentSort = computed(() => props.filters.sort || 'created_at');
const currentDirection = computed(() => props.filters.direction || 'desc');

const sort = (column: string) => {
    trigger('light');
    const newDirection = currentSort.value === column && currentDirection.value === 'asc' ? 'desc' : 'asc';
    form.sort = column;
    form.direction = newDirection;
    applyFilters();
};

const getSortIcon = (column: string) => {
    if (currentSort.value !== column) {
        return ArrowUpDown;
    }
    return currentDirection.value === 'asc' ? ArrowUp : ArrowDown;
};

const statusOptions = [
    { value: 'active', label: 'Aktif' },
    { value: 'used', label: 'Terpakai' },
    { value: 'expired', label: 'Kedaluwarsa' },
];

const toggleStatus = (status: string, checked: boolean) => {
    trigger('light');
    const currentStatus = Array.isArray(form.status) ? form.status : [];

    if (checked) {
        // Add status if not already present
        if (!currentStatus.includes(status)) {
            form.status = [...currentStatus, status];
        }
    } else {
        // Remove status if present
        form.status = currentStatus.filter((s) => s !== status);
    }

    applyFilters();
};

const applyFilters = () => {
    const queryString = buildQueryString();
    const url = queryString ? `/coupons?${queryString}` : '/coupons';

    router.visit(url, {
        preserveState: true,
        preserveScroll: true,
        replace: true,
    });
};

const clearFilters = () => {
    trigger('medium');
    form.reset();
    form.status = [];
    form.search = '';
    form.customer_name = '';
    form.first_name = '';
    form.last_name = '';
    form.customer_phone = '';
    form.coupon_type = '';
    form.date_from = '';
    form.date_to = '';
    form.expires_from = '';
    form.expires_to = '';
    router.get('/coupons', {}, {
        preserveState: true,
        preserveScroll: true,
        replace: true,
    });
};

const hasActiveFilters = computed(() => {
    return (
        (Array.isArray(form.status) && form.status.length > 0) ||
        form.search ||
        form.customer_name ||
        form.first_name ||
        form.last_name ||
        form.customer_phone ||
        form.coupon_type ||
        form.date_from ||
        form.date_to ||
        form.expires_from ||
        form.expires_to
    );
});

const activeFilterBadges = computed(() => {
    const badges: Array<{ label: string; key: string }> = [];

    if (Array.isArray(form.status) && form.status.length > 0) {
        form.status.forEach((s) => {
            badges.push({ label: statusLabels[s as keyof typeof statusLabels], key: `status-${s}` });
        });
    }

    if (form.search) {
        badges.push({ label: `Cari: ${form.search}`, key: 'search' });
    }

    if (form.customer_name) {
        badges.push({ label: `Nama: ${form.customer_name}`, key: 'customer_name' });
    }

    if (form.first_name) {
        badges.push({ label: `Nama Depan: ${form.first_name}`, key: 'first_name' });
    }

    if (form.last_name) {
        badges.push({ label: `Nama Belakang: ${form.last_name}`, key: 'last_name' });
    }

    if (form.customer_phone) {
        badges.push({ label: `Telepon: ${form.customer_phone}`, key: 'customer_phone' });
    }

    if (form.coupon_type) {
        badges.push({ label: `Jenis: ${form.coupon_type}`, key: 'coupon_type' });
    }

    if (form.date_from) {
        badges.push({ label: `Dibuat dari: ${form.date_from}`, key: 'date_from' });
    }

    if (form.date_to) {
        badges.push({ label: `Dibuat sampai: ${form.date_to}`, key: 'date_to' });
    }

    if (form.expires_from) {
        badges.push({ label: `Kadaluwarsa dari: ${form.expires_from}`, key: 'expires_from' });
    }

    if (form.expires_to) {
        badges.push({ label: `Kadaluwarsa sampai: ${form.expires_to}`, key: 'expires_to' });
    }

    return badges;
});

const removeFilter = (key: string) => {
    trigger('light');
    if (key.startsWith('status-')) {
        const status = key.replace('status-', '');
        const currentStatus = Array.isArray(form.status) ? form.status : [];
        form.status = currentStatus.filter((s) => s !== status);
        applyFilters();
    } else {
        (form as any)[key] = '';
        applyFilters();
    }
};

const buildQueryString = (page?: number): string => {
    const searchParams = new URLSearchParams();

    // Use array notation for multiple status values so Laravel can parse them properly
    if (Array.isArray(form.status) && form.status.length > 0) {
        form.status.forEach((s) => searchParams.append('status[]', s));
    }
    if (form.search) searchParams.set('search', form.search);
    if (form.customer_name) searchParams.set('customer_name', form.customer_name);
    if (form.first_name) searchParams.set('first_name', form.first_name);
    if (form.last_name) searchParams.set('last_name', form.last_name);
    if (form.customer_phone) searchParams.set('customer_phone', form.customer_phone);
    if (form.coupon_type) searchParams.set('coupon_type', form.coupon_type);
    if (form.date_from) searchParams.set('date_from', form.date_from);
    if (form.date_to) searchParams.set('date_to', form.date_to);
    if (form.expires_from) searchParams.set('expires_from', form.expires_from);
    if (form.expires_to) searchParams.set('expires_to', form.expires_to);
    if (form.sort) searchParams.set('sort', form.sort);
    if (form.direction) searchParams.set('direction', form.direction);
    if (page) searchParams.set('page', String(page));

    return searchParams.toString();
};

const getPageNumbers = (): (number | string)[] => {
    const current = props.coupons.current_page;
    const last = props.coupons.last_page;
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

// Stagger delay for list items
const getStaggerClass = (index: number): string => {
    const staggerIndex = Math.min(index + 1, 15);
    return `stagger-${staggerIndex}`;
};

const breadcrumbs = [
    {
        title: 'Kupon',
        href: '/coupons',
    },
];

// Handle card click with haptic
const handleCardClick = () => {
    trigger('light');
};

// Handle pagination click
const handlePaginationClick = (page: number | string) => {
    if (typeof page === 'number') {
        trigger('light');
        router.visit(`/coupons?${buildQueryString(page)}`, { preserveState: true, preserveScroll: true });
    }
};
</script>

<template>
    <Head title="Semua Kupon" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <PullToRefresh>
            <div class="flex h-full flex-1 flex-col gap-4 sm:gap-6 overflow-x-auto p-4 md:p-6">
                <!-- Header with spring animation -->
                <div 
                    :class="[
                        'flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between',
                        'transition-all duration-500',
                        isLoaded ? 'translate-y-0 opacity-100' : 'translate-y-4 opacity-0',
                    ]"
                >
                    <PageHeader
                        title="Semua Kupon"
                        description="Kelola dan lihat semua kupon yang telah dibuat"
                    />
                    <Button
                        as-child
                        size="lg"
                        class="h-12 w-full gap-2 rounded-xl press-effect btn-gradient sm:w-auto sm:h-11"
                    >
                        <Link href="/coupons/create" @click="trigger('medium')">
                            <Plus class="h-4 w-4" />
                            Buat Kupon Baru
                        </Link>
                    </Button>
                </div>

                <!-- Filters Card with spring entrance -->
                <Card 
                    :class="[
                        'border rounded-xl',
                        'transition-all duration-500 delay-100',
                        isLoaded ? 'translate-y-0 opacity-100' : 'translate-y-4 opacity-0',
                    ]"
                >
                    <CardHeader class="pb-4">
                        <div class="flex items-center gap-2">
                            <Filter class="h-5 w-5 text-primary" />
                            <CardTitle class="text-lg font-semibold">Filter & Pencarian</CardTitle>
                        </div>
                    </CardHeader>
                    <CardContent>
                        <form @submit.prevent="applyFilters" class="space-y-4">
                            <!-- Basic Search -->
                            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                                <!-- Search -->
                                <div class="space-y-2 sm:col-span-2 lg:col-span-1">
                                    <Label class="text-sm font-medium">Cari</Label>
                                    <div class="relative form-field-focus">
                                        <Search class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-muted-foreground" />
                                        <Input
                                            v-model="form.search"
                                            type="text"
                                            placeholder="Kode, nama, telepon, atau jenis..."
                                            class="h-11 pl-10 text-base rounded-xl ios-input-focus md:h-10 md:text-sm"
                                            @input="applyFilters"
                                        />
                                    </div>
                                </div>

                                <!-- Status Filter (Quick) -->
                                <div class="space-y-2 sm:col-span-2 lg:col-span-1">
                                    <Label class="text-sm font-medium">Status</Label>
                                    <div class="flex flex-col gap-2 sm:flex-row sm:flex-wrap">
                                        <label
                                            v-for="option in statusOptions"
                                            :key="option.value"
                                            class="flex cursor-pointer items-center gap-2 rounded-xl border px-4 py-3 text-sm transition-all duration-200 press-effect"
                                            :class="{
                                                'bg-primary text-primary-foreground border-primary shadow-sm': Array.isArray(form.status) && form.status.includes(option.value),
                                                'hover:bg-muted hover:border-muted-foreground/20': !(Array.isArray(form.status) && form.status.includes(option.value)),
                                            }"
                                        >
                                            <input
                                                type="checkbox"
                                                :value="option.value"
                                                :checked="Array.isArray(form.status) && form.status.includes(option.value)"
                                                @change="toggleStatus(option.value, ($event.target as HTMLInputElement).checked)"
                                                class="sr-only peer"
                                            />
                                            <div
                                                class="peer-checked:bg-primary peer-checked:text-primary-foreground peer-checked:border-primary border-input flex size-4 shrink-0 items-center justify-center rounded-[4px] border shadow-xs transition-colors"
                                            >
                                                <svg
                                                    v-if="Array.isArray(form.status) && form.status.includes(option.value)"
                                                    xmlns="http://www.w3.org/2000/svg"
                                                    width="14"
                                                    height="14"
                                                    viewBox="0 0 24 24"
                                                    fill="none"
                                                    stroke="currentColor"
                                                    stroke-width="3"
                                                    stroke-linecap="round"
                                                    stroke-linejoin="round"
                                                >
                                                    <polyline points="20 6 9 17 4 12" />
                                                </svg>
                                            </div>
                                            <span>{{ option.label }}</span>
                                        </label>
                                    </div>
                                </div>

                                <!-- Date From -->
                                <div class="space-y-2">
                                    <Label class="text-sm font-medium">Dibuat Dari</Label>
                                    <Input
                                        v-model="form.date_from"
                                        type="date"
                                        class="h-11 text-base rounded-xl ios-input-focus md:h-10 md:text-sm"
                                        @change="applyFilters"
                                    />
                                </div>

                                <!-- Date To -->
                                <div class="space-y-2">
                                    <Label class="text-sm font-medium">Dibuat Sampai</Label>
                                    <Input
                                        v-model="form.date_to"
                                        type="date"
                                        class="h-11 text-base rounded-xl ios-input-focus md:h-10 md:text-sm"
                                        @change="applyFilters"
                                    />
                                </div>
                            </div>

                            <!-- Advanced Search Collapsible -->
                            <Collapsible v-model="showAdvancedSearch">
                                <CollapsibleTrigger as-child>
                                    <Button
                                        type="button"
                                        variant="outline"
                                        class="w-full justify-between rounded-xl press-effect"
                                        @click="trigger('light')"
                                    >
                                        <span>Pencarian Lanjutan</span>
                                        <ChevronDown v-if="!showAdvancedSearch" class="h-4 w-4" />
                                        <ChevronUp v-else class="h-4 w-4" />
                                    </Button>
                                </CollapsibleTrigger>
                                <CollapsibleContent class="mt-4 space-y-4 animate-spring-up">
                                    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                                        <!-- Customer Name -->
                                        <div class="space-y-2">
                                            <Label for="customer_name" class="text-sm font-medium">Nama Pelanggan</Label>
                                            <Input
                                                id="customer_name"
                                                v-model="form.customer_name"
                                                type="text"
                                                placeholder="Nama pelanggan..."
                                                class="h-11 text-base rounded-xl ios-input-focus md:h-10 md:text-sm"
                                                @input="applyFilters"
                                            />
                                        </div>

                                        <!-- First Name -->
                                        <div class="space-y-2">
                                            <Label for="first_name" class="text-sm font-medium">Nama Depan</Label>
                                            <Input
                                                id="first_name"
                                                v-model="form.first_name"
                                                type="text"
                                                placeholder="Nama depan..."
                                                class="h-11 text-base rounded-xl ios-input-focus md:h-10 md:text-sm"
                                                @input="applyFilters"
                                            />
                                        </div>

                                        <!-- Last Name -->
                                        <div class="space-y-2">
                                            <Label for="last_name" class="text-sm font-medium">Nama Belakang</Label>
                                            <Input
                                                id="last_name"
                                                v-model="form.last_name"
                                                type="text"
                                                placeholder="Nama belakang..."
                                                class="h-11 text-base rounded-xl ios-input-focus md:h-10 md:text-sm"
                                                @input="applyFilters"
                                            />
                                        </div>

                                        <!-- Customer Phone -->
                                        <div class="space-y-2">
                                            <Label for="customer_phone" class="text-sm font-medium">Telepon Pelanggan</Label>
                                            <Input
                                                id="customer_phone"
                                                v-model="form.customer_phone"
                                                type="text"
                                                placeholder="Nomor telepon..."
                                                class="h-11 text-base rounded-xl ios-input-focus md:h-10 md:text-sm"
                                                @input="applyFilters"
                                            />
                                        </div>

                                        <!-- Coupon Type -->
                                        <div class="space-y-2">
                                            <Label for="coupon_type" class="text-sm font-medium">Jenis Kupon</Label>
                                            <Input
                                                id="coupon_type"
                                                v-model="form.coupon_type"
                                                type="text"
                                                placeholder="Jenis kupon..."
                                                class="h-11 text-base rounded-xl ios-input-focus md:h-10 md:text-sm"
                                                @input="applyFilters"
                                            />
                                        </div>

                                        <!-- Expires From -->
                                        <div class="space-y-2">
                                            <Label for="expires_from" class="text-sm font-medium">Kadaluwarsa Dari</Label>
                                            <Input
                                                id="expires_from"
                                                v-model="form.expires_from"
                                                type="date"
                                                class="h-11 text-base rounded-xl ios-input-focus md:h-10 md:text-sm"
                                                @change="applyFilters"
                                            />
                                        </div>

                                        <!-- Expires To -->
                                        <div class="space-y-2">
                                            <Label for="expires_to" class="text-sm font-medium">Kadaluwarsa Sampai</Label>
                                            <Input
                                                id="expires_to"
                                                v-model="form.expires_to"
                                                type="date"
                                                class="h-11 text-base rounded-xl ios-input-focus md:h-10 md:text-sm"
                                                @change="applyFilters"
                                            />
                                        </div>
                                    </div>
                                </CollapsibleContent>
                            </Collapsible>

                            <!-- Active Filters Badges -->
                            <div v-if="activeFilterBadges.length > 0" class="flex flex-wrap gap-2">
                                <Badge
                                    v-for="badge in activeFilterBadges"
                                    :key="badge.key"
                                    variant="outline"
                                    class="gap-1 pr-1 press-effect"
                                >
                                    <span>{{ badge.label }}</span>
                                    <Button
                                        type="button"
                                        variant="ghost"
                                        size="sm"
                                        class="h-auto p-0 hover:bg-transparent"
                                        @click="removeFilter(badge.key)"
                                    >
                                        <X class="h-3 w-3" />
                                    </Button>
                                </Badge>
                            </div>

                            <!-- Clear Filters -->
                            <div v-if="hasActiveFilters" class="flex justify-end">
                                <Button
                                    type="button"
                                    variant="ghost"
                                    size="sm"
                                    class="gap-2 press-effect"
                                    @click="clearFilters"
                                >
                                    <X class="h-4 w-4" />
                                    Hapus Semua Filter
                                </Button>
                            </div>
                        </form>
                    </CardContent>
                </Card>

                <!-- Coupons Table with spring entrance -->
                <Card 
                    :class="[
                        'border rounded-xl',
                        'transition-all duration-500 delay-200',
                        isLoaded ? 'translate-y-0 opacity-100' : 'translate-y-4 opacity-0',
                    ]"
                >
                    <CardContent class="p-0">
                        <!-- Mobile View: Cards with staggered animations -->
                        <div class="block space-y-3 p-4 md:hidden">
                            <Link
                                v-for="(coupon, index) in coupons.data"
                                :key="coupon.id"
                                :href="`/coupons/${coupon.id}`"
                                :class="[
                                    'block rounded-xl border bg-card p-4 shadow-sm',
                                    'transition-all duration-300',
                                    'mobile-card-press',
                                    isLoaded ? 'animate-spring-up' : 'opacity-0',
                                    getStaggerClass(index),
                                ]"
                                @click="handleCardClick"
                            >
                                <div class="space-y-3">
                                    <!-- Code and Status Row -->
                                    <div class="flex items-start justify-between gap-3">
                                        <div class="flex-1 min-w-0">
                                            <p class="font-mono text-lg font-bold text-foreground truncate">{{ coupon.code }}</p>
                                            <p class="text-sm font-medium text-muted-foreground mt-1">{{ coupon.customer_name }}</p>
                                        </div>
                                        <StatusBadge :status="coupon.status" size="sm" />
                                    </div>

                                    <!-- Type and Date Row -->
                                    <div class="flex items-center justify-between gap-2 flex-wrap">
                                        <Badge variant="outline" class="text-xs">{{ coupon.type }}</Badge>
                                        <span class="text-xs text-muted-foreground">
                                            {{ new Date(coupon.created_at).toLocaleDateString('id-ID') }}
                                        </span>
                                    </div>
                                </div>
                            </Link>
                        </div>

                        <!-- Desktop View: Table -->
                        <div class="hidden overflow-x-auto md:block">
                            <table class="w-full">
                                <thead class="border-b bg-muted/30">
                                    <tr>
                                        <th class="px-6 py-3.5 text-left text-xs font-semibold uppercase tracking-wider text-muted-foreground">
                                            <button
                                                @click="sort('code')"
                                                class="flex items-center gap-2 hover:text-foreground transition-colors press-effect"
                                            >
                                                Kode
                                                <component :is="getSortIcon('code')" class="h-3 w-3" />
                                            </button>
                                        </th>
                                        <th class="px-6 py-3.5 text-left text-xs font-semibold uppercase tracking-wider text-muted-foreground">
                                            <button
                                                @click="sort('customer_name')"
                                                class="flex items-center gap-2 hover:text-foreground transition-colors press-effect"
                                            >
                                                Pelanggan
                                                <component :is="getSortIcon('customer_name')" class="h-3 w-3" />
                                            </button>
                                        </th>
                                        <th class="px-6 py-3.5 text-left text-xs font-semibold uppercase tracking-wider text-muted-foreground">
                                            <button
                                                @click="sort('customer_phone')"
                                                class="flex items-center gap-2 hover:text-foreground transition-colors press-effect"
                                            >
                                                Telepon
                                                <component :is="getSortIcon('customer_phone')" class="h-3 w-3" />
                                            </button>
                                        </th>
                                        <th class="px-6 py-3.5 text-left text-xs font-semibold uppercase tracking-wider text-muted-foreground">
                                            <button
                                                @click="sort('type')"
                                                class="flex items-center gap-2 hover:text-foreground transition-colors press-effect"
                                            >
                                                Jenis
                                                <component :is="getSortIcon('type')" class="h-3 w-3" />
                                            </button>
                                        </th>
                                        <th class="px-6 py-3.5 text-left text-xs font-semibold uppercase tracking-wider text-muted-foreground">
                                            <button
                                                @click="sort('status')"
                                                class="flex items-center gap-2 hover:text-foreground transition-colors press-effect"
                                            >
                                                Status
                                                <component :is="getSortIcon('status')" class="h-3 w-3" />
                                            </button>
                                        </th>
                                        <th class="px-6 py-3.5 text-left text-xs font-semibold uppercase tracking-wider text-muted-foreground">
                                            <button
                                                @click="sort('created_at')"
                                                class="flex items-center gap-2 hover:text-foreground transition-colors press-effect"
                                            >
                                                Dibuat
                                                <component :is="getSortIcon('created_at')" class="h-3 w-3" />
                                            </button>
                                        </th>
                                        <th class="px-6 py-3.5 text-right text-xs font-semibold uppercase tracking-wider text-muted-foreground">
                                            Aksi
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y">
                                    <tr
                                        v-for="(coupon, index) in coupons.data"
                                        :key="coupon.id"
                                        :class="[
                                            'list-row-interactive',
                                            isLoaded ? 'animate-fade-up' : 'opacity-0',
                                            getStaggerClass(index),
                                        ]"
                                    >
                                        <td class="whitespace-nowrap px-6 py-4">
                                            <span class="font-mono font-semibold text-foreground">{{ coupon.code }}</span>
                                        </td>
                                        <td class="px-6 py-4">
                                            <span class="font-medium text-foreground">{{ coupon.customer_name }}</span>
                                        </td>
                                        <td class="px-6 py-4">
                                            <span class="text-sm text-muted-foreground">
                                                {{ coupon.formatted_phone || coupon.customer_phone }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4">
                                            <span class="text-sm text-foreground">{{ coupon.type }}</span>
                                        </td>
                                        <td class="px-6 py-4">
                                            <StatusBadge :status="coupon.status" size="sm" />
                                        </td>
                                        <td class="px-6 py-4">
                                            <span class="text-sm text-muted-foreground">
                                                {{ new Date(coupon.created_at).toLocaleDateString('id-ID') }}
                                            </span>
                                        </td>
                                        <td class="whitespace-nowrap px-6 py-4 text-right">
                                            <Button
                                                as-child
                                                variant="ghost"
                                                size="sm"
                                                class="rounded-xl press-effect"
                                            >
                                                <Link :href="`/coupons/${coupon.id}`" @click="trigger('light')">
                                                    <Eye class="h-4 w-4" />
                                                </Link>
                                            </Button>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination -->
                        <div v-if="coupons.last_page > 1" class="border-t p-4">
                            <div class="flex flex-col items-center justify-between gap-4 sm:flex-row">
                                <p class="text-xs sm:text-sm text-muted-foreground text-center sm:text-left">
                                    Menampilkan {{ (coupons.current_page - 1) * coupons.per_page + 1 }} sampai
                                    {{ Math.min(coupons.current_page * coupons.per_page, coupons.total) }} dari
                                    {{ coupons.total }} kupon
                                </p>
                                <div class="flex items-center gap-1 sm:gap-2">
                                    <!-- Previous Button -->
                                    <Button
                                        variant="outline"
                                        size="sm"
                                        class="h-9 w-9 rounded-xl p-0 press-effect"
                                        :disabled="coupons.current_page === 1"
                                        @click="handlePaginationClick(coupons.current_page - 1)"
                                    >
                                        <ChevronLeft class="h-4 w-4" />
                                    </Button>

                                    <!-- Page Numbers - Hide on very small screens -->
                                    <div class="hidden xs:flex gap-1">
                                        <template v-for="page in getPageNumbers()" :key="page">
                                            <Button
                                                v-if="page !== '...'"
                                                variant="outline"
                                                size="sm"
                                                class="h-9 min-w-[2.5rem] rounded-xl text-xs press-effect"
                                                :class="{ 'bg-primary text-primary-foreground': page === coupons.current_page }"
                                                @click="handlePaginationClick(page)"
                                            >
                                                {{ page }}
                                            </Button>
                                            <span v-else class="px-2 py-1 text-xs text-muted-foreground">...</span>
                                        </template>
                                    </div>

                                    <!-- Next Button -->
                                    <Button
                                        variant="outline"
                                        size="sm"
                                        class="h-9 w-9 rounded-xl p-0 press-effect"
                                        :disabled="coupons.current_page === coupons.last_page"
                                        @click="handlePaginationClick(coupons.current_page + 1)"
                                    >
                                        <ChevronRight class="h-4 w-4" />
                                    </Button>
                                </div>
                            </div>
                        </div>

                        <!-- Empty State -->
                        <EmptyState
                            v-if="coupons.data.length === 0"
                            :icon="Ticket"
                            title="Tidak ada kupon ditemukan"
                            description="Coba ubah filter atau buat kupon baru"
                        />
                    </CardContent>
                </Card>
            </div>
        </PullToRefresh>
    </AppLayout>
</template>
