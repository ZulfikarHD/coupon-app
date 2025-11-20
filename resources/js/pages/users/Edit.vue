<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import PageHeader from '@/components/PageHeader.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import { User, Mail, Lock, Shield, ChevronDown } from 'lucide-vue-next';
import InputError from '@/components/InputError.vue';

interface User {
    id: number;
    name: string;
    email: string;
    role: 'admin' | 'user';
}

interface Props {
    user: User;
}

const props = defineProps<Props>();

const form = useForm({
    name: props.user.name,
    email: props.user.email,
    password: '',
    password_confirmation: '',
    role: props.user.role,
});

const submit = () => {
    form.put(`/users/${props.user.id}`, {
        preserveScroll: true,
    });
};

const breadcrumbs = [
    {
        title: 'User Management',
        href: '/users',
    },
    {
        title: 'Edit User',
        href: `/users/${props.user.id}/edit`,
    },
];
</script>

<template>
    <Head title="Edit User" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 overflow-x-auto p-4 md:p-6">
            <!-- Header -->
            <PageHeader
                title="Edit User"
                description="Perbarui informasi user"
            />

            <!-- Form -->
            <Card class="border rounded-xl">
                <CardHeader class="pb-4">
                    <div class="flex items-center gap-2">
                        <User class="h-5 w-5 text-primary" />
                        <CardTitle class="text-lg font-semibold">Informasi User</CardTitle>
                    </div>
                    <CardDescription class="text-sm mt-1">
                        Perbarui informasi user yang dipilih
                    </CardDescription>
                </CardHeader>
                <CardContent>
                    <form @submit.prevent="submit" class="space-y-6">
                        <!-- Name -->
                        <div class="space-y-2">
                            <Label for="name">
                                <div class="flex items-center gap-2">
                                    <User class="h-4 w-4" />
                                    Nama Lengkap
                                </div>
                            </Label>
                            <Input
                                id="name"
                                v-model="form.name"
                                type="text"
                                placeholder="Masukkan nama lengkap"
                                required
                                autocomplete="name"
                                class="rounded-xl"
                                :class="{ 'border-destructive': form.errors.name }"
                            />
                            <InputError :message="form.errors.name" />
                        </div>

                        <!-- Email -->
                        <div class="space-y-2">
                            <Label for="email">
                                <div class="flex items-center gap-2">
                                    <Mail class="h-4 w-4" />
                                    Email
                                </div>
                            </Label>
                            <Input
                                id="email"
                                v-model="form.email"
                                type="email"
                                placeholder="user@example.com"
                                required
                                autocomplete="email"
                                :class="{ 'border-destructive': form.errors.email }"
                            />
                            <InputError :message="form.errors.email" />
                        </div>

                        <!-- Password (Optional) -->
                        <div class="space-y-2">
                            <Label for="password">
                                <div class="flex items-center gap-2">
                                    <Lock class="h-4 w-4" />
                                    Password Baru (Opsional)
                                </div>
                            </Label>
                            <Input
                                id="password"
                                v-model="form.password"
                                type="password"
                                placeholder="Kosongkan jika tidak ingin mengubah password"
                                autocomplete="new-password"
                                :class="{ 'border-destructive': form.errors.password }"
                            />
                            <p class="text-xs text-muted-foreground">
                                Biarkan kosong jika tidak ingin mengubah password
                            </p>
                            <InputError :message="form.errors.password" />
                        </div>

                        <!-- Password Confirmation (if password is filled) -->
                        <div v-if="form.password" class="space-y-2">
                            <Label for="password_confirmation">
                                <div class="flex items-center gap-2">
                                    <Lock class="h-4 w-4" />
                                    Konfirmasi Password Baru
                                </div>
                            </Label>
                            <Input
                                id="password_confirmation"
                                v-model="form.password_confirmation"
                                type="password"
                                placeholder="Ulangi password baru"
                                autocomplete="new-password"
                                :class="{ 'border-destructive': form.errors.password_confirmation }"
                            />
                            <InputError :message="form.errors.password_confirmation" />
                        </div>

                        <!-- Role -->
                        <div class="space-y-2">
                            <Label for="role">
                                <div class="flex items-center gap-2">
                                    <Shield class="h-4 w-4" />
                                    Role
                                </div>
                            </Label>
                            <DropdownMenu>
                                <DropdownMenuTrigger as-child>
                                    <Button
                                        variant="outline"
                                        class="w-full justify-between"
                                        :class="{ 'border-destructive': form.errors.role }"
                                    >
                                        {{ form.role === 'admin' ? 'Admin' : 'User' }}
                                        <ChevronDown class="ml-2 h-4 w-4 opacity-50" />
                                    </Button>
                                </DropdownMenuTrigger>
                                <DropdownMenuContent class="w-full">
                                    <DropdownMenuItem
                                        @click="form.role = 'user'"
                                        :class="{ 'bg-accent': form.role === 'user' }"
                                    >
                                        User
                                    </DropdownMenuItem>
                                    <DropdownMenuItem
                                        @click="form.role = 'admin'"
                                        :class="{ 'bg-accent': form.role === 'admin' }"
                                    >
                                        Admin
                                    </DropdownMenuItem>
                                </DropdownMenuContent>
                            </DropdownMenu>
                            <InputError :message="form.errors.role" />
                        </div>

                        <!-- Actions -->
                        <div class="flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">
                            <Button
                                type="button"
                                variant="outline"
                                as-child
                                size="lg"
                                class="rounded-xl"
                            >
                                <a href="/users">Batal</a>
                            </Button>
                            <Button type="submit" :disabled="form.processing" size="lg" class="rounded-xl">
                                {{ form.processing ? 'Menyimpan...' : 'Simpan Perubahan' }}
                            </Button>
                        </div>
                    </form>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>
