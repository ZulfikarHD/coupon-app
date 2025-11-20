<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import PageHeader from '@/components/PageHeader.vue';
import InputError from '@/components/InputError.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Checkbox } from '@/components/ui/checkbox';
import { Plus, User, Phone, Mail, Link as LinkIcon, Calendar, FileText } from 'lucide-vue-next';
import { onMounted, ref, watch, computed } from 'vue';

// Helper function to format date as YYYY-MM-DD for date input
const formatDateForInput = (date: Date): string => {
    const year = date.getFullYear();
    const month = String(date.getMonth() + 1).padStart(2, '0');
    const day = String(date.getDate()).padStart(2, '0');
    return `${year}-${month}-${day}`;
};

// Get today's date for min attribute (computed to ensure reactivity)
// HTML5 date inputs support the 'min' attribute in YYYY-MM-DD format
const today = computed(() => formatDateForInput(new Date()));

// Get date 2 months from now for initial value
const getTwoMonthsFromNow = (): string => {
    const date = new Date();
    date.setMonth(date.getMonth() + 2);
    return formatDateForInput(date);
};

const form = useForm({
    type: '',
    description: '',
    first_name: '',
    last_name: '',
    customer_phone: '',
    customer_email: '',
    customer_social_media: '',
    expires_at: '',
});

const neverExpires = ref(false);

// Set initial date to 2 months from now
onMounted(() => {
    form.expires_at = getTwoMonthsFromNow();
});

// Watch neverExpires checkbox
watch(neverExpires, (value) => {
    if (value) {
        // If "Never expires" is checked, clear the expiration date
        form.expires_at = '';
    } else {
        // If unchecked, set to 2 months from now
        form.expires_at = getTwoMonthsFromNow();
    }
});

const submit = () => {
    form.post('/coupons', {
        preserveScroll: true,
    });
};

const breadcrumbs = [
    {
        title: 'Kupon',
        href: '/coupons',
    },
    {
        title: 'Buat Kupon Baru',
        href: '/coupons/create',
    },
];
</script>

<template>
    <Head title="Buat Kupon Baru" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 sm:gap-6 overflow-x-auto p-4 md:p-6">
            <!-- Header -->
            <PageHeader
                title="Buat Kupon Baru"
                description="Isi informasi kupon dan pelanggan di bawah ini"
            />

            <form @submit.prevent="submit" class="space-y-4 sm:space-y-6">
                <!-- Customer Info Section -->
                <Card class="border rounded-xl">
                    <CardHeader class="pb-4">
                        <div class="flex items-center gap-2">
                            <User class="h-5 w-5 text-primary" />
                            <CardTitle class="text-lg font-semibold">Informasi Pelanggan</CardTitle>
                        </div>
                        <CardDescription class="text-sm mt-1">
                            Data pelanggan yang akan menerima kupon
                        </CardDescription>
                    </CardHeader>
                    <CardContent class="space-y-4 sm:space-y-6">
                        <div class="space-y-2">
                            <Label for="first_name" class="text-sm font-medium">
                                Nama Depan <span class="text-destructive">*</span>
                            </Label>
                            <Input
                                id="first_name"
                                v-model="form.first_name"
                                type="text"
                                placeholder="Masukkan nama depan"
                                :class="{ 'border-destructive': form.errors.first_name }"
                                class="h-11 text-base rounded-xl md:h-10 md:text-sm"
                            />
                            <InputError :message="form.errors.first_name" />
                            <p class="text-xs text-muted-foreground mt-1">
                                Hanya huruf yang diperbolehkan
                            </p>
                        </div>

                        <div class="space-y-2">
                            <Label for="last_name" class="text-sm font-medium">
                                Nama Belakang <span class="text-destructive">*</span>
                            </Label>
                            <Input
                                id="last_name"
                                v-model="form.last_name"
                                type="text"
                                placeholder="Masukkan nama belakang"
                                :class="{ 'border-destructive': form.errors.last_name }"
                                class="h-11 text-base rounded-xl md:h-10 md:text-sm"
                            />
                            <InputError :message="form.errors.last_name" />
                            <p class="text-xs text-muted-foreground mt-1">
                                Hanya huruf yang diperbolehkan
                            </p>
                        </div>

                        <div class="space-y-2">
                            <Label for="customer_phone" class="text-sm font-medium">
                                Nomor Telepon <span class="text-destructive">*</span>
                            </Label>
                            <div class="relative">
                                <Phone class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-muted-foreground" />
                                <Input
                                    id="customer_phone"
                                    v-model="form.customer_phone"
                                    type="tel"
                                    placeholder="0812-3456-7890"
                                    :class="{ 'border-destructive': form.errors.customer_phone }"
                                    class="h-11 pl-10 text-base rounded-xl md:h-10 md:text-sm"
                                />
                            </div>
                            <p v-if="form.errors.customer_phone" class="text-sm text-destructive">
                                {{ form.errors.customer_phone }}
                            </p>
                        </div>

                        <div class="space-y-2">
                            <Label for="customer_email" class="text-sm font-medium">
                                Email (Opsional)
                            </Label>
                            <div class="relative">
                                <Mail class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-muted-foreground" />
                                <Input
                                    id="customer_email"
                                    v-model="form.customer_email"
                                    type="email"
                                    placeholder="pelanggan@example.com"
                                    :class="{ 'border-destructive': form.errors.customer_email }"
                                    class="h-11 pl-10 text-base rounded-xl md:h-10 md:text-sm"
                                />
                            </div>
                            <p v-if="form.errors.customer_email" class="text-sm text-destructive">
                                {{ form.errors.customer_email }}
                            </p>
                        </div>

                        <div class="space-y-2">
                            <Label for="customer_social_media" class="text-sm font-medium">
                                Media Sosial (Opsional)
                            </Label>
                            <div class="relative">
                                <LinkIcon class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-muted-foreground" />
                                <Input
                                    id="customer_social_media"
                                    v-model="form.customer_social_media"
                                    type="text"
                                    placeholder="@username atau link"
                                    :class="{ 'border-destructive': form.errors.customer_social_media }"
                                    class="h-11 pl-10 text-base rounded-xl md:h-10 md:text-sm"
                                />
                            </div>
                            <p v-if="form.errors.customer_social_media" class="text-sm text-destructive">
                                {{ form.errors.customer_social_media }}
                            </p>
                        </div>
                    </CardContent>
                </Card>

                <!-- Coupon Info Section -->
                <Card class="border rounded-xl">
                    <CardHeader class="pb-4">
                        <div class="flex items-center gap-2">
                            <FileText class="h-5 w-5 text-primary" />
                            <CardTitle class="text-lg font-semibold">Informasi Kupon</CardTitle>
                        </div>
                        <CardDescription class="text-sm mt-1">
                            Detail kupon yang akan diberikan
                        </CardDescription>
                    </CardHeader>
                    <CardContent class="space-y-4 sm:space-y-6">
                        <div class="space-y-2">
                            <Label for="type" class="text-sm font-medium">
                                Jenis Kupon <span class="text-destructive">*</span>
                            </Label>
                            <Input
                                id="type"
                                v-model="form.type"
                                type="text"
                                placeholder="Contoh: Gratis 1 Kopi, Diskon 20%, dll"
                                :class="{ 'border-destructive': form.errors.type }"
                                class="h-11 text-base rounded-xl md:h-10 md:text-sm"
                            />
                            <p v-if="form.errors.type" class="text-sm text-destructive">
                                {{ form.errors.type }}
                            </p>
                        </div>

                        <div class="space-y-2">
                            <Label for="description" class="text-sm font-medium">
                                Deskripsi <span class="text-destructive">*</span>
                            </Label>
                            <textarea
                                id="description"
                                v-model="form.description"
                                rows="4"
                                placeholder="Jelaskan detail kupon ini..."
                                :class="[
                                    'flex min-h-[80px] w-full rounded-xl border border-input bg-transparent px-3 py-2 text-base shadow-xs transition-[color,box-shadow] outline-none placeholder:text-muted-foreground focus-visible:border-ring focus-visible:ring-ring/50 focus-visible:ring-[3px] disabled:cursor-not-allowed disabled:opacity-50 md:text-sm',
                                    form.errors.description ? 'border-destructive' : '',
                                ]"
                            />
                            <p v-if="form.errors.description" class="text-sm text-destructive">
                                {{ form.errors.description }}
                            </p>
                        </div>

                        <div class="space-y-2">
                            <div class="flex items-center gap-2 mb-2">
                                <Checkbox
                                    id="never_expires"
                                    v-model:checked="neverExpires"
                                />
                                <Label
                                    for="never_expires"
                                    class="text-sm font-medium cursor-pointer"
                                >
                                    Tidak pernah kedaluwarsa
                                </Label>
                            </div>
                            <Label for="expires_at" class="text-sm font-medium">
                                Tanggal Kedaluwarsa (Opsional)
                            </Label>
                            <div class="relative">
                                <Calendar class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-muted-foreground pointer-events-none z-10" />
                                <input
                                    id="expires_at"
                                    v-model="form.expires_at"
                                    type="date"
                                    :min="today"
                                    :disabled="neverExpires"
                                    :readonly="neverExpires"
                                    :class="[
                                        'flex h-11 w-full rounded-xl border border-input bg-transparent pl-10 pr-3 py-2 text-base shadow-xs transition-[color,box-shadow] outline-none placeholder:text-muted-foreground focus-visible:border-ring focus-visible:ring-ring/50 focus-visible:ring-[3px] disabled:cursor-not-allowed disabled:opacity-50 md:h-10 md:text-sm',
                                        form.errors.expires_at ? 'border-destructive' : '',
                                    ]"
                                />
                            </div>
                            <p v-if="form.errors.expires_at" class="text-sm text-destructive">
                                {{ form.errors.expires_at }}
                            </p>
                            <p v-else class="text-xs text-muted-foreground mt-1">
                                Pilih tanggal kedaluwarsa kupon
                            </p>
                        </div>
                    </CardContent>
                </Card>

                <!-- Action Buttons -->
                <div class="flex flex-col gap-3 sm:flex-row sm:justify-end">
                    <Button
                        type="submit"
                        size="lg"
                        :disabled="form.processing"
                        class="h-12 w-full gap-2 rounded-xl shadow-lg active:scale-[0.98] transition-transform sm:w-auto sm:h-11"
                    >
                        <Plus class="h-4 w-4" />
                        <span v-if="form.processing">Membuat...</span>
                        <span v-else>Buat Kupon</span>
                    </Button>
                    <Button
                        type="button"
                        variant="outline"
                        size="lg"
                        class="h-11 w-full rounded-xl active:scale-[0.98] transition-transform sm:w-auto"
                        @click="$inertia.visit('/coupons')"
                    >
                        Batal
                    </Button>
                </div>
            </form>
        </div>
    </AppLayout>
</template>
