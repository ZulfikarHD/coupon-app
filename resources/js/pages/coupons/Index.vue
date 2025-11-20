<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Badge } from '@/components/ui/badge';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Checkbox } from '@/components/ui/checkbox';
import { Label } from '@/components/ui/label';
import { Collapsible, CollapsibleContent, CollapsibleTrigger } from '@/components/ui/collapsible';
import { Plus, Search, Filter, Eye, X, ChevronDown, ChevronUp } from 'lucide-vue-next';
import { computed, ref } from 'vue';

interface Coupon {
    id: number;
    code: string;
    type: string;
    customer_name: string;
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
        customer_phone?: string;
        coupon_type?: string;
        date_from?: string;
        date_to?: string;
        expires_from?: string;
        expires_to?: string;
    };
}

const props = defineProps<Props>();

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
    customer_phone: props.filters.customer_phone || '',
    coupon_type: props.filters.coupon_type || '',
    date_from: props.filters.date_from || '',
    date_to: props.filters.date_to || '',
    expires_from: props.filters.expires_from || '',
    expires_to: props.filters.expires_to || '',
});

const showAdvancedSearch = ref(false);

const statusColors = {
    active: 'bg-green-500/10 text-green-700 dark:text-green-400 border-green-500/20',
    used: 'bg-gray-500/10 text-gray-700 dark:text-gray-400 border-gray-500/20',
    expired: 'bg-red-500/10 text-red-700 dark:text-red-400 border-red-500/20',
};

const statusLabels = {
    active: 'Aktif',
    used: 'Terpakai',
    expired: 'Kedaluwarsa',
};

const statusOptions = [
    { value: 'active', label: 'Aktif' },
    { value: 'used', label: 'Terpakai' },
    { value: 'expired', label: 'Kedaluwarsa' },
];

const toggleStatus = (status: string, checked: boolean) => {
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
    form.reset();
    form.status = [];
    form.search = '';
    form.customer_name = '';
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
    
    // Laravel automatically parses multiple status=value parameters as an array
    // So we use 'status' without brackets
    if (Array.isArray(form.status) && form.status.length > 0) {
        form.status.forEach((s) => searchParams.append('status', s));
    }
    if (form.search) searchParams.set('search', form.search);
    if (form.customer_name) searchParams.set('customer_name', form.customer_name);
    if (form.customer_phone) searchParams.set('customer_phone', form.customer_phone);
    if (form.coupon_type) searchParams.set('coupon_type', form.coupon_type);
    if (form.date_from) searchParams.set('date_from', form.date_from);
    if (form.date_to) searchParams.set('date_to', form.date_to);
    if (form.expires_from) searchParams.set('expires_from', form.expires_from);
    if (form.expires_to) searchParams.set('expires_to', form.expires_to);
    if (page) searchParams.set('page', String(page));
    
    return searchParams.toString();
};

const breadcrumbs = [
    {
        title: 'Kupon',
        href: '/coupons',
    },
];
</script>

<template>
    <Head title="Semua Kupon" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4 md:p-6">
            <!-- Header -->
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div class="space-y-1">
                    <h1 class="text-2xl font-semibold tracking-tight md:text-3xl">
                        Semua Kupon
                    </h1>
                    <p class="text-sm text-muted-foreground md:text-base">
                        Kelola dan lihat semua kupon yang telah dibuat
                    </p>
                </div>
                <Button
                    as-child
                    class="h-11 w-full gap-2 sm:w-auto"
                >
                    <Link href="/coupons/create">
                        <Plus class="h-4 w-4" />
                        Buat Kupon Baru
                    </Link>
                </Button>
            </div>

            <!-- Filters Card -->
            <Card class="border-2">
                <CardHeader class="pb-4">
                    <div class="flex items-center gap-2">
                        <Filter class="h-5 w-5 text-primary" />
                        <CardTitle class="text-lg md:text-xl">Filter & Pencarian</CardTitle>
                    </div>
                </CardHeader>
                <CardContent>
                    <form @submit.prevent="applyFilters" class="space-y-4">
                        <!-- Basic Search -->
                        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                            <!-- Search -->
                            <div class="space-y-2 sm:col-span-2 lg:col-span-1">
                                <Label class="text-sm font-medium">Cari</Label>
                                <div class="relative">
                                    <Search class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-muted-foreground" />
                                    <Input
                                        v-model="form.search"
                                        type="text"
                                        placeholder="Kode, nama, telepon, atau jenis..."
                                        class="h-11 pl-10 text-base md:h-10 md:text-sm"
                                        @input="applyFilters"
                                    />
                                </div>
                            </div>

                            <!-- Status Filter (Quick) -->
                            <div class="space-y-2">
                                <Label class="text-sm font-medium">Status</Label>
                                <div class="flex flex-wrap gap-2">
                                    <label
                                        v-for="option in statusOptions"
                                        :key="option.value"
                                        class="flex cursor-pointer items-center gap-2 rounded-md border px-3 py-2 text-sm transition-colors hover:bg-muted"
                                        :class="{
                                            'bg-primary text-primary-foreground': Array.isArray(form.status) && form.status.includes(option.value),
                                        }"
                                    >
                                        <Checkbox
                                            :checked="Array.isArray(form.status) && form.status.includes(option.value)"
                                            @update:checked="(checked) => toggleStatus(option.value, checked)"
                                        />
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
                                    class="h-11 text-base md:h-10 md:text-sm"
                                    @change="applyFilters"
                                />
                            </div>

                            <!-- Date To -->
                            <div class="space-y-2">
                                <Label class="text-sm font-medium">Dibuat Sampai</Label>
                                <Input
                                    v-model="form.date_to"
                                    type="date"
                                    class="h-11 text-base md:h-10 md:text-sm"
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
                                    class="w-full justify-between"
                                >
                                    <span>Pencarian Lanjutan</span>
                                    <ChevronDown v-if="!showAdvancedSearch" class="h-4 w-4" />
                                    <ChevronUp v-else class="h-4 w-4" />
                                </Button>
                            </CollapsibleTrigger>
                            <CollapsibleContent class="mt-4 space-y-4">
                                <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                                    <!-- Customer Name -->
                                    <div class="space-y-2">
                                        <Label for="customer_name" class="text-sm font-medium">Nama Pelanggan</Label>
                                        <Input
                                            id="customer_name"
                                            v-model="form.customer_name"
                                            type="text"
                                            placeholder="Nama pelanggan..."
                                            class="h-11 text-base md:h-10 md:text-sm"
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
                                            class="h-11 text-base md:h-10 md:text-sm"
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
                                            class="h-11 text-base md:h-10 md:text-sm"
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
                                            class="h-11 text-base md:h-10 md:text-sm"
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
                                            class="h-11 text-base md:h-10 md:text-sm"
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
                                class="gap-1 pr-1"
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
                                class="gap-2"
                                @click="clearFilters"
                            >
                                <X class="h-4 w-4" />
                                Hapus Semua Filter
                            </Button>
                        </div>
                    </form>
                </CardContent>
            </Card>

            <!-- Coupons Table -->
            <Card class="border-2">
                <CardContent class="p-0">
                    <!-- Mobile View: Cards -->
                    <div class="block space-y-4 p-4 md:hidden">
                        <div
                            v-for="coupon in coupons.data"
                            :key="coupon.id"
                            class="rounded-lg border bg-card p-4 shadow-sm"
                        >
                            <div class="flex items-start justify-between">
                                <div class="flex-1 space-y-2">
                                    <div>
                                        <p class="font-semibold text-foreground">{{ coupon.code }}</p>
                                        <p class="text-sm text-muted-foreground">{{ coupon.customer_name }}</p>
                                    </div>
                                    <div class="flex flex-wrap gap-2">
                                        <Badge :class="statusColors[coupon.status]">
                                            {{ statusLabels[coupon.status] }}
                                        </Badge>
                                        <span class="text-xs text-muted-foreground">
                                            {{ coupon.type }}
                                        </span>
                                    </div>
                                    <p class="text-xs text-muted-foreground">
                                        Dibuat: {{ new Date(coupon.created_at).toLocaleDateString('id-ID') }}
                                    </p>
                                </div>
                                <Button
                                    as-child
                                    variant="outline"
                                    size="sm"
                                    class="ml-2"
                                >
                                    <Link :href="`/coupons/${coupon.id}`">
                                        <Eye class="h-4 w-4" />
                                    </Link>
                                </Button>
                            </div>
                        </div>
                    </div>

                    <!-- Desktop View: Table -->
                    <div class="hidden overflow-x-auto md:block">
                        <table class="w-full">
                            <thead class="border-b bg-muted/50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-muted-foreground">
                                        Kode
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-muted-foreground">
                                        Pelanggan
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-muted-foreground">
                                        Telepon
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-muted-foreground">
                                        Jenis
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-muted-foreground">
                                        Status
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-muted-foreground">
                                        Dibuat
                                    </th>
                                    <th class="px-6 py-3 text-right text-xs font-medium uppercase tracking-wider text-muted-foreground">
                                        Aksi
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="divide-y">
                                <tr
                                    v-for="coupon in coupons.data"
                                    :key="coupon.id"
                                    class="hover:bg-muted/50 transition-colors"
                                >
                                    <td class="whitespace-nowrap px-6 py-4">
                                        <span class="font-mono font-medium">{{ coupon.code }}</span>
                                    </td>
                                    <td class="px-6 py-4">
                                        <span class="font-medium">{{ coupon.customer_name }}</span>
                                    </td>
                                    <td class="px-6 py-4">
                                        <span class="text-sm text-muted-foreground">
                                            {{ coupon.formatted_phone || coupon.customer_phone }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4">
                                        <span class="text-sm">{{ coupon.type }}</span>
                                    </td>
                                    <td class="px-6 py-4">
                                        <Badge :class="statusColors[coupon.status]">
                                            {{ statusLabels[coupon.status] }}
                                        </Badge>
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
                                >
                                    <Link :href="`/coupons/${coupon.id}`">
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
                            <p class="text-sm text-muted-foreground">
                                Menampilkan {{ coupons.data.length }} dari {{ coupons.total }} kupon
                            </p>
                            <div class="flex gap-2">
                                <Button
                                    variant="outline"
                                    size="sm"
                                    :disabled="coupons.current_page === 1"
                                    @click="router.visit(`/coupons?${buildQueryString(coupons.current_page - 1)}`, { preserveState: true, preserveScroll: true })"
                                >
                                    Sebelumnya
                                </Button>
                                <Button
                                    variant="outline"
                                    size="sm"
                                    :disabled="coupons.current_page === coupons.last_page"
                                    @click="router.visit(`/coupons?${buildQueryString(coupons.current_page + 1)}`, { preserveState: true, preserveScroll: true })"
                                >
                                    Selanjutnya
                                </Button>
                            </div>
                        </div>
                    </div>

                    <!-- Empty State -->
                    <div v-if="coupons.data.length === 0" class="p-12 text-center">
                        <p class="text-lg font-medium text-muted-foreground">
                            Tidak ada kupon ditemukan
                        </p>
                        <p class="mt-2 text-sm text-muted-foreground">
                            Coba ubah filter atau buat kupon baru
                        </p>
                    </div>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>
