<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import PageHeader from '@/components/PageHeader.vue';
import EmptyState from '@/components/EmptyState.vue';
import StatusBadge from '@/components/StatusBadge.vue';
import PullToRefresh from '@/components/PullToRefresh.vue';
import SwipeableListItem from '@/components/SwipeableListItem.vue';
import { useStatusColors } from '@/composables/useStatusColors';
import { useHaptic } from '@/composables/useHaptic';
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Badge } from '@/components/ui/badge';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import { Plus, Search, Edit, Trash2, User as UserIcon, Shield, Users, ChevronDown, ArrowUpDown, ArrowUp, ArrowDown, ChevronLeft, ChevronRight } from 'lucide-vue-next';
import { ref, computed, onMounted } from 'vue';
import EditUserModal from '@/components/EditUserModal.vue';

const { trigger } = useHaptic();
const isLoaded = ref(false);

onMounted(() => {
    requestAnimationFrame(() => {
        isLoaded.value = true;
    });
});
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

const showEditModal = ref(false);
const selectedUser = ref<User | null>(null);

const openEditModal = (user: User) => {
    trigger('light');
    selectedUser.value = user;
    showEditModal.value = true;
};

// Stagger delay for list items
const getStaggerClass = (index: number): string => {
    const staggerIndex = Math.min(index + 1, 15);
    return `stagger-${staggerIndex}`;
};

// Handle swipe delete
const handleSwipeDelete = async (userId: number) => {
    trigger('heavy');
    deleteUser(userId);
};
</script>

<template>
    <Head title="User Management" />

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
                        title="User Management"
                        description="Kelola pengguna sistem"
                    />
                    <Button 
                        as-child 
                        size="lg" 
                        class="h-12 w-full gap-2 rounded-xl press-effect btn-gradient sm:w-auto sm:h-11"
                    >
                        <Link href="/users/create" @click="trigger('medium')">
                            <Plus class="h-5 w-5" />
                            Tambah User
                        </Link>
                    </Button>
                </div>

            <!-- Filters with spring animation -->
            <Card 
                :class="[
                    'border rounded-xl',
                    'transition-all duration-500 delay-100',
                    isLoaded ? 'translate-y-0 opacity-100' : 'translate-y-4 opacity-0',
                ]"
            >
                <CardContent class="pt-6">
                    <form @submit.prevent="applyFilters" class="space-y-4">
                        <!-- Search Input -->
                        <div class="space-y-2 form-field-focus">
                            <Label class="text-sm font-medium">Cari User</Label>
                            <div class="relative">
                                <Search class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-muted-foreground" />
                                <Input
                                    v-model="form.search"
                                    type="text"
                                    placeholder="Cari nama atau email..."
                                    class="h-11 pl-10 text-base rounded-xl ios-input-focus md:h-10 md:text-sm"
                                    @input="applyFilters"
                                />
                            </div>
                        </div>
                        
                        <!-- Role Filter -->
                        <div class="space-y-2">
                            <Label class="text-sm font-medium">Role</Label>
                            <DropdownMenu>
                                <DropdownMenuTrigger as-child>
                                    <Button
                                        variant="outline"
                                        class="w-full h-11 justify-between rounded-xl press-effect"
                                        :class="{ 'border-destructive': form.errors.role }"
                                        @click="trigger('light')"
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
                        
                        <!-- Action Buttons -->
                        <div class="flex gap-2">
                            <Button
                                type="button"
                                variant="outline"
                                @click="trigger('light'); resetFilters()"
                                :disabled="form.processing"
                                class="flex-1 h-11 rounded-xl press-effect"
                            >
                                Reset
                            </Button>
                        </div>
                    </form>
                </CardContent>
            </Card>

            <!-- Users Table with spring animation -->
            <Card 
                :class="[
                    'border rounded-xl',
                    'transition-all duration-500 delay-200',
                    isLoaded ? 'translate-y-0 opacity-100' : 'translate-y-4 opacity-0',
                ]"
            >
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
                    <!-- Mobile View: Cards with swipe-to-delete -->
                    <template v-else>
                        <div class="block space-y-3 md:hidden">
                            <SwipeableListItem
                                v-for="(user, index) in props.users.data"
                                :key="user.id"
                                @delete="handleSwipeDelete(user.id)"
                            >
                                <Card
                                    :class="[
                                        'border rounded-xl p-4 mobile-card-press',
                                        isLoaded ? 'animate-spring-up' : 'opacity-0',
                                        getStaggerClass(index),
                                    ]"
                                >
                                <div class="space-y-3">
                                    <!-- User Header -->
                                    <div class="flex items-start justify-between gap-3">
                                        <div class="flex items-center gap-3 flex-1 min-w-0">
                                            <div class="flex h-10 w-10 items-center justify-center rounded-full bg-primary/10 flex-shrink-0">
                                                <UserIcon class="h-5 w-5 text-primary" />
                                            </div>
                                            <div class="flex-1 min-w-0">
                                                <p class="font-semibold text-foreground truncate">{{ user.name }}</p>
                                                <p class="text-sm text-muted-foreground truncate">{{ user.email }}</p>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <!-- User Details Grid -->
                                    <div class="grid grid-cols-2 gap-3 text-sm">
                                        <div>
                                            <p class="text-muted-foreground mb-1">Role</p>
                                            <StatusBadge
                                                :role="user.role"
                                                :icon="user.role === 'admin' ? Shield : undefined"
                                                size="sm"
                                            />
                                        </div>
                                        <div>
                                            <p class="text-muted-foreground mb-1">Status</p>
                                            <Badge
                                                v-if="user.email_verified_at"
                                                variant="outline"
                                                class="bg-green-500/10 text-green-700 dark:text-green-400 border-green-500/20 text-xs"
                                            >
                                                Verified
                                            </Badge>
                                            <Badge
                                                v-else
                                                variant="outline"
                                                class="bg-yellow-500/10 text-yellow-700 dark:text-yellow-400 border-yellow-500/20 text-xs"
                                            >
                                                Unverified
                                            </Badge>
                                        </div>
                                        <div>
                                            <p class="text-muted-foreground mb-1">Dibuat</p>
                                            <p class="font-medium">{{ new Date(user.created_at).toLocaleDateString('id-ID') }}</p>
                                        </div>
                                    </div>
                                    
                                    <!-- Actions -->
                                    <div class="flex items-center gap-2 pt-2 border-t">
                                        <Button
                                            variant="outline"
                                            size="sm"
                                            class="flex-1 gap-2 rounded-xl press-effect"
                                            @click="openEditModal(user)"
                                        >
                                            <Edit class="h-4 w-4" />
                                            Edit
                                        </Button>
                                        <Dialog>
                                            <DialogTrigger as-child>
                                                <Button
                                                    variant="destructive"
                                                    size="sm"
                                                    class="flex-1 gap-2 rounded-xl press-effect"
                                                    @click="trigger('medium')"
                                                >
                                                    <Trash2 class="h-4 w-4" />
                                                    Hapus
                                                </Button>
                                            </DialogTrigger>
                                            <DialogContent class="sm:max-w-md">
                                                <DialogHeader>
                                                    <DialogTitle>Hapus User?</DialogTitle>
                                                    <DialogDescription>
                                                        Apakah Anda yakin ingin menghapus user
                                                        <strong>{{ user.name }}</strong>? Tindakan ini tidak dapat dibatalkan.
                                                    </DialogDescription>
                                                </DialogHeader>
                                                <DialogFooter class="flex-col gap-2 sm:flex-row">
                                                    <DialogClose as-child>
                                                        <Button variant="outline" class="w-full sm:w-auto rounded-xl h-11 press-effect">
                                                            Batal
                                                        </Button>
                                                    </DialogClose>
                                                    <Button
                                                        @click="trigger('heavy'); deleteUser(user.id)"
                                                        variant="destructive"
                                                        class="w-full sm:w-auto rounded-xl h-11 press-effect"
                                                    >
                                                        Hapus
                                                    </Button>
                                                </DialogFooter>
                                            </DialogContent>
                                        </Dialog>
                                    </div>
                                </div>
                            </Card>
                            </SwipeableListItem>
                        </div>
                        <!-- Desktop View: Table -->
                        <div class="hidden md:block overflow-x-auto">
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
                                    v-for="(user, index) in props.users.data"
                                    :key="user.id"
                                    :class="[
                                        'border-b list-row-interactive',
                                        isLoaded ? 'animate-fade-up' : 'opacity-0',
                                        getStaggerClass(index),
                                    ]"
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
                                                variant="ghost"
                                                size="sm"
                                                class="rounded-xl press-effect"
                                                @click="openEditModal(user)"
                                            >
                                                <Edit class="h-4 w-4" />
                                            </Button>
                                            <Dialog>
                                                <DialogTrigger as-child>
                                                    <Button
                                                        variant="ghost"
                                                        size="sm"
                                                        class="text-destructive hover:text-destructive rounded-xl press-effect"
                                                        @click="trigger('medium')"
                                                    >
                                                        <Trash2 class="h-4 w-4" />
                                                    </Button>
                                                </DialogTrigger>
                                                <DialogContent class="sm:max-w-md">
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
                                                    <DialogFooter class="flex-col gap-2 sm:flex-row">
                                                        <DialogClose as-child>
                                                            <Button variant="outline" class="w-full sm:w-auto rounded-xl h-11 press-effect">
                                                                Batal
                                                            </Button>
                                                        </DialogClose>
                                                        <Button
                                                            @click="trigger('heavy'); deleteUser(user.id)"
                                                            variant="destructive"
                                                            class="w-full sm:w-auto rounded-xl h-11 press-effect"
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
                    </template>

                    <!-- Pagination -->
                    <div
                        v-if="props.users.last_page > 1"
                        class="mt-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between"
                    >
                        <p class="text-xs sm:text-sm text-muted-foreground text-center sm:text-left">
                            Menampilkan {{ (props.users.current_page - 1) * props.users.per_page + 1 }} sampai
                            {{ Math.min(props.users.current_page * props.users.per_page, props.users.total) }} dari
                            {{ props.users.total }} user
                        </p>
                        <div class="flex items-center gap-1 sm:gap-2">
                            <Button
                                variant="outline"
                                size="sm"
                                class="h-9 w-9 rounded-xl p-0 press-effect"
                                :disabled="props.users.current_page === 1"
                                @click="trigger('light'); router.get(`/users?${buildPaginationQuery(props.users.current_page - 1)}`)"
                            >
                                <ChevronLeft class="h-4 w-4" />
                            </Button>
                            
                            <div class="hidden xs:flex gap-1">
                                <template v-for="page in getPageNumbers()" :key="page">
                                    <Button
                                        v-if="page !== '...'"
                                        variant="outline"
                                        size="sm"
                                        class="h-9 min-w-[2.5rem] rounded-xl text-xs press-effect"
                                        :class="{ 'bg-primary text-primary-foreground': page === props.users.current_page }"
                                        @click="trigger('light'); router.get(`/users?${buildPaginationQuery(page)}`)"
                                    >
                                        {{ page }}
                                    </Button>
                                    <span v-else class="px-2 py-1 text-xs text-muted-foreground">...</span>
                                </template>
                            </div>
                            
                            <Button
                                variant="outline"
                                size="sm"
                                class="h-9 w-9 rounded-xl p-0 press-effect"
                                :disabled="props.users.current_page === props.users.last_page"
                                @click="trigger('light'); router.get(`/users?${buildPaginationQuery(props.users.current_page + 1)}`)"
                            >
                                <ChevronRight class="h-4 w-4" />
                            </Button>
                        </div>
                    </div>
                </CardContent>
            </Card>
            </div>
        </PullToRefresh>

        <!-- Edit User Modal -->
        <EditUserModal
            v-if="selectedUser"
            :is-open="showEditModal"
            :user="selectedUser"
            @update:is-open="showEditModal = $event"
        />
    </AppLayout>
</template>
