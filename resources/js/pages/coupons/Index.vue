<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Badge } from '@/components/ui/badge';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Plus, Search, Filter, Eye, X } from 'lucide-vue-next';
import { computed } from 'vue';

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
        status?: string;
        search?: string;
        date_from?: string;
        date_to?: string;
    };
}

const props = defineProps<Props>();

const form = useForm({
    status: props.filters.status || 'all',
    search: props.filters.search || '',
    date_from: props.filters.date_from || '',
    date_to: props.filters.date_to || '',
});

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

const applyFilters = () => {
    form.get('/coupons', {
        preserveState: true,
        preserveScroll: true,
        replace: true,
    });
};

const clearFilters = () => {
    form.reset();
    form.get('/coupons', {
        preserveState: true,
        preserveScroll: true,
        replace: true,
    });
};

const hasActiveFilters = computed(() => {
    return form.status !== 'all' || form.search || form.date_from || form.date_to;
});

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
                        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                            <!-- Search -->
                            <div class="space-y-2 sm:col-span-2 lg:col-span-1">
                                <label class="text-sm font-medium">Cari</label>
                                <div class="relative">
                                    <Search class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-muted-foreground" />
                                    <Input
                                        v-model="form.search"
                                        type="text"
                                        placeholder="Kode, nama, atau telepon..."
                                        class="h-11 pl-10 text-base md:h-10 md:text-sm"
                                        @input="applyFilters"
                                    />
                                </div>
                            </div>

                            <!-- Status Filter -->
                            <div class="space-y-2">
                                <label class="text-sm font-medium">Status</label>
                                <select
                                    v-model="form.status"
                                    class="flex h-11 w-full rounded-md border border-input bg-transparent px-3 py-2 text-base shadow-xs transition-[color,box-shadow] outline-none focus-visible:border-ring focus-visible:ring-ring/50 focus-visible:ring-[3px] disabled:cursor-not-allowed disabled:opacity-50 md:h-10 md:text-sm"
                                    @change="applyFilters"
                                >
                                    <option value="all">Semua</option>
                                    <option value="active">Aktif</option>
                                    <option value="used">Terpakai</option>
                                    <option value="expired">Kedaluwarsa</option>
                                </select>
                            </div>

                            <!-- Date From -->
                            <div class="space-y-2">
                                <label class="text-sm font-medium">Dari Tanggal</label>
                                <Input
                                    v-model="form.date_from"
                                    type="date"
                                    class="h-11 text-base md:h-10 md:text-sm"
                                    @change="applyFilters"
                                />
                            </div>

                            <!-- Date To -->
                            <div class="space-y-2">
                                <label class="text-sm font-medium">Sampai Tanggal</label>
                                <Input
                                    v-model="form.date_to"
                                    type="date"
                                    class="h-11 text-base md:h-10 md:text-sm"
                                    @change="applyFilters"
                                />
                            </div>
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
                                Hapus Filter
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
                                    @click="router.visit(`/coupons?page=${coupons.current_page - 1}`)"
                                >
                                    Sebelumnya
                                </Button>
                                <Button
                                    variant="outline"
                                    size="sm"
                                    :disabled="coupons.current_page === coupons.last_page"
                                    @click="router.visit(`/coupons?page=${coupons.current_page + 1}`)"
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
