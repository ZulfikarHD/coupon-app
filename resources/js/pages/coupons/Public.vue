<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { Badge } from '@/components/ui/badge';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { QrCode, CheckCircle2, XCircle, Clock } from 'lucide-vue-next';
import { onMounted, ref } from 'vue';
import QRCode from 'qrcode';

interface Coupon {
    id: number;
    code: string;
    type: string;
    description: string;
    customer_name: string;
    status: 'active' | 'used' | 'expired';
    expires_at: string | null;
    created_at: string;
}

interface Props {
    coupon: Coupon;
}

const props = defineProps<Props>();

const qrCodeDataUrl = ref('');
const publicUrl = window.location.href;

const statusColors = {
    active: 'bg-green-500/10 text-green-700 dark:text-green-400 border-green-500/20',
    used: 'bg-gray-500/10 text-gray-700 dark:text-gray-400 border-gray-500/20',
    expired: 'bg-red-500/10 text-red-700 dark:text-red-400 border-red-500/20',
};

const statusLabels = {
    active: 'Aktif',
    used: 'Sudah Terpakai',
    expired: 'Kedaluwarsa',
};

onMounted(async () => {
    // Generate QR Code
    try {
        qrCodeDataUrl.value = await QRCode.toDataURL(publicUrl, {
            width: 400,
            margin: 2,
        });
    } catch (err) {
        console.error('Failed to generate QR code:', err);
    }
});
</script>

<template>
    <Head :title="`Kupon ${coupon.code}`" />

    <div class="min-h-screen bg-background">
        <div class="mx-auto max-w-md px-4 py-8 md:py-12">
            <!-- Store Branding Area (placeholder) -->
            <div class="mb-8 text-center">
                <h1 class="text-2xl font-bold tracking-tight md:text-3xl">
                    Kupon Saya
                </h1>
            </div>

            <!-- Coupon Card -->
            <Card class="border-2 shadow-lg">
                <CardHeader class="pb-4 text-center">
                    <div class="mb-4 flex justify-center">
                        <Badge :class="statusColors[coupon.status]" class="text-base px-4 py-2">
                            {{ statusLabels[coupon.status] }}
                        </Badge>
                    </div>
                    <CardTitle class="text-xl md:text-2xl">{{ coupon.type }}</CardTitle>
                    <CardDescription class="text-base md:text-lg">
                        {{ coupon.description }}
                    </CardDescription>
                </CardHeader>
                <CardContent class="space-y-6">
                    <!-- QR Code -->
                    <div class="flex flex-col items-center space-y-4">
                        <div
                            v-if="qrCodeDataUrl"
                            class="rounded-lg border-4 border-dashed p-6 bg-white dark:bg-gray-900"
                        >
                            <img
                                :src="qrCodeDataUrl"
                                alt="QR Code"
                                class="h-auto w-full max-w-[300px]"
                            />
                        </div>
                        <div v-else class="flex h-[300px] w-full items-center justify-center rounded-lg border-2 border-dashed">
                            <p class="text-sm text-muted-foreground">Memuat QR Code...</p>
                        </div>
                        
                        <!-- Barcode (Coupon Code) -->
                        <div class="w-full text-center">
                            <p class="font-mono text-2xl font-bold tracking-wider">{{ coupon.code }}</p>
                            <p class="mt-2 text-sm text-muted-foreground">
                                Kode Kupon
                            </p>
                        </div>
                    </div>

                    <!-- Customer Name -->
                    <div class="border-t pt-4">
                        <p class="text-center text-sm font-medium text-muted-foreground">
                            Untuk
                        </p>
                        <p class="mt-1 text-center text-lg font-semibold">
                            {{ coupon.customer_name }}
                        </p>
                    </div>

                    <!-- Expiration Date -->
                    <div v-if="coupon.expires_at" class="border-t pt-4">
                        <div class="flex items-center justify-center gap-2 text-sm text-muted-foreground">
                            <Clock class="h-4 w-4" />
                            <span>
                                Berlaku hingga {{ new Date(coupon.expires_at).toLocaleDateString('id-ID', {
                                    year: 'numeric',
                                    month: 'long',
                                    day: 'numeric',
                                }) }}
                            </span>
                        </div>
                    </div>

                    <!-- Status Info -->
                    <div v-if="coupon.status === 'used'" class="border-t pt-4">
                        <div class="flex items-center justify-center gap-2 text-sm text-muted-foreground">
                            <CheckCircle2 class="h-4 w-4" />
                            <span>
                                Digunakan pada {{ new Date(coupon.created_at).toLocaleDateString('id-ID', {
                                    year: 'numeric',
                                    month: 'long',
                                    day: 'numeric',
                                }) }}
                            </span>
                        </div>
                    </div>
                </CardContent>
            </Card>

            <!-- Footer Note -->
            <p class="mt-6 text-center text-xs text-muted-foreground">
                Tunjukkan QR Code ini kepada kasir untuk menggunakan kupon
            </p>
        </div>
    </div>
</template>
