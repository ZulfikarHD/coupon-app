<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Badge } from '@/components/ui/badge';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui';
import { Plus, Search, Edit, Trash2, User as UserIcon, Shield, Users } from 'lucide-vue-next';
import { ref } from 'vue';
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
    };
}

const props = defineProps<Props>();

const form = useForm({
    search: props.filters.search || '',
    role: props.filters.role || 'all',
});

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

const roleColors = {
    admin: 'bg-purple-500/10 text-purple-700 dark:text-purple-400 border-purple-500/20',
    user: 'bg-blue-500/10 text-blue-700 dark:text-blue-400 border-blue-500/20',
};

const roleLabels = {
    admin: 'Admin',
    user: 'User',
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
        <div class="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4 md:p-6">
            <!-- Header -->
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div class="space-y-1">
                    <h1 class="text-2xl font-semibold tracking-tight md:text-3xl">
                        User Management
                    </h1>
                    <p class="text-sm text-muted-foreground md:text-base">
                        Kelola pengguna sistem
                    </p>
                </div>
                <Button as-child size="lg" class="gap-2">
                    <Link href="/users/create">
                        <Plus class="h-5 w-5" />
                        Tambah User
                    </Link>
                </Button>
            </div>

            <!-- Filters -->
            <Card class="border-2">
                <CardContent class="pt-6">
                    <form @submit.prevent="applyFilters" class="flex flex-col gap-4 sm:flex-row">
                        <div class="flex-1">
                            <div class="relative">
                                <Search class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-muted-foreground" />
                                <Input
                                    v-model="form.search"
                                    type="text"
                                    placeholder="Cari nama atau email..."
                                    class="pl-10"
                                />
                            </div>
                        </div>
                        <div class="w-full sm:w-48">
                            <Select v-model="form.role" @update:model-value="applyFilters">
                                <SelectTrigger>
                                    <SelectValue placeholder="Semua Role" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="all">Semua Role</SelectItem>
                                    <SelectItem value="admin">Admin</SelectItem>
                                    <SelectItem value="user">User</SelectItem>
                                </SelectContent>
                            </Select>
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
            <Card class="border-2">
                <CardHeader>
                    <CardTitle class="flex items-center gap-2">
                        <Users class="h-5 w-5 text-primary" />
                        Daftar User ({{ props.users.total }})
                    </CardTitle>
                    <CardDescription>
                        Total pengguna terdaftar dalam sistem
                    </CardDescription>
                </CardHeader>
                <CardContent>
                    <div v-if="props.users.data.length === 0" class="py-12 text-center">
                        <UserIcon class="mx-auto h-12 w-12 text-muted-foreground mb-4" />
                        <p class="text-sm text-muted-foreground">
                            Tidak ada user ditemukan
                        </p>
                    </div>
                    <div v-else class="overflow-x-auto">
                        <table class="w-full">
                            <thead>
                                <tr class="border-b">
                                    <th class="px-4 py-3 text-left text-sm font-medium text-muted-foreground">
                                        Nama
                                    </th>
                                    <th class="px-4 py-3 text-left text-sm font-medium text-muted-foreground">
                                        Email
                                    </th>
                                    <th class="px-4 py-3 text-left text-sm font-medium text-muted-foreground">
                                        Role
                                    </th>
                                    <th class="px-4 py-3 text-left text-sm font-medium text-muted-foreground">
                                        Status
                                    </th>
                                    <th class="px-4 py-3 text-left text-sm font-medium text-muted-foreground">
                                        Dibuat
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
                                        <Badge
                                            :class="roleColors[user.role]"
                                            variant="outline"
                                            class="border"
                                        >
                                            <Shield
                                                v-if="user.role === 'admin'"
                                                class="mr-1 h-3 w-3"
                                            />
                                            {{ roleLabels[user.role] }}
                                        </Badge>
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
                        class="mt-6 flex items-center justify-between"
                    >
                        <div class="text-sm text-muted-foreground">
                            Menampilkan {{ (props.users.current_page - 1) * props.users.per_page + 1 }} sampai
                            {{ Math.min(props.users.current_page * props.users.per_page, props.users.total) }} dari
                            {{ props.users.total }} user
                        </div>
                        <div class="flex gap-2">
                            <Button
                                variant="outline"
                                size="sm"
                                :disabled="props.users.current_page === 1"
                                @click="router.get(props.users.current_page - 1 ? `/users?page=${props.users.current_page - 1}` : '/users')"
                            >
                                Sebelumnya
                            </Button>
                            <Button
                                variant="outline"
                                size="sm"
                                :disabled="props.users.current_page === props.users.last_page"
                                @click="router.get(`/users?page=${props.users.current_page + 1}`)"
                            >
                                Selanjutnya
                            </Button>
                        </div>
                    </div>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>
