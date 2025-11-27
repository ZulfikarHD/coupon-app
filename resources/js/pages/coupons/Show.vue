<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import PageHeader from '@/components/PageHeader.vue';
import StatusBadge from '@/components/StatusBadge.vue';
import { useStatusColors } from '@/composables/useStatusColors';
import { Head, router, useForm, usePage } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import Input from '@/components/ui/input/Input.vue';
import Label from '@/components/ui/label/Label.vue';
import Textarea from '@/components/ui/textarea.vue';
import Dialog from '@/components/ui/dialog/Dialog.vue';
import DialogContent from '@/components/ui/dialog/DialogContent.vue';
import DialogDescription from '@/components/ui/dialog/DialogDescription.vue';
import DialogFooter from '@/components/ui/dialog/DialogFooter.vue';
import DialogHeader from '@/components/ui/dialog/DialogHeader.vue';
import DialogTitle from '@/components/ui/dialog/DialogTitle.vue';
import Alert from '@/components/ui/alert/Alert.vue';
import AlertDescription from '@/components/ui/alert/AlertDescription.vue';
import {
    Copy,
    Trash2,
    ArrowLeft,
    QrCode,
    User,
    Phone,
    Mail,
    Link as LinkIcon,
    FileText,
    CheckCircle2,
    XCircle,
    RotateCcw,
    AlertTriangle
} from 'lucide-vue-next';
import { onMounted, ref, computed, watch } from 'vue';
import QRCode from 'qrcode';
import { useSweetAlert } from '@/composables/useSweetAlert';
import { useHaptic } from '@/composables/useHaptic';

interface Coupon {
    id: number;
    code: string;
    type: string;
    description: string;
    customer_name: string;
    first_name?: string;
    last_name?: string;
    customer_phone: string;
    customer_email: string | null;
    customer_social_media: string | null;
    expires_at: string | null;
    status: 'active' | 'used' | 'expired';
    created_at: string;
    user?: {
        name: string;
    };
    validations?: Array<{
        id: number;
        action: 'used' | 'reversed';
        validated_at: string;
        notes: string | null;
        validator?: {
            name: string;
        };
    }>;
    formatted_phone?: string;
}

interface Props {
    coupon: Coupon;
    qrUrl: string;
    publicUrl: string;
}

const props = defineProps<Props>();

const swal = useSweetAlert();
const { trigger } = useHaptic();
const isLoaded = ref(false);
const qrCodeDataUrl = ref('');
const isCopying = ref(false);
const showReversalModal = ref(false);

// Flash messages from backend
const page = usePage();
const flashSuccess = computed(() => page.props.flash?.success);
const flashError = computed(() => page.props.flash?.error);

// Reversal form
const reversalForm = useForm({
    password: '',
    reason: '',
});

const { statusLabels } = useStatusColors();

const actionLabels = {
    used: 'Digunakan',
    reversed: 'Dibatalkan',
};

const copyToClipboard = async (event: Event) => {
    event.preventDefault();
    event.stopPropagation();

    isCopying.value = true;

    // Build the URL to copy
    const urlToCopy = props.publicUrl || `${window.location.origin}/coupon/${props.coupon.code}`;

    try {
        // Try modern clipboard API first (works in HTTPS or localhost)
        if (navigator.clipboard && navigator.clipboard.writeText) {
            await navigator.clipboard.writeText(urlToCopy);
            isCopying.value = false;
            swal.toast('Link berhasil disalin ke clipboard!', 'success');
            return;
        }

        // Fallback for older browsers or non-secure contexts
        const textArea = document.createElement('textarea');
        textArea.value = urlToCopy;
        textArea.style.position = 'fixed';
        textArea.style.left = '-999999px';
        textArea.style.top = '-999999px';
        textArea.style.opacity = '0';
        textArea.setAttribute('readonly', '');
        document.body.appendChild(textArea);
        textArea.focus();
        textArea.select();
        textArea.setSelectionRange(0, urlToCopy.length);

        try {
            const successful = document.execCommand('copy');
            document.body.removeChild(textArea);
            isCopying.value = false;
            if (successful) {
                swal.toast('Link berhasil disalin ke clipboard!', 'success');
            } else {
                throw new Error('Copy command failed');
            }
        } catch (err) {
            document.body.removeChild(textArea);
            isCopying.value = false;
            // Last resort: show the URL in a prompt for manual copy
            const result = await swal.prompt(
                'Salin link ini (Ctrl+C atau Cmd+C):',
                'Salin Link',
                'Masukkan kode atau URL',
                urlToCopy,
                'OK',
                'Batal'
            );
            if (result.isConfirmed) {
                swal.toast('Link siap untuk disalin!', 'info');
            }
        }
    } catch (err) {
        console.error('Failed to copy:', err);
        isCopying.value = false;
        // Final fallback: show prompt
        const result = await swal.prompt(
            'Salin link ini (Ctrl+C atau Cmd+C):',
            'Salin Link',
            'Masukkan kode atau URL',
            urlToCopy,
            'OK',
            'Batal'
        );
        if (result.isConfirmed) {
            swal.toast('Link siap untuk disalin!', 'info');
        }
    }
};

const deleteCoupon = async () => {
    const result = await swal.confirm(
        'Apakah Anda yakin ingin menghapus kupon ini?',
        'Hapus Kupon',
        'Ya, Hapus',
        'Batal',
        '#ef4444'
    );

    if (result.isConfirmed) {
        router.delete(`/coupons/${props.coupon.id}`);
    }
};

const openReversalModal = () => {
    showReversalModal.value = true;
};

const closeReversalModal = () => {
    showReversalModal.value = false;
    reversalForm.reset();
    reversalForm.clearErrors();
};

const submitReversal = () => {
    reversalForm.post(`/coupons/${props.coupon.id}/reverse`, {
        preserveScroll: true,
        onSuccess: () => {
            closeReversalModal();
        },
    });
};

// Watch for flash messages and show SweetAlert2 toasts
watch(flashSuccess, (message) => {
    if (message) {
        swal.toast(message, 'success');
    }
});

watch(flashError, (message) => {
    if (message) {
        swal.toast(message, 'error');
    }
});

onMounted(async () => {
    // Generate QR Code
    try {
        qrCodeDataUrl.value = await QRCode.toDataURL(props.publicUrl, {
            width: 300,
            margin: 2,
        });
    } catch (err) {
        console.error('Failed to generate QR code:', err);
    }
    
    // iOS spring animation on load
    requestAnimationFrame(() => {
        isLoaded.value = true;
    });
});

const breadcrumbs = [
    {
        title: 'Kupon',
        href: '/coupons',
    },
    {
        title: props.coupon.code,
        href: `/coupons/${props.coupon.id}`,
    },
];
</script>

<template>
    <Head :title="`Kupon ${coupon.code}`" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 sm:gap-6 overflow-x-auto p-4 md:p-6">
            <!-- Header with spring animation -->
            <div 
                :class="[
                    'flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between',
                    'transition-all duration-500',
                    isLoaded ? 'translate-y-0 opacity-100' : 'translate-y-4 opacity-0',
                ]"
            >
                <div class="flex items-center gap-4">
                    <Button
                        variant="ghost"
                        size="icon"
                        class="h-10 w-10 rounded-xl press-effect"
                        @click="trigger('light'); $inertia.visit('/coupons')"
                    >
                        <ArrowLeft class="h-5 w-5" />
                    </Button>
                    <PageHeader
                        :title="coupon.code"
                        description="Detail kupon dan informasi pelanggan"
                    />
                </div>
                <div class="flex flex-wrap gap-2">
                    <Button
                        variant="outline"
                        size="lg"
                        class="h-11 flex-1 gap-2 rounded-xl min-w-[120px] press-effect sm:flex-initial"
                        :disabled="isCopying"
                        @click="trigger('medium'); copyToClipboard($event)"
                    >
                        <Copy class="h-4 w-4" />
                        <span class="text-sm">{{ isCopying ? 'Menyalin...' : 'Salin Link' }}</span>
                    </Button>
                    <Button
                        v-if="coupon.status === 'used'"
                        variant="outline"
                        size="lg"
                        class="h-11 flex-1 gap-2 rounded-xl border-orange-500 text-orange-600 hover:bg-orange-500/10 hover:text-orange-700 dark:text-orange-400 dark:hover:text-orange-300 min-w-[120px] press-effect sm:flex-initial"
                        @click="trigger('medium'); openReversalModal()"
                    >
                        <RotateCcw class="h-4 w-4" />
                        <span class="text-sm">Batalkan</span>
                    </Button>
                    <Button
                        variant="destructive"
                        size="lg"
                        class="h-11 flex-1 gap-2 rounded-xl min-w-[120px] press-effect sm:flex-initial"
                        @click="trigger('heavy'); deleteCoupon()"
                    >
                        <Trash2 class="h-4 w-4" />
                        <span class="text-sm">Hapus</span>
                    </Button>
                </div>
            </div>

            <!-- Flash Messages -->
            <Alert v-if="flashSuccess" class="border-green-500 bg-green-500/10 text-green-700 dark:text-green-400">
                <CheckCircle2 class="h-4 w-4" />
                <AlertDescription>{{ flashSuccess }}</AlertDescription>
            </Alert>
            <Alert v-if="flashError" variant="destructive">
                <AlertTriangle class="h-4 w-4" />
                <AlertDescription>{{ flashError }}</AlertDescription>
            </Alert>

            <div class="grid gap-4 sm:gap-6 lg:grid-cols-3">
                <!-- QR Code - Show first on mobile, sidebar on desktop -->
                <div 
                    :class="[
                        'space-y-6 order-first lg:order-last',
                        'transition-all duration-500 delay-100',
                        isLoaded ? 'translate-y-0 opacity-100' : 'translate-y-4 opacity-0',
                    ]"
                >
                    <Card class="border rounded-xl sticky top-4 card-hover">
                        <CardHeader class="pb-4">
                            <div class="flex items-center gap-2">
                                <QrCode class="h-5 w-5 text-primary" />
                                <CardTitle class="text-base sm:text-lg font-semibold">QR Code</CardTitle>
                            </div>
                            <CardDescription class="text-sm mt-1">
                                Scan untuk melihat kupon
                            </CardDescription>
                        </CardHeader>
                        <CardContent class="flex flex-col items-center space-y-4">
                            <div
                                v-if="qrCodeDataUrl"
                                class="rounded-xl border-2 border-dashed p-4 sm:p-6 bg-white dark:bg-gray-900"
                            >
                                <img
                                    :src="qrCodeDataUrl"
                                    alt="QR Code"
                                    class="h-auto w-full max-w-[240px] sm:max-w-[280px] md:max-w-[320px]"
                                />
                            </div>
                            <div v-else class="flex h-[250px] w-full items-center justify-center rounded-xl border-2 border-dashed">
                                <p class="text-sm text-muted-foreground">Memuat QR Code...</p>
                            </div>
                            <div class="w-full text-center">
                                <p class="font-mono text-sm font-bold">{{ coupon.code }}</p>
                                <p class="mt-1 text-xs text-muted-foreground">
                                    Kode Kupon
                                </p>
                            </div>
                        </CardContent>
                    </Card>
                </div>

                <!-- Main Content -->
                <div class="space-y-4 sm:space-y-6 lg:col-span-2 order-last lg:order-first">
                    <!-- Coupon Info Card -->
                    <Card 
                        :class="[
                            'border rounded-xl',
                            'transition-all duration-500 delay-150',
                            isLoaded ? 'translate-y-0 opacity-100' : 'translate-y-4 opacity-0',
                        ]"
                    >
                        <CardHeader class="pb-4">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-2">
                                    <FileText class="h-5 w-5 text-primary" />
                                    <CardTitle class="text-lg font-semibold">Informasi Kupon</CardTitle>
                                </div>
                                <StatusBadge :status="coupon.status" />
                            </div>
                        </CardHeader>
                        <CardContent class="space-y-4">
                            <div>
                                <p class="text-sm font-medium text-muted-foreground">Kode Kupon</p>
                                <p class="mt-1 font-mono text-2xl font-bold">{{ coupon.code }}</p>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-muted-foreground">Jenis</p>
                                <p class="mt-1 text-base font-medium">{{ coupon.type }}</p>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-muted-foreground">Deskripsi</p>
                                <p class="mt-1 text-base text-foreground">{{ coupon.description }}</p>
                            </div>
                            <div class="grid gap-4 sm:grid-cols-2">
                                <div>
                                    <p class="text-sm font-medium text-muted-foreground">Dibuat</p>
                                    <p class="mt-1 text-sm">
                                        {{ new Date(coupon.created_at).toLocaleDateString('id-ID', {
                                            year: 'numeric',
                                            month: 'long',
                                            day: 'numeric',
                                            hour: '2-digit',
                                            minute: '2-digit',
                                        }) }}
                                    </p>
                                </div>
                                <div v-if="coupon.expires_at">
                                    <p class="text-sm font-medium text-muted-foreground">Kedaluwarsa</p>
                                    <p class="mt-1 text-sm">
                                        {{ new Date(coupon.expires_at).toLocaleDateString('id-ID', {
                                            year: 'numeric',
                                            month: 'long',
                                            day: 'numeric',
                                        }) }}
                                    </p>
                                </div>
                            </div>
                            <div v-if="coupon.user">
                                <p class="text-sm font-medium text-muted-foreground">Dibuat Oleh</p>
                                <p class="mt-1 text-sm">{{ coupon.user.name }}</p>
                            </div>
                        </CardContent>
                    </Card>

                    <!-- Customer Info Card -->
                    <Card 
                        :class="[
                            'border rounded-xl',
                            'transition-all duration-500 delay-200',
                            isLoaded ? 'translate-y-0 opacity-100' : 'translate-y-4 opacity-0',
                        ]"
                    >
                        <CardHeader class="pb-4">
                            <div class="flex items-center gap-2">
                                <User class="h-5 w-5 text-primary" />
                                <CardTitle class="text-base sm:text-lg font-semibold">Informasi Pelanggan</CardTitle>
                            </div>
                        </CardHeader>
                        <CardContent class="space-y-4">
                            <div class="space-y-2">
                                <div class="flex items-center gap-2 text-sm font-medium text-muted-foreground">
                                    <User class="h-4 w-4 flex-shrink-0" />
                                    <span>Nama Lengkap</span>
                                </div>
                                <p class="mt-1 text-base font-medium pl-6">{{ coupon.customer_name }}</p>
                            </div>
                            <div v-if="coupon.first_name || coupon.last_name" class="space-y-2">
                                <div class="flex items-center gap-2 text-sm font-medium text-muted-foreground">
                                    <User class="h-4 w-4 flex-shrink-0" />
                                    <span>Nama Depan & Belakang</span>
                                </div>
                                <p class="mt-1 text-base pl-6">
                                    <span v-if="coupon.first_name">{{ coupon.first_name }}</span>
                                    <span v-if="coupon.first_name && coupon.last_name"> </span>
                                    <span v-if="coupon.last_name">{{ coupon.last_name }}</span>
                                </p>
                            </div>
                            <div class="space-y-2">
                                <div class="flex items-center gap-2 text-sm font-medium text-muted-foreground">
                                    <Phone class="h-4 w-4 flex-shrink-0" />
                                    <span>Telepon</span>
                                </div>
                                <p class="mt-1 text-base pl-6">{{ coupon.formatted_phone || coupon.customer_phone }}</p>
                            </div>
                            <div v-if="coupon.customer_email" class="space-y-2">
                                <div class="flex items-center gap-2 text-sm font-medium text-muted-foreground">
                                    <Mail class="h-4 w-4 flex-shrink-0" />
                                    <span>Email</span>
                                </div>
                                <p class="mt-1 text-base pl-6">{{ coupon.customer_email }}</p>
                            </div>
                            <div v-if="coupon.customer_social_media" class="space-y-2">
                                <div class="flex items-center gap-2 text-sm font-medium text-muted-foreground">
                                    <LinkIcon class="h-4 w-4 flex-shrink-0" />
                                    <span>Media Sosial</span>
                                </div>
                                <p class="mt-1 text-base pl-6">{{ coupon.customer_social_media }}</p>
                            </div>
                        </CardContent>
                    </Card>

                    <!-- Validation History -->
                    <Card 
                        v-if="coupon.validations && coupon.validations.length > 0" 
                        :class="[
                            'border rounded-xl',
                            'transition-all duration-500 delay-250',
                            isLoaded ? 'translate-y-0 opacity-100' : 'translate-y-4 opacity-0',
                        ]"
                    >
                        <CardHeader class="pb-4">
                            <CardTitle class="text-lg font-semibold">Riwayat Validasi</CardTitle>
                            <CardDescription class="text-sm mt-1">
                                Catatan penggunaan dan pembatalan kupon
                            </CardDescription>
                        </CardHeader>
                        <CardContent>
                            <div class="space-y-4">
                                <div
                                    v-for="(validation, index) in coupon.validations"
                                    :key="validation.id"
                                    :class="[
                                        'flex items-start gap-3 sm:gap-4 rounded-xl border p-3 sm:p-4',
                                        'transition-all duration-300 mobile-card-press',
                                        isLoaded ? 'animate-spring-up' : 'opacity-0',
                                    ]"
                                    :style="{ animationDelay: `${300 + index * 50}ms` }"
                                >
                                    <div
                                        :class="[
                                            'flex h-10 w-10 shrink-0 items-center justify-center rounded-full',
                                            validation.action === 'used'
                                                ? 'bg-green-500/10 text-green-600 dark:text-green-400'
                                                : 'bg-orange-500/10 text-orange-600 dark:text-orange-400',
                                        ]"
                                    >
                                        <CheckCircle2
                                            v-if="validation.action === 'used'"
                                            class="h-5 w-5"
                                        />
                                        <XCircle
                                            v-else
                                            class="h-5 w-5"
                                        />
                                    </div>
                                    <div class="flex-1 space-y-1">
                                        <div class="flex items-center gap-2">
                                            <p class="font-medium">
                                                {{ actionLabels[validation.action] }}
                                            </p>
                                            <Badge
                                                :class="
                                                    validation.action === 'used'
                                                        ? 'bg-green-500/10 text-green-700 dark:text-green-400'
                                                        : 'bg-orange-500/10 text-orange-700 dark:text-orange-400'
                                                "
                                            >
                                                {{ validation.action === 'used' ? 'Digunakan' : 'Dibatalkan' }}
                                            </Badge>
                                        </div>
                                        <p class="text-sm text-muted-foreground">
                                            Oleh: {{ validation.validator?.name || 'Tidak diketahui' }}
                                        </p>
                                        <p class="text-sm text-muted-foreground">
                                            {{ new Date(validation.validated_at).toLocaleDateString('id-ID', {
                                                year: 'numeric',
                                                month: 'long',
                                                day: 'numeric',
                                                hour: '2-digit',
                                                minute: '2-digit',
                                            }) }}
                                        </p>
                                        <p v-if="validation.notes" class="mt-2 text-sm">
                                            <span class="font-medium">Catatan:</span> {{ validation.notes }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </CardContent>
                    </Card>
                </div>
            </div>
        </div>

        <!-- Reversal Modal -->
        <Dialog :open="showReversalModal" @update:open="closeReversalModal">
            <DialogContent class="sm:max-w-md">
                <DialogHeader>
                    <DialogTitle class="flex items-center gap-2 text-orange-600 dark:text-orange-400">
                        <AlertTriangle class="h-5 w-5" />
                        Batalkan Penggunaan Kupon
                    </DialogTitle>
                    <DialogDescription>
                        Anda yakin ingin membatalkan penggunaan kupon ini? Kupon akan kembali menjadi aktif dan dapat digunakan lagi.
                    </DialogDescription>
                </DialogHeader>

                <form @submit.prevent="submitReversal" class="space-y-4 py-4">
                    <!-- Coupon Info Display -->
                    <div class="rounded-lg border bg-muted/50 p-4 space-y-2">
                        <div>
                            <p class="text-xs font-medium text-muted-foreground">Kode Kupon</p>
                            <p class="mt-1 font-mono font-semibold">{{ coupon.code }}</p>
                        </div>
                        <div>
                            <p class="text-xs font-medium text-muted-foreground">Pelanggan</p>
                            <p class="mt-1">{{ coupon.customer_name }}</p>
                        </div>
                    </div>

                    <!-- Password Input -->
                    <div class="space-y-2">
                        <Label for="reversal-password">
                            Password Anda <span class="text-destructive">*</span>
                        </Label>
                        <Input
                            id="reversal-password"
                            v-model="reversalForm.password"
                            type="password"
                            placeholder="Masukkan password untuk konfirmasi"
                            :disabled="reversalForm.processing"
                            :class="{ 'border-destructive': reversalForm.errors.password }"
                        />
                        <p v-if="reversalForm.errors.password" class="text-sm text-destructive">
                            {{ reversalForm.errors.password }}
                        </p>
                    </div>

                    <!-- Reason Textarea -->
                    <div class="space-y-2">
                        <Label for="reversal-reason">
                            Alasan Pembatalan <span class="text-destructive">*</span>
                        </Label>
                        <Textarea
                            id="reversal-reason"
                            v-model="reversalForm.reason"
                            placeholder="Jelaskan alasan pembatalan (minimal 10 karakter)..."
                            rows="3"
                            :disabled="reversalForm.processing"
                            :class="{ 'border-destructive': reversalForm.errors.reason }"
                        />
                        <p v-if="reversalForm.errors.reason" class="text-sm text-destructive">
                            {{ reversalForm.errors.reason }}
                        </p>
                        <p v-else class="text-xs text-muted-foreground">
                            {{ reversalForm.reason.length }} / 10 karakter minimum
                        </p>
                    </div>

                    <DialogFooter class="gap-2 sm:gap-0">
                        <Button
                            type="button"
                            variant="outline"
                            @click="closeReversalModal"
                            :disabled="reversalForm.processing"
                            class="rounded-xl"
                        >
                            Batal
                        </Button>
                        <Button
                            type="submit"
                            variant="destructive"
                            class="gap-2 rounded-xl bg-orange-500 hover:bg-orange-600"
                            :disabled="reversalForm.processing || !reversalForm.password || reversalForm.reason.length < 10"
                        >
                            <RotateCcw v-if="!reversalForm.processing" class="h-4 w-4" />
                            {{ reversalForm.processing ? 'Memproses...' : 'Konfirmasi Pembatalan' }}
                        </Button>
                    </DialogFooter>
                </form>
            </DialogContent>
        </Dialog>
    </AppLayout>
</template>
