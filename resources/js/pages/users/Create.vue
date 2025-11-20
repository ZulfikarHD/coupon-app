<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui';
import { User, Mail, Lock, Shield } from 'lucide-vue-next';
import InputError from '@/components/InputError.vue';

const form = useForm({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
    role: 'user',
});

const submit = () => {
    form.post('/users', {
        preserveScroll: true,
    });
};

const breadcrumbs = [
    {
        title: 'User Management',
        href: '/users',
    },
    {
        title: 'Tambah User',
        href: '/users/create',
    },
];
</script>

<template>
    <Head title="Tambah User" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4 md:p-6">
            <!-- Header -->
            <div class="space-y-1">
                <h1 class="text-2xl font-semibold tracking-tight md:text-3xl">
                    Tambah User Baru
                </h1>
                <p class="text-sm text-muted-foreground md:text-base">
                    Buat akun user baru untuk sistem
                </p>
            </div>

            <!-- Form -->
            <Card class="border-2">
                <CardHeader>
                    <CardTitle class="flex items-center gap-2">
                        <User class="h-5 w-5 text-primary" />
                        Informasi User
                    </CardTitle>
                    <CardDescription>
                        Lengkapi informasi user yang akan dibuat
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

                        <!-- Password -->
                        <div class="space-y-2">
                            <Label for="password">
                                <div class="flex items-center gap-2">
                                    <Lock class="h-4 w-4" />
                                    Password
                                </div>
                            </Label>
                            <Input
                                id="password"
                                v-model="form.password"
                                type="password"
                                placeholder="Minimal 8 karakter"
                                required
                                autocomplete="new-password"
                                :class="{ 'border-destructive': form.errors.password }"
                            />
                            <InputError :message="form.errors.password" />
                        </div>

                        <!-- Password Confirmation -->
                        <div class="space-y-2">
                            <Label for="password_confirmation">
                                <div class="flex items-center gap-2">
                                    <Lock class="h-4 w-4" />
                                    Konfirmasi Password
                                </div>
                            </Label>
                            <Input
                                id="password_confirmation"
                                v-model="form.password_confirmation"
                                type="password"
                                placeholder="Ulangi password"
                                required
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
                            <Select v-model="form.role">
                                <SelectTrigger
                                    :class="{ 'border-destructive': form.errors.role }"
                                >
                                    <SelectValue placeholder="Pilih role" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="user">User</SelectItem>
                                    <SelectItem value="admin">Admin</SelectItem>
                                </SelectContent>
                            </Select>
                            <InputError :message="form.errors.role" />
                        </div>

                        <!-- Actions -->
                        <div class="flex items-center gap-4">
                            <Button type="submit" :disabled="form.processing" size="lg">
                                {{ form.processing ? 'Menyimpan...' : 'Simpan' }}
                            </Button>
                            <Button
                                type="button"
                                variant="outline"
                                as-child
                                size="lg"
                            >
                                <a href="/users">Batal</a>
                            </Button>
                        </div>
                    </form>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>
