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
import { useHaptic } from '@/composables/useHaptic';
import { ref, onMounted } from 'vue';

const { trigger } = useHaptic();
const isLoaded = ref(false);

onMounted(() => {
    requestAnimationFrame(() => {
        isLoaded.value = true;
    });
});

const form = useForm({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
    role: 'user',
});

const submit = () => {
    trigger('medium');
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
        <div class="flex h-full flex-1 flex-col gap-4 sm:gap-6 overflow-x-auto p-4 md:p-6">
            <!-- Header with spring animation -->
            <div
                :class="[
                    'transition-all duration-500',
                    isLoaded ? 'translate-y-0 opacity-100' : 'translate-y-4 opacity-0',
                ]"
            >
                <PageHeader
                    title="Tambah User Baru"
                    description="Buat akun user baru untuk sistem"
                />
            </div>

            <!-- Form with spring animation -->
            <Card 
                :class="[
                    'border rounded-xl',
                    'transition-all duration-500 delay-100',
                    isLoaded ? 'translate-y-0 opacity-100' : 'translate-y-4 opacity-0',
                ]"
            >
                <CardHeader class="pb-4">
                    <div class="flex items-center gap-2">
                        <User class="h-5 w-5 text-primary" />
                        <CardTitle class="text-lg font-semibold">Informasi User</CardTitle>
                    </div>
                    <CardDescription class="text-sm mt-1">
                        Lengkapi informasi user yang akan dibuat
                    </CardDescription>
                </CardHeader>
                <CardContent>
                    <form @submit.prevent="submit" class="space-y-6">
                        <!-- Name -->
                        <div class="space-y-2 form-field-focus">
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
                                class="h-11 text-base rounded-xl ios-input-focus md:h-10 md:text-sm"
                                :class="{ 'border-destructive': form.errors.name }"
                            />
                            <InputError :message="form.errors.name" />
                        </div>

                        <!-- Email -->
                        <div class="space-y-2 form-field-focus">
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
                                class="h-11 text-base rounded-xl ios-input-focus md:h-10 md:text-sm"
                                :class="{ 'border-destructive': form.errors.email }"
                            />
                            <InputError :message="form.errors.email" />
                        </div>

                        <!-- Password -->
                        <div class="space-y-2 form-field-focus">
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
                                class="h-11 text-base rounded-xl ios-input-focus md:h-10 md:text-sm"
                                :class="{ 'border-destructive': form.errors.password }"
                            />
                            <p class="text-xs text-muted-foreground">
                                Password harus minimal 8 karakter
                            </p>
                            <InputError :message="form.errors.password" />
                        </div>

                        <!-- Password Confirmation -->
                        <div class="space-y-2 form-field-focus">
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
                                class="h-11 text-base rounded-xl ios-input-focus md:h-10 md:text-sm"
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
                                        class="w-full h-11 justify-between rounded-xl press-effect"
                                        :class="{ 'border-destructive': form.errors.role }"
                                        @click="trigger('light')"
                                    >
                                        {{ form.role === 'admin' ? 'Admin' : 'User' }}
                                        <ChevronDown class="ml-2 h-4 w-4 opacity-50" />
                                    </Button>
                                </DropdownMenuTrigger>
                                <DropdownMenuContent class="w-full">
                                    <DropdownMenuItem
                                        @click="trigger('light'); form.role = 'user'"
                                        :class="{ 'bg-accent': form.role === 'user' }"
                                    >
                                        User
                                    </DropdownMenuItem>
                                    <DropdownMenuItem
                                        @click="trigger('light'); form.role = 'admin'"
                                        :class="{ 'bg-accent': form.role === 'admin' }"
                                    >
                                        Admin
                                    </DropdownMenuItem>
                                </DropdownMenuContent>
                            </DropdownMenu>
                            <InputError :message="form.errors.role" />
                        </div>

                        <!-- Actions -->
                        <div 
                            :class="[
                                'flex flex-col gap-3 sm:flex-row sm:justify-end',
                                'transition-all duration-500 delay-200',
                                isLoaded ? 'translate-y-0 opacity-100' : 'translate-y-4 opacity-0',
                            ]"
                        >
                            <Button
                                type="submit"
                                :disabled="form.processing"
                                size="lg"
                                class="h-12 w-full gap-2 rounded-xl btn-gradient press-effect sm:w-auto sm:h-11"
                            >
                                {{ form.processing ? 'Menyimpan...' : 'Simpan' }}
                            </Button>
                            <Button
                                type="button"
                                variant="outline"
                                as-child
                                size="lg"
                                class="h-11 w-full rounded-xl press-effect sm:w-auto"
                            >
                                <a href="/users" @click="trigger('light')">Batal</a>
                            </Button>
                        </div>
                    </form>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>
