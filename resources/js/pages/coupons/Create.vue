<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import InputError from '@/components/InputError.vue';
import { type BreadcrumbItem } from '@/types';
import { router } from '@inertiajs/vue3';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Kupon',
        href: '/coupons',
    },
    {
        title: 'Buat Kupon Baru',
        href: '/coupons/create',
    },
];

const form = useForm({
    customer_name: '',
    customer_phone: '',
    customer_email: '',
    customer_social_media: '',
    type: '',
    description: '',
    expires_at: '',
});

const submit = () => {
    form.post('/coupons', {
        preserveScroll: true,
        onSuccess: () => {
            form.reset();
        },
    });
};
</script>

<template>
    <Head title="Buat Kupon Baru" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="container mx-auto px-4 py-6 sm:px-6 lg:px-8">
            <!-- Mobile-first header -->
            <div class="mb-6 sm:mb-8">
                <h1 class="text-2xl font-semibold text-foreground sm:text-3xl">
                    Buat Kupon Baru
                </h1>
                <p class="mt-2 text-sm text-muted-foreground sm:text-base">
                    Isi informasi pelanggan dan detail kupon
                </p>
            </div>

            <form @submit.prevent="submit" class="space-y-6">
                <!-- Customer Info Card - Mobile First -->
                <Card class="w-full">
                    <CardHeader class="pb-4">
                        <CardTitle class="text-lg sm:text-xl">Informasi Pelanggan</CardTitle>
                        <CardDescription class="text-sm">
                            Data pelanggan yang akan menerima kupon
                        </CardDescription>
                    </CardHeader>
                    <CardContent class="space-y-4">
                        <!-- Customer Name -->
                        <div class="space-y-2">
                            <Label for="customer_name" class="text-sm font-medium">
                                Nama Pelanggan <span class="text-destructive">*</span>
                            </Label>
                            <Input
                                id="customer_name"
                                v-model="form.customer_name"
                                type="text"
                                placeholder="Masukkan nama pelanggan"
                                class="w-full"
                                :class="{ 'border-destructive': form.errors.customer_name }"
                            />
                            <InputError :message="form.errors.customer_name" />
                        </div>

                        <!-- Customer Phone -->
                        <div class="space-y-2">
                            <Label for="customer_phone" class="text-sm font-medium">
                                Nomor Telepon <span class="text-destructive">*</span>
                            </Label>
                            <Input
                                id="customer_phone"
                                v-model="form.customer_phone"
                                type="tel"
                                placeholder="0812-3456-7890 atau +6281234567890"
                                class="w-full"
                                :class="{ 'border-destructive': form.errors.customer_phone }"
                            />
                            <InputError :message="form.errors.customer_phone" />
                            <p class="text-xs text-muted-foreground">
                                Format: 08xx atau +628xx (akan dinormalisasi otomatis)
                            </p>
                        </div>

                        <!-- Customer Email -->
                        <div class="space-y-2">
                            <Label for="customer_email" class="text-sm font-medium">
                                Email (Opsional)
                            </Label>
                            <Input
                                id="customer_email"
                                v-model="form.customer_email"
                                type="email"
                                placeholder="pelanggan@example.com"
                                class="w-full"
                                :class="{ 'border-destructive': form.errors.customer_email }"
                            />
                            <InputError :message="form.errors.customer_email" />
                        </div>

                        <!-- Social Media -->
                        <div class="space-y-2">
                            <Label for="customer_social_media" class="text-sm font-medium">
                                Media Sosial (Opsional)
                            </Label>
                            <Input
                                id="customer_social_media"
                                v-model="form.customer_social_media"
                                type="text"
                                placeholder="@username atau link profil"
                                class="w-full"
                                :class="{ 'border-destructive': form.errors.customer_social_media }"
                            />
                            <InputError :message="form.errors.customer_social_media" />
                        </div>
                    </CardContent>
                </Card>

                <!-- Coupon Info Card -->
                <Card class="w-full">
                    <CardHeader class="pb-4">
                        <CardTitle class="text-lg sm:text-xl">Detail Kupon</CardTitle>
                        <CardDescription class="text-sm">
                            Informasi tentang kupon yang akan dibuat
                        </CardDescription>
                    </CardHeader>
                    <CardContent class="space-y-4">
                        <!-- Type -->
                        <div class="space-y-2">
                            <Label for="type" class="text-sm font-medium">
                                Jenis Kupon <span class="text-destructive">*</span>
                            </Label>
                            <Input
                                id="type"
                                v-model="form.type"
                                type="text"
                                placeholder="Contoh: Gratis 1 Kopi, Diskon 20%, dll"
                                class="w-full"
                                :class="{ 'border-destructive': form.errors.type }"
                            />
                            <InputError :message="form.errors.type" />
                        </div>

                        <!-- Description -->
                        <div class="space-y-2">
                            <Label for="description" class="text-sm font-medium">
                                Deskripsi <span class="text-destructive">*</span>
                            </Label>
                            <textarea
                                id="description"
                                v-model="form.description"
                                rows="4"
                                placeholder="Jelaskan detail kupon, syarat dan ketentuan..."
                                class="flex min-h-[80px] w-full rounded-md border border-input bg-transparent px-3 py-2 text-sm shadow-xs placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-[3px] focus-visible:ring-ring/50 disabled:cursor-not-allowed disabled:opacity-50 dark:bg-input/30 dark:border-input"
                                :class="{ 'border-destructive': form.errors.description }"
                            />
                            <InputError :message="form.errors.description" />
                        </div>

                        <!-- Expiration Date -->
                        <div class="space-y-2">
                            <Label for="expires_at" class="text-sm font-medium">
                                Tanggal Kedaluwarsa (Opsional)
                            </Label>
                            <Input
                                id="expires_at"
                                v-model="form.expires_at"
                                type="date"
                                class="w-full"
                                :class="{ 'border-destructive': form.errors.expires_at }"
                            />
                            <InputError :message="form.errors.expires_at" />
                            <p class="text-xs text-muted-foreground">
                                Kosongkan jika kupon tidak memiliki batas waktu
                            </p>
                        </div>
                    </CardContent>
                </Card>

                <!-- Action Buttons - Mobile First -->
                <div class="flex flex-col-reverse gap-3 sm:flex-row sm:justify-end sm:gap-4">
                    <Button
                        type="button"
                        variant="outline"
                        class="w-full sm:w-auto"
                        @click="router.visit('/coupons')"
                        :disabled="form.processing"
                    >
                        Batal
                    </Button>
                    <Button
                        type="submit"
                        class="w-full sm:w-auto"
                        :disabled="form.processing"
                    >
                        <span v-if="form.processing">Membuat...</span>
                        <span v-else>Buat Kupon</span>
                    </Button>
                </div>
            </form>
        </div>
    </AppLayout>
</template>
