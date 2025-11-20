<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import PageHeader from '@/components/PageHeader.vue';
import EmptyState from '@/components/EmptyState.vue';
import StatusBadge from '@/components/StatusBadge.vue';
import { useStatusColors } from '@/composables/useStatusColors';
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Badge } from '@/components/ui/badge';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import { Plus, Search, Edit, Trash2, User as UserIcon, Shield, Users, ChevronDown, ArrowUpDown, ArrowUp, ArrowDown, ChevronLeft, ChevronRight } from 'lucide-vue-next';
import { ref, computed } from 'vue';
import {
    Dialog,
    DialogClose,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
    DialogTrigger,
} from '@/components/ui/dialog';

interface User {
    id: number;
    name: string;
    email: string;
    role: 'admin' | 'user';
    created_at: string;
    email_verified_at: string | null;
}

interface Props {
    users: {
        data: User[];
        current_page: number;
        last_page: number;
        per_page: number;
        total: number;
    };
    filters: {
        search?: string;
        role?: string;
        sort?: string;
        direction?: string;
    };
}

const props = defineProps<Props>();

const form = useForm({
    search: props.filters.search || '',
    role: props.filters.role || 'all',
    sort: props.filters.sort || 'created_at',
    direction: props.filters.direction || 'desc',
});

const currentSort = computed(() => props.filters.sort || 'created_at');
const currentDirection = computed(() => props.filters.direction || 'desc');

const sort = (column: string) => {
    const newDirection = currentSort.value === column && currentDirection.value === 'asc' ? 'desc' : 'asc';
    form.sort = column;
    form.direction = newDirection;
    form.get('/users', {
        preserveState: true,
        preserveScroll: true,
    });
};

const getSortIcon = (column: string) => {
    if (currentSort.value !== column) {
        return ArrowUpDown;
    }
    return currentDirection.value === 'asc' ? ArrowUp : ArrowDown;
};

const applyFilters = () => {
    form.get('/users', {
        preserveState: true,
        preserveScroll: true,
    });
};

const resetFilters = () => {
    form.reset();
    form.get('/users', {
        preserveState: true,
        preserveScroll: true,
    });
};

const deleteUser = (userId: number) => {
    router.delete(`/users/${userId}`, {
        preserveScroll: true,
        onSuccess: () => {
            // Success handled by flash message
        },
    });
};

const { roleLabels } = useStatusColors();

const buildPaginationQuery = (page: number): string => {
    const params = new URLSearchParams();
    if (form.search) params.set('search', form.search);
    if (form.role && form.role !== 'all') params.set('role', form.role);
    if (form.sort) params.set('sort', form.sort);
    if (form.direction) params.set('direction', form.direction);
    if (page > 1) params.set('page', String(page));
    return params.toString();
};

const getPageNumbers = (): (number | string)[] => {
    const current = props.users.current_page;
    const last = props.users.last_page;
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

const breadcrumbs = [
    {
        title: 'User Management',
        href: '/users',
    },
];
</script>

<template>
    <Head title="User Management" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 overflow-x-auto p-4 md:p-6">
            <!-- Header -->
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <PageHeader
                    title="User Management"
                    description="Kelola pengguna sistem"
                />
                <Button as-child size="lg" class="gap-2 rounded-xl">
                    <Link href="/users/create">
                        <Plus class="h-5 w-5" />
                        Tambah User
                    </Link>
                </Button>
            </div>

            <!-- Filters -->
            <Card class="border rounded-xl">
                <CardContent class="pt-6">
                    <form @submit.prevent="applyFilters" class="flex flex-col gap-4 sm:flex-row">
                        <div class="flex-1">
                            <div class="relative">
                                <Search class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-muted-foreground" />
                                <Input
                                    v-model="form.search"
                                    type="text"
                                    placeholder="Cari nama atau email..."
                                    class="pl-10 rounded-xl"
                                />
                            </div>
                        </div>
                        <div class="w-full sm:w-48">
                            <DropdownMenu>
                                <DropdownMenuTrigger as-child>
                                    <Button
                                        variant="outline"
                                        class="w-full justify-between"
                                        :class="{ 'border-destructive': form.errors.role }"
                                    >
                                        {{ form.role === 'all' ? 'Semua Role' : form.role === 'admin' ? 'Admin' : 'User' }}
                                        <ChevronDown class="ml-2 h-4 w-4 opacity-50" />
                                    </Button>
                                </DropdownMenuTrigger>
                                <DropdownMenuContent class="w-full">
                                    <DropdownMenuItem
                                        @click="form.role = 'all'; applyFilters()"
                                        :class="{ 'bg-accent': form.role === 'all' }"
                                    >
                                        Semua Role
                                    </DropdownMenuItem>
                                    <DropdownMenuItem
                                        @click="form.role = 'admin'; applyFilters()"
                                        :class="{ 'bg-accent': form.role === 'admin' }"
                                    >
                                        Admin
                                    </DropdownMenuItem>
                                    <DropdownMenuItem
                                        @click="form.role = 'user'; applyFilters()"
                                        :class="{ 'bg-accent': form.role === 'user' }"
                                    >
                                        User
                                    </DropdownMenuItem>
                                </DropdownMenuContent>
                            </DropdownMenu>
                        </div>
                        <div class="flex gap-2">
                            <Button type="submit" :disabled="form.processing">
                                <Search class="h-4 w-4" />
                            </Button>
                            <Button
                                type="button"
                                variant="outline"
                                @click="resetFilters"
                                :disabled="form.processing"
                            >
                                Reset
                            </Button>
                        </div>
                    </form>
                </CardContent>
            </Card>

            <!-- Users Table -->
            <Card class="border rounded-xl">
                <CardHeader class="pb-4">
                    <div class="flex items-center gap-2">
                        <Users class="h-5 w-5 text-primary" />
                        <CardTitle class="text-lg font-semibold">Daftar User ({{ props.users.total }})</CardTitle>
                    </div>
                    <CardDescription class="text-sm mt-1">
                        Total pengguna terdaftar dalam sistem
                    </CardDescription>
                </CardHeader>
                <CardContent>
                    <EmptyState
                        v-if="props.users.data.length === 0"
                        :icon="UserIcon"
                        title="Tidak ada user ditemukan"
                    />
                    <div v-else class="overflow-x-auto">
                        <table class="w-full">
                            <thead>
                                <tr class="border-b">
                                    <th class="px-4 py-3 text-left text-sm font-medium text-muted-foreground">
                                        <button
                                            @click="sort('name')"
                                            class="flex items-center gap-2 hover:text-foreground transition-colors"
                                        >
                                            Nama
                                            <component :is="getSortIcon('name')" class="h-4 w-4" />
                                        </button>
                                    </th>
                                    <th class="px-4 py-3 text-left text-sm font-medium text-muted-foreground">
                                        <button
                                            @click="sort('email')"
                                            class="flex items-center gap-2 hover:text-foreground transition-colors"
                                        >
                                            Email
                                            <component :is="getSortIcon('email')" class="h-4 w-4" />
                                        </button>
                                    </th>
                                    <th class="px-4 py-3 text-left text-sm font-medium text-muted-foreground">
                                        <button
                                            @click="sort('role')"
                                            class="flex items-center gap-2 hover:text-foreground transition-colors"
                                        >
                                            Role
                                            <component :is="getSortIcon('role')" class="h-4 w-4" />
                                        </button>
                                    </th>
                                    <th class="px-4 py-3 text-left text-sm font-medium text-muted-foreground">
                                        Status
                                    </th>
                                    <th class="px-4 py-3 text-left text-sm font-medium text-muted-foreground">
                                        <button
                                            @click="sort('created_at')"
                                            class="flex items-center gap-2 hover:text-foreground transition-colors"
                                        >
                                            Dibuat
                                            <component :is="getSortIcon('created_at')" class="h-4 w-4" />
                                        </button>
                                    </th>
                                    <th class="px-4 py-3 text-right text-sm font-medium text-muted-foreground">
                                        Aksi
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr
                                    v-for="user in props.users.data"
                                    :key="user.id"
                                    class="border-b transition-colors hover:bg-muted/50"
                                >
                                    <td class="px-4 py-3">
                                        <div class="flex items-center gap-2">
                                            <div class="flex h-8 w-8 items-center justify-center rounded-full bg-primary/10">
                                                <UserIcon class="h-4 w-4 text-primary" />
                                            </div>
                                            <span class="font-medium">{{ user.name }}</span>
                                        </div>
                                    </td>
                                    <td class="px-4 py-3 text-sm text-muted-foreground">
                                        {{ user.email }}
                                    </td>
                                    <td class="px-4 py-3">
                                        <StatusBadge
                                            :role="user.role"
                                            :icon="user.role === 'admin' ? Shield : undefined"
                                            size="sm"
                                        />
                                    </td>
                                    <td class="px-4 py-3">
                                        <Badge
                                            v-if="user.email_verified_at"
                                            variant="outline"
                                            class="bg-green-500/10 text-green-700 dark:text-green-400 border-green-500/20"
                                        >
                                            Verified
                                        </Badge>
                                        <Badge
                                            v-else
                                            variant="outline"
                                            class="bg-yellow-500/10 text-yellow-700 dark:text-yellow-400 border-yellow-500/20"
                                        >
                                            Unverified
                                        </Badge>
                                    </td>
                                    <td class="px-4 py-3 text-sm text-muted-foreground">
                                        {{ new Date(user.created_at).toLocaleDateString('id-ID') }}
                                    </td>
                                    <td class="px-4 py-3">
                                        <div class="flex items-center justify-end gap-2">
                                            <Button
                                                as-child
                                                variant="ghost"
                                                size="sm"
                                                class="rounded-xl"
                                            >
                                                <Link :href="`/users/${user.id}/edit`">
                                                    <Edit class="h-4 w-4" />
                                                </Link>
                                            </Button>
                                            <Dialog>
                                                <DialogTrigger as-child>
                                                    <Button
                                                        variant="ghost"
                                                        size="sm"
                                                        class="text-destructive hover:text-destructive"
                                                    >
                                                        <Trash2 class="h-4 w-4" />
                                                    </Button>
                                                </DialogTrigger>
                                                <DialogContent>
                                                    <DialogHeader>
                                                        <DialogTitle>
                                                            Hapus User?
                                                        </DialogTitle>
                                                        <DialogDescription>
                                                            Apakah Anda yakin ingin menghapus user
                                                            <strong>{{ user.name }}</strong>? Tindakan ini tidak dapat
                                                            dibatalkan.
                                                        </DialogDescription>
                                                    </DialogHeader>
                                                    <DialogFooter>
                                                        <DialogClose as-child>
                                                            <Button variant="outline">Batal</Button>
                                                        </DialogClose>
                                                        <Button
                                                            @click="deleteUser(user.id)"
                                                            variant="destructive"
                                                        >
                                                            Hapus
                                                        </Button>
                                                    </DialogFooter>
                                                </DialogContent>
                                            </Dialog>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div
                        v-if="props.users.last_page > 1"
                        class="mt-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between"
                    >
                        <div class="text-sm text-muted-foreground">
                            Menampilkan {{ (props.users.current_page - 1) * props.users.per_page + 1 }} sampai
                            {{ Math.min(props.users.current_page * props.users.per_page, props.users.total) }} dari
                            {{ props.users.total }} user
                        </div>
                        <div class="flex items-center gap-2">
                            <Button
                                variant="outline"
                                size="sm"
                                class="rounded-xl"
                                :disabled="props.users.current_page === 1"
                                @click="router.get(`/users?${buildPaginationQuery(props.users.current_page - 1)}`)"
                            >
                                <ChevronLeft class="h-4 w-4" />
                            </Button>
                            
                            <div class="flex gap-1">
                                <template v-for="page in getPageNumbers()" :key="page">
                                    <Button
                                        v-if="page !== '...'"
                                        variant="outline"
                                        size="sm"
                                        class="rounded-xl min-w-[2.5rem]"
                                        :class="{ 'bg-primary text-primary-foreground': page === props.users.current_page }"
                                        @click="router.get(`/users?${buildPaginationQuery(page)}`)"
                                    >
                                        {{ page }}
                                    </Button>
                                    <span v-else class="px-2 py-1 text-sm text-muted-foreground">...</span>
                                </template>
                            </div>
                            
                            <Button
                                variant="outline"
                                size="sm"
                                class="rounded-xl"
                                :disabled="props.users.current_page === props.users.last_page"
                                @click="router.get(`/users?${buildPaginationQuery(props.users.current_page + 1)}`)"
                            >
                                <ChevronRight class="h-4 w-4" />
                            </Button>
                        </div>
                    </div>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>
