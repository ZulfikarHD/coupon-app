<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Badge } from '@/components/ui/badge';
import { Card, CardContent } from '@/components/ui/card';
import { type BreadcrumbItem } from '@/types';
import { Plus, Search, Filter, X } from 'lucide-vue-next';
import { ref, computed } from 'vue';

interface Coupon {
    id: number;
    code: string;
    customer_name: string;
    customer_phone: string;
    type: string;
    status: 'active' | 'used' | 'expired';
    created_at: string;
    expires_at: string | null;
    user?: {
        name: string;
    };
}

interface Props {
    coupons: {
        data: Coupon[];
        links: any[];
        current_page: number;
        last_page: number;
        per_page: number;
        total: number;
    };
    filters: {
        status?: string;
        search?: string;
        date_from?: string;
        date_to?: string;
    };
}

const props = defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Kupon',
        href: '/coupons',
    },
];

const showFilters = ref(false);

const filterForm = useForm({
    status: props.filters.status || '',
    search: props.filters.search || '',
    date_from: props.filters.date_from || '',
    date_to: props.filters.date_to || '',
});

const applyFilters = () => {
    filterForm.get('/coupons', {
        preserveState: true,
        preserveScroll: true,
    });
};

const clearFilters = () => {
    filterForm.reset();
    filterForm.get('/coupons', {
        preserveState: true,
        preserveScroll: true,
    });
};

const hasActiveFilters = computed(() => {
    return !!(
        filterForm.status ||
        filterForm.search ||
        filterForm.date_from ||
        filterForm.date_to
    );
});

const getStatusBadgeVariant = (status: string) => {
    switch (status) {
        case 'active':
            return 'default';
        case 'used':
            return 'secondary';
        case 'expired':
            return 'destructive';
        default:
            return 'outline';
    }
};

const getStatusLabel = (status: string) => {
    switch (status) {
        case 'active':
            return 'Aktif';
        case 'used':
            return 'Terpakai';
        case 'expired':
            return 'Kedaluwarsa';
        default:
            return status;
    }
};

const formatDate = (dateString: string) => {
    const date = new Date(dateString);
    return date.toLocaleDateString('id-ID', {
        day: '2-digit',
        month: 'short',
        year: 'numeric',
    });
};

const formatPhone = (phone: string) => {
    if (!phone) return '';
    if (phone.startsWith('62')) {
        return '0' + phone.substring(2);
    }
    return phone;
};
</script>

<template>
    <Head title="Daftar Kupon" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="container mx-auto px-4 py-6 sm:px-6 lg:px-8">
            <!-- Mobile-first header -->
            <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h1 class="text-2xl font-semibold text-foreground sm:text-3xl">
                        Daftar Kupon
                    </h1>
                    <p class="mt-1 text-sm text-muted-foreground">
                        Total: {{ coupons.total }} kupon
                    </p>
                </div>
                <Button as-child class="w-full sm:w-auto">
                    <Link href="/coupons/create">
                        <Plus class="mr-2 h-4 w-4" />
                        Buat Kupon Baru
                    </Link>
                </Button>
            </div>

            <!-- Filters Card - Mobile First -->
            <Card class="mb-6">
                <CardContent class="p-4 sm:p-6">
                    <div class="space-y-4">
                        <!-- Search Bar -->
                        <div class="relative">
                            <Search class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-muted-foreground" />
                            <Input
                                v-model="filterForm.search"
                                type="text"
                                placeholder="Cari kode, nama, atau nomor telepon..."
                                class="w-full pl-10"
                                @keyup.enter="applyFilters"
                            />
                        </div>

                        <!-- Filter Toggle Button (Mobile) -->
                        <Button
                            v-if="!showFilters"
                            type="button"
                            variant="outline"
                            class="w-full sm:hidden"
                            @click="showFilters = true"
                        >
                            <Filter class="mr-2 h-4 w-4" />
                            Filter
                        </Button>

                        <!-- Filters (Collapsible on Mobile) -->
                        <div
                            :class="[
                                'space-y-4',
                                showFilters ? 'block' : 'hidden sm:block',
                            ]"
                        >
                            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
                                <!-- Status Filter -->
                                <div class="space-y-2">
                                    <label class="text-sm font-medium">Status</label>
                                    <select
                                        v-model="filterForm.status"
                                        class="flex h-9 w-full rounded-md border border-input bg-transparent px-3 py-1 text-sm shadow-xs transition-colors focus-visible:outline-none focus-visible:ring-[3px] focus-visible:ring-ring/50 disabled:cursor-not-allowed disabled:opacity-50 dark:bg-input/30 dark:border-input"
                                    >
                                        <option value="">Semua Status</option>
                                        <option value="active">Aktif</option>
                                        <option value="used">Terpakai</option>
                                        <option value="expired">Kedaluwarsa</option>
                                    </select>
                                </div>

                                <!-- Date From -->
                                <div class="space-y-2">
                                    <label class="text-sm font-medium">Dari Tanggal</label>
                                    <Input
                                        v-model="filterForm.date_from"
                                        type="date"
                                        class="w-full"
                                    />
                                </div>

                                <!-- Date To -->
                                <div class="space-y-2">
                                    <label class="text-sm font-medium">Sampai Tanggal</label>
                                    <Input
                                        v-model="filterForm.date_to"
                                        type="date"
                                        class="w-full"
                                    />
                                </div>
                            </div>

                            <!-- Filter Actions -->
                            <div class="flex flex-col gap-2 sm:flex-row sm:justify-end">
                                <Button
                                    v-if="hasActiveFilters"
                                    type="button"
                                    variant="outline"
                                    class="w-full sm:w-auto"
                                    @click="clearFilters"
                                >
                                    <X class="mr-2 h-4 w-4" />
                                    Hapus Filter
                                </Button>
                                <Button
                                    type="button"
                                    class="w-full sm:w-auto"
                                    @click="applyFilters"
                                >
                                    Terapkan Filter
                                </Button>
                            </div>
                        </div>
                    </div>
                </CardContent>
            </Card>

            <!-- Coupons List - Mobile First -->
            <div class="space-y-4">
                <!-- Mobile Card View -->
                <div class="block sm:hidden">
                    <Card
                        v-for="coupon in coupons.data"
                        :key="coupon.id"
                        class="mb-4 cursor-pointer transition-shadow hover:shadow-md"
                        @click="router.visit(`/coupons/${coupon.id}`)"
                    >
                        <CardContent class="p-4">
                            <div class="flex items-start justify-between">
                                <div class="flex-1">
                                    <div class="flex items-center gap-2 mb-2">
                                        <h3 class="font-semibold text-foreground">
                                            {{ coupon.code }}
                                        </h3>
                                        <Badge :variant="getStatusBadgeVariant(coupon.status)">
                                            {{ getStatusLabel(coupon.status) }}
                                        </Badge>
                                    </div>
                                    <p class="text-sm font-medium text-foreground mb-1">
                                        {{ coupon.customer_name }}
                                    </p>
                                    <p class="text-xs text-muted-foreground mb-1">
                                        {{ formatPhone(coupon.customer_phone) }}
                                    </p>
                                    <p class="text-xs text-muted-foreground mb-2">
                                        {{ coupon.type }}
                                    </p>
                                    <p class="text-xs text-muted-foreground">
                                        Dibuat: {{ formatDate(coupon.created_at) }}
                                    </p>
                                </div>
                            </div>
                        </CardContent>
                    </Card>
                </div>

                <!-- Desktop Table View -->
                <div class="hidden sm:block overflow-x-auto">
                    <Card>
                        <CardContent class="p-0">
                            <div class="overflow-x-auto">
                                <table class="w-full">
                                    <thead class="border-b bg-muted/50">
                                        <tr>
                                            <th class="px-4 py-3 text-left text-sm font-medium text-foreground">
                                                Kode
                                            </th>
                                            <th class="px-4 py-3 text-left text-sm font-medium text-foreground">
                                                Pelanggan
                                            </th>
                                            <th class="px-4 py-3 text-left text-sm font-medium text-foreground">
                                                Telepon
                                            </th>
                                            <th class="px-4 py-3 text-left text-sm font-medium text-foreground">
                                                Jenis
                                            </th>
                                            <th class="px-4 py-3 text-left text-sm font-medium text-foreground">
                                                Status
                                            </th>
                                            <th class="px-4 py-3 text-left text-sm font-medium text-foreground">
                                                Dibuat
                                            </th>
                                            <th class="px-4 py-3 text-left text-sm font-medium text-foreground">
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
                                            <td class="px-4 py-3">
                                                <span class="font-mono font-medium text-foreground">
                                                    {{ coupon.code }}
                                                </span>
                                            </td>
                                            <td class="px-4 py-3 text-sm text-foreground">
                                                {{ coupon.customer_name }}
                                            </td>
                                            <td class="px-4 py-3 text-sm text-muted-foreground">
                                                {{ formatPhone(coupon.customer_phone) }}
                                            </td>
                                            <td class="px-4 py-3 text-sm text-foreground">
                                                {{ coupon.type }}
                                            </td>
                                            <td class="px-4 py-3">
                                                <Badge :variant="getStatusBadgeVariant(coupon.status)">
                                                    {{ getStatusLabel(coupon.status) }}
                                                </Badge>
                                            </td>
                                            <td class="px-4 py-3 text-sm text-muted-foreground">
                                                {{ formatDate(coupon.created_at) }}
                                            </td>
                                            <td class="px-4 py-3">
                                                <Button
                                                    as-child
                                                    variant="ghost"
                                                    size="sm"
                                                >
                                                    <Link :href="`/coupons/${coupon.id}`">
                                                        Lihat
                                                    </Link>
                                                </Button>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </CardContent>
                    </Card>
                </div>

                <!-- Pagination -->
                <div
                    v-if="coupons.last_page > 1"
                    class="flex flex-wrap items-center justify-center gap-2"
                >
                    <template v-for="link in coupons.links" :key="link.label">
                        <Button
                            v-if="link.url"
                            as-child
                            :variant="link.active ? 'default' : 'outline'"
                            size="sm"
                        >
                            <Link :href="link.url" v-html="link.label" />
                        </Button>
                        <span
                            v-else
                            class="px-3 py-1 text-sm text-muted-foreground"
                            v-html="link.label"
                        />
                    </template>
                </div>

                <!-- Empty State -->
                <Card v-if="coupons.data.length === 0">
                    <CardContent class="py-12 text-center">
                        <p class="text-muted-foreground">
                            Tidak ada kupon ditemukan.
                        </p>
                        <Button as-child class="mt-4">
                            <Link href="/coupons/create">
                                <Plus class="mr-2 h-4 w-4" />
                                Buat Kupon Pertama
                            </Link>
                        </Button>
                    </CardContent>
                </Card>
            </div>
        </div>
    </AppLayout>
</template>
