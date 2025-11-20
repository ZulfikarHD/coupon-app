<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { Badge } from '@/components/ui/badge';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { QrCode, CheckCircle2, XCircle, Clock, Sparkles, Gift, Star } from 'lucide-vue-next';
import { onMounted, ref, computed } from 'vue';
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
    validations?: Array<{
        id: number;
        validated_at: string;
        action: string;
    }>;
}

interface Props {
    coupon: Coupon;
}

const props = defineProps<Props>();

const qrCodeDataUrl = ref('');
const publicUrl = window.location.href;

// Get validated_at from the first 'used' validation
const validatedAt = computed(() => {
    if (props.coupon.status === 'used' && props.coupon.validations && props.coupon.validations.length > 0) {
        const usedValidation = props.coupon.validations.find(v => v.action === 'used');
        return usedValidation?.validated_at || null;
    }
    return null;
});

// Open Graph meta tags
const ogTitle = computed(() => `Kupon ${props.coupon.code} - ${props.coupon.type}`);
const ogDescription = computed(() => props.coupon.description);
const ogImage = computed(() => {
    // Use QR code as OG image if available, otherwise use a default
    return qrCodeDataUrl.value || `${publicUrl.split('/coupon/')[0]}/favicon.svg`;
});

const statusColors = {
    active: 'bg-gradient-to-r from-green-500/20 to-emerald-500/20 text-green-700 dark:text-green-300 border-green-400/30 shadow-green-500/10',
    used: 'bg-gradient-to-r from-gray-500/20 to-slate-500/20 text-gray-700 dark:text-gray-300 border-gray-400/30',
    expired: 'bg-gradient-to-r from-red-500/20 to-rose-500/20 text-red-700 dark:text-red-300 border-red-400/30',
};

const statusLabels = {
    active: 'Aktif',
    used: 'Sudah Terpakai',
    expired: 'Kedaluwarsa',
};

const statusIcons = {
    active: Sparkles,
    used: CheckCircle2,
    expired: XCircle,
};

onMounted(async () => {
    // Generate QR Code
    try {
        qrCodeDataUrl.value = await QRCode.toDataURL(publicUrl, {
            width: 400,
            margin: 2,
            color: {
                dark: '#000000',
                light: '#FFFFFF',
            },
        });
    } catch (err) {
        console.error('Failed to generate QR code:', err);
    }
});
</script>

<template>
    <Head>
        <title>{{ `Kupon ${coupon.code}` }}</title>
        <!-- Open Graph / Facebook / WhatsApp -->
        <meta property="og:type" content="website" />
        <meta property="og:url" :content="publicUrl" />
        <meta property="og:title" :content="ogTitle" />
        <meta property="og:description" :content="ogDescription" />
        <meta property="og:image" :content="ogImage" />
        <meta property="og:site_name" content="Coupon System" />

        <!-- Twitter Card -->
        <meta name="twitter:card" content="summary_large_image" />
        <meta name="twitter:url" :content="publicUrl" />
        <meta name="twitter:title" :content="ogTitle" />
        <meta name="twitter:description" :content="ogDescription" />
        <meta name="twitter:image" :content="ogImage" />
    </Head>

    <div class="min-h-screen bg-gradient-to-br from-primary/5 via-background to-accent/5">
        <!-- Decorative background elements -->
        <div class="fixed inset-0 overflow-hidden pointer-events-none">
            <div class="absolute -top-40 -right-40 w-80 h-80 bg-primary/10 rounded-full blur-3xl"></div>
            <div class="absolute -bottom-40 -left-40 w-80 h-80 bg-accent/10 rounded-full blur-3xl"></div>
        </div>

        <div class="relative mx-auto max-w-md px-4 py-8 md:py-12">
            <!-- Header with icon -->
            <div class="mb-8 text-center">
                <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-gradient-to-br from-primary to-primary/60 mb-4 shadow-lg">
                    <Gift class="h-8 w-8 text-primary-foreground" />
                </div>
                <h1 class="text-3xl font-bold tracking-tight bg-gradient-to-r from-foreground to-foreground/70 bg-clip-text text-transparent md:text-4xl">
                    Kupon Saya
                </h1>
                <p class="mt-2 text-sm text-muted-foreground">
                    Tunjukkan QR Code kepada kasir
                </p>
            </div>

            <!-- Coupon Card with gradient border effect -->
            <Card class="border-2 shadow-2xl relative overflow-hidden bg-card/95 backdrop-blur-sm">
                <!-- Decorative corner elements -->
                <div class="absolute top-0 left-0 w-20 h-20 bg-gradient-to-br from-primary/20 to-transparent rounded-br-full"></div>
                <div class="absolute bottom-0 right-0 w-20 h-20 bg-gradient-to-tl from-accent/20 to-transparent rounded-tl-full"></div>

                <CardHeader class="pb-6 text-center relative z-10">
                    <!-- Status Badge with icon -->
                    <div class="mb-6 flex justify-center">
                        <Badge :class="statusColors[coupon.status]" class="text-sm px-5 py-2.5 shadow-md border-2 flex items-center gap-2">
                            <component :is="statusIcons[coupon.status]" class="h-4 w-4" />
                            {{ statusLabels[coupon.status] }}
                        </Badge>
                    </div>

                    <!-- Coupon Type with star decoration -->
                    <div class="flex items-center justify-center gap-2 mb-2">
                        <Star class="h-5 w-5 text-primary fill-primary/20" />
                        <CardTitle class="text-2xl md:text-3xl font-bold bg-gradient-to-r from-primary to-accent bg-clip-text text-transparent">
                            {{ coupon.type }}
                        </CardTitle>
                        <Star class="h-5 w-5 text-primary fill-primary/20" />
                    </div>
                    <CardDescription class="text-base md:text-lg text-muted-foreground mt-2">
                        {{ coupon.description }}
                    </CardDescription>
                </CardHeader>

                <CardContent class="space-y-8 relative z-10">
                    <!-- QR Code Section with animated border -->
                    <div class="flex flex-col items-center space-y-6">
                        <div class="relative">
                            <!-- Animated ring around QR code -->
                            <div
                                v-if="coupon.status === 'active'"
                                class="absolute inset-0 rounded-2xl bg-gradient-to-r from-primary via-accent to-primary animate-pulse opacity-20 blur-xl"
                            ></div>

                            <div
                                v-if="qrCodeDataUrl"
                                class="relative rounded-2xl border-4 border-dashed border-primary/30 p-6 bg-white dark:bg-gray-900 shadow-xl transition-all hover:scale-105 hover:shadow-2xl"
                            >
                                <div class="absolute -top-2 -right-2 w-6 h-6 bg-primary rounded-full animate-ping"></div>
                                <img
                                    :src="qrCodeDataUrl"
                                    alt="QR Code"
                                    class="h-auto w-full max-w-[280px] md:max-w-[320px] relative z-10"
                                />
                            </div>
                            <div v-else class="flex h-[320px] w-full max-w-[320px] items-center justify-center rounded-2xl border-2 border-dashed border-muted bg-muted/50">
                                <div class="text-center">
                                    <QrCode class="h-12 w-12 mx-auto mb-2 text-muted-foreground animate-pulse" />
                                    <p class="text-sm text-muted-foreground">Memuat QR Code...</p>
                                </div>
                            </div>
                        </div>

                        <!-- Coupon Code with decorative styling -->
                        <div class="w-full text-center space-y-2">
                            <p class="text-xs font-medium text-muted-foreground uppercase tracking-wider">
                                Kode Kupon
                            </p>
                            <div class="inline-flex items-center gap-2 px-6 py-3 bg-gradient-to-r from-primary/10 via-accent/10 to-primary/10 rounded-xl border-2 border-primary/20">
                                <QrCode class="h-4 w-4 text-primary" />
                                <p class="font-mono text-2xl md:text-3xl font-bold tracking-wider text-foreground">
                                    {{ coupon.code }}
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Customer Name Section -->
                    <div class="border-t border-border/50 pt-6">
                        <div class="text-center space-y-2">
                            <p class="text-xs font-medium text-muted-foreground uppercase tracking-wider">
                                Untuk
                            </p>
                            <div class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-muted/50">
                                <p class="text-xl md:text-2xl font-bold text-foreground">
                                    {{ coupon.customer_name }}
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Expiration Date -->
                    <div v-if="coupon.expires_at" class="border-t border-border/50 pt-6">
                        <div class="flex items-center justify-center gap-3 px-4 py-3 rounded-lg bg-muted/30">
                            <div class="flex items-center justify-center w-10 h-10 rounded-full bg-primary/10">
                                <Clock class="h-5 w-5 text-primary" />
                            </div>
                            <div class="text-left">
                                <p class="text-xs font-medium text-muted-foreground">Berlaku hingga</p>
                                <p class="text-sm font-semibold text-foreground">
                                    {{ new Date(coupon.expires_at).toLocaleDateString('id-ID', {
                                        year: 'numeric',
                                        month: 'long',
                                        day: 'numeric',
                                    }) }}
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Status Info for used coupons -->
                    <div v-if="coupon.status === 'used' && validatedAt" class="border-t border-border/50 pt-6">
                        <div class="flex items-center justify-center gap-3 px-4 py-3 rounded-lg bg-muted/30">
                            <div class="flex items-center justify-center w-10 h-10 rounded-full bg-gray-500/10">
                                <CheckCircle2 class="h-5 w-5 text-gray-600 dark:text-gray-400" />
                            </div>
                            <div class="text-left">
                                <p class="text-xs font-medium text-muted-foreground">Digunakan pada</p>
                                <p class="text-sm font-semibold text-foreground">
                                    {{ new Date(validatedAt).toLocaleDateString('id-ID', {
                                        year: 'numeric',
                                        month: 'long',
                                        day: 'numeric',
                                        hour: '2-digit',
                                        minute: '2-digit',
                                    }) }}
                                </p>
                            </div>
                        </div>
                    </div>
                </CardContent>
            </Card>

            <!-- Footer Note with better styling -->
            <div class="mt-8 text-center space-y-2">
                <div class="inline-flex items-center gap-2 px-4 py-2 rounded-full bg-primary/10 border border-primary/20">
                    <QrCode class="h-4 w-4 text-primary" />
                    <p class="text-xs font-medium text-foreground">
                        Tunjukkan QR Code ini kepada kasir
                    </p>
                </div>
                <p class="text-xs text-muted-foreground">
                    Kupon ini hanya dapat digunakan sekali
                </p>
            </div>
        </div>
    </div>
</template>
