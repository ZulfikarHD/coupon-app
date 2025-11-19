<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { type BreadcrumbItem } from '@/types';
import { ArrowLeft, Copy, QrCode, Printer, CheckCircle2 } from 'lucide-vue-next';
import { ref, computed, onMounted } from 'vue';
import QRCode from 'qrcode';

interface CouponValidation {
    id: number;
    action: 'used' | 'reversed';
    validated_at: string;
    notes: string | null;
    validator?: {
        name: string;
    };
}

interface Coupon {
    id: number;
    code: string;
    type: string;
    description: string;
    customer_name: string;
    customer_phone: string;
    customer_email: string | null;
    customer_social_media: string | null;
    status: 'active' | 'used' | 'expired';
    expires_at: string | null;
    created_at: string;
    user?: {
        name: string;
    };
    validations?: CouponValidation[];
}

interface Props {
    coupon: Coupon;
}

const props = defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Kupon',
        href: '/coupons',
    },
    {
        title: props.coupon.code,
        href: `/coupons/${props.coupon.id}`,
    },
];

const qrCodeDataUrl = ref<string>('');
const copied = ref(false);
const couponUrl = computed(() => {
    return `${window.location.origin}/coupon/${props.coupon.code}`;
});

onMounted(async () => {
    try {
        qrCodeDataUrl.value = await QRCode.toDataURL(couponUrl.value, {
            width: 300,
            margin: 2,
            color: {
                dark: '#000000',
                light: '#FFFFFF',
            },
        });
    } catch (error) {
        console.error('Error generating QR code:', error);
    }
});

const copyLink = async () => {
    try {
        await navigator.clipboard.writeText(couponUrl.value);
        copied.value = true;
        setTimeout(() => {
            copied.value = false;
        }, 2000);
    } catch (error) {
        console.error('Failed to copy:', error);
    }
};

const formatDate = (dateString: string) => {
    const date = new Date(dateString);
    return date.toLocaleDateString('id-ID', {
        day: '2-digit',
        month: 'long',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    });
};

const formatPhone = (phone: string) => {
    if (!phone) return '';
    let formatted = phone;
    if (phone.startsWith('62')) {
        formatted = '0' + phone.substring(2);
    }
    // Format as 0812-3456-7890
    if (formatted.length >= 10) {
        return `${formatted.substring(0, 4)}-${formatted.substring(4, 8)}-${formatted.substring(8)}`;
    }
    return formatted;
};

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

const printCoupon = () => {
    window.print();
};
</script>

<template>
    <Head :title="`Kupon ${coupon.code}`" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="container mx-auto px-4 py-6 sm:px-6 lg:px-8">
            <!-- Back Button - Mobile First -->
            <Button
                as-child
                variant="ghost"
                class="mb-6"
            >
                <Link href="/coupons">
                    <ArrowLeft class="mr-2 h-4 w-4" />
                    Kembali ke Daftar
                </Link>
            </Button>

            <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
                <!-- Main Content - Mobile First -->
                <div class="lg:col-span-2 space-y-6">
                    <!-- Coupon Info Card -->
                    <Card>
                        <CardHeader>
                            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                                <div>
                                    <CardTitle class="text-2xl sm:text-3xl font-mono">
                                        {{ coupon.code }}
                                    </CardTitle>
                                    <CardDescription class="mt-2 text-base">
                                        {{ coupon.type }}
                                    </CardDescription>
                                </div>
                                <Badge
                                    :variant="getStatusBadgeVariant(coupon.status)"
                                    class="w-fit text-sm px-3 py-1"
                                >
                                    {{ getStatusLabel(coupon.status) }}
                                </Badge>
                            </div>
                        </CardHeader>
                        <CardContent class="space-y-4">
                            <div>
                                <h3 class="text-sm font-medium text-muted-foreground mb-1">
                                    Deskripsi
                                </h3>
                                <p class="text-sm text-foreground whitespace-pre-line">
                                    {{ coupon.description }}
                                </p>
                            </div>

                            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                                <div>
                                    <h3 class="text-sm font-medium text-muted-foreground mb-1">
                                        Dibuat
                                    </h3>
                                    <p class="text-sm text-foreground">
                                        {{ formatDate(coupon.created_at) }}
                                    </p>
                                    <p class="text-xs text-muted-foreground mt-1">
                                        oleh {{ coupon.user?.name || 'Staff' }}
                                    </p>
                                </div>

                                <div v-if="coupon.expires_at">
                                    <h3 class="text-sm font-medium text-muted-foreground mb-1">
                                        Kedaluwarsa
                                    </h3>
                                    <p class="text-sm text-foreground">
                                        {{ formatDate(coupon.expires_at) }}
                                    </p>
                                </div>
                            </div>
                        </CardContent>
                    </Card>

                    <!-- Customer Info Card -->
                    <Card>
                        <CardHeader>
                            <CardTitle class="text-lg sm:text-xl">Informasi Pelanggan</CardTitle>
                        </CardHeader>
                        <CardContent class="space-y-3">
                            <div>
                                <h3 class="text-sm font-medium text-muted-foreground mb-1">
                                    Nama
                                </h3>
                                <p class="text-sm text-foreground">{{ coupon.customer_name }}</p>
                            </div>

                            <div>
                                <h3 class="text-sm font-medium text-muted-foreground mb-1">
                                    Telepon
                                </h3>
                                <p class="text-sm text-foreground">
                                    {{ formatPhone(coupon.customer_phone) }}
                                </p>
                            </div>

                            <div v-if="coupon.customer_email">
                                <h3 class="text-sm font-medium text-muted-foreground mb-1">
                                    Email
                                </h3>
                                <p class="text-sm text-foreground">{{ coupon.customer_email }}</p>
                            </div>

                            <div v-if="coupon.customer_social_media">
                                <h3 class="text-sm font-medium text-muted-foreground mb-1">
                                    Media Sosial
                                </h3>
                                <p class="text-sm text-foreground">
                                    {{ coupon.customer_social_media }}
                                </p>
                            </div>
                        </CardContent>
                    </Card>

                    <!-- Validation History -->
                    <Card v-if="coupon.validations && coupon.validations.length > 0">
                        <CardHeader>
                            <CardTitle class="text-lg sm:text-xl">Riwayat Validasi</CardTitle>
                        </CardHeader>
                        <CardContent>
                            <div class="space-y-4">
                                <div
                                    v-for="validation in coupon.validations"
                                    :key="validation.id"
                                    class="border-l-2 border-muted pl-4 py-2"
                                >
                                    <div class="flex items-center gap-2 mb-1">
                                        <Badge
                                            :variant="validation.action === 'used' ? 'default' : 'destructive'"
                                        >
                                            {{ validation.action === 'used' ? 'Digunakan' : 'Dibatalkan' }}
                                        </Badge>
                                        <span class="text-xs text-muted-foreground">
                                            {{ formatDate(validation.validated_at) }}
                                        </span>
                                    </div>
                                    <p class="text-sm text-foreground">
                                        oleh {{ validation.validator?.name || 'Staff' }}
                                    </p>
                                    <p v-if="validation.notes" class="text-xs text-muted-foreground mt-1">
                                        Catatan: {{ validation.notes }}
                                    </p>
                                </div>
                            </div>
                        </CardContent>
                    </Card>
                </div>

                <!-- Sidebar - Mobile First -->
                <div class="space-y-6">
                    <!-- QR Code Card -->
                    <Card>
                        <CardHeader>
                            <CardTitle class="text-lg sm:text-xl flex items-center gap-2">
                                <QrCode class="h-5 w-5" />
                                QR Code
                            </CardTitle>
                        </CardHeader>
                        <CardContent class="space-y-4">
                            <div class="flex justify-center bg-white p-4 rounded-lg dark:bg-muted">
                                <img
                                    v-if="qrCodeDataUrl"
                                    :src="qrCodeDataUrl"
                                    alt="QR Code"
                                    class="w-full max-w-[250px]"
                                />
                                <div v-else class="w-full max-w-[250px] aspect-square bg-muted animate-pulse" />
                            </div>

                            <!-- Barcode (Text representation) -->
                            <div class="text-center">
                                <p class="text-xs text-muted-foreground mb-2">Barcode</p>
                                <p class="font-mono text-lg tracking-wider text-foreground">
                                    {{ coupon.code }}
                                </p>
                            </div>

                            <!-- Actions -->
                            <div class="space-y-2">
                                <Button
                                    class="w-full"
                                    @click="copyLink"
                                >
                                    <Copy v-if="!copied" class="mr-2 h-4 w-4" />
                                    <CheckCircle2 v-else class="mr-2 h-4 w-4" />
                                    {{ copied ? 'Tersalin!' : 'Salin Link' }}
                                </Button>

                                <Button
                                    variant="outline"
                                    class="w-full"
                                    @click="printCoupon"
                                >
                                    <Printer class="mr-2 h-4 w-4" />
                                    Cetak
                                </Button>
                            </div>
                        </CardContent>
                    </Card>

                    <!-- Action Buttons -->
                    <Card v-if="coupon.status === 'active'">
                        <CardHeader>
                            <CardTitle class="text-lg sm:text-xl">Aksi</CardTitle>
                        </CardHeader>
                        <CardContent class="space-y-2">
                            <Button
                                variant="default"
                                class="w-full"
                                @click="router.visit(`/coupons/${coupon.id}/validate`)"
                            >
                                Tandai sebagai Terpakai
                            </Button>
                        </CardContent>
                    </Card>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<style scoped>
@media print {
    .no-print {
        display: none;
    }
}
</style>
