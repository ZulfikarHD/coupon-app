<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/vue3';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import {
    Collapsible,
    CollapsibleContent,
    CollapsibleTrigger,
} from '@/components/ui/collapsible';
import { ScanLine, ChevronDown, ChevronUp, AlertCircle, CheckCircle2, Loader2 } from 'lucide-vue-next';
import { onMounted, onUnmounted, ref, watch, onBeforeUnmount } from 'vue';
import { Html5Qrcode } from 'html5-qrcode';
import { useSweetAlert } from '@/composables/useSweetAlert';
import { router } from '@inertiajs/vue3';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: '/dashboard',
    },
    {
        title: 'Scan Kupon',
        href: '/scan',
    },
];

interface CouponData {
    code: string;
    type: string;
    description: string;
    customer_name: string;
    status: string;
    expires_at: string | null;
}

const swal = useSweetAlert();
const scannerId = 'qr-reader';
const html5QrCode = ref<Html5Qrcode | null>(null);
const isScanning = ref(false);
const scanningStatus = ref<string>('');
const showManualInput = ref(false);
const manualCode = ref('');
const isSubmittingManual = ref(false);

// Modal state
const showValidationModal = ref(false);
const couponData = ref<CouponData | null>(null);
const password = ref('');
const isSubmitting = ref(false);
const errorMessage = ref('');
const successMessage = ref('');

// Watch for success/error messages and show SweetAlert2 toasts
watch(successMessage, (message) => {
    if (message) {
        swal.toast(message, 'success');
    }
});

watch(errorMessage, (message) => {
    if (message) {
        swal.toast(message, 'error');
    }
});

const startScanner = async () => {
    try {
        if (html5QrCode.value) {
            return; // Already started
        }

        scanningStatus.value = 'Meminta izin kamera...';
        isScanning.value = true;

        html5QrCode.value = new Html5Qrcode(scannerId);

        await html5QrCode.value.start(
            { facingMode: 'environment' }, // Use back camera
            {
                fps: 10,
                qrbox: { width: 250, height: 250 },
                aspectRatio: 1.0,
            },
            (decodedText) => {
                handleScannedCode(decodedText);
            },
            (errorMessage) => {
                // Ignore scanning errors, just keep scanning
            },
            {
                // Disable verbose logging
                verbose: false,
            }
        );

        scanningStatus.value = 'Arahkan kamera ke QR Code kupon';
    } catch (err: any) {
        console.error('Error starting scanner:', err);
        isScanning.value = false;
        
        if (err.name === 'NotAllowedError' || err.message?.includes('permission')) {
            scanningStatus.value = 'Izin kamera ditolak. Silakan gunakan input manual.';
        } else if (err.name === 'NotFoundError') {
            scanningStatus.value = 'Kamera tidak ditemukan. Silakan gunakan input manual.';
        } else {
            scanningStatus.value = 'Gagal mengakses kamera. Silakan gunakan input manual.';
        }
    }
};

const stopScanner = async () => {
    if (html5QrCode.value) {
        try {
            await html5QrCode.value.stop().catch(() => {
                // Ignore stop errors (might already be stopped)
            });
            await html5QrCode.value.clear().catch(() => {
                // Ignore clear errors
            });
        } catch (err) {
            // Ignore any errors when stopping
            console.debug('Scanner cleanup:', err);
        } finally {
            html5QrCode.value = null;
        }
    }
    isScanning.value = false;
    scanningStatus.value = '';
};

const extractCodeFromUrl = (url: string): string => {
    // Extract code from URL like /coupon/ABC-1234-XYZ
    if (url.includes('/coupon/')) {
        const parts = url.split('/coupon/');
        return parts[parts.length - 1].split('?')[0].split('#')[0];
    }
    return url;
};

const getCsrfToken = (): string | null => {
    // Get CSRF token from cookie (Laravel sets XSRF-TOKEN cookie)
    const name = 'XSRF-TOKEN';
    const value = `; ${document.cookie}`;
    const parts = value.split(`; ${name}=`);
    if (parts.length === 2) {
        return decodeURIComponent(parts.pop()?.split(';').shift() || '');
    }
    // Fallback: try to get from meta tag if available
    const metaToken = document.querySelector('meta[name="csrf-token"]');
    return metaToken ? (metaToken as HTMLMetaElement).content : null;
};

const checkCoupon = async (code: string): Promise<{ success: boolean; data?: any; error?: string }> => {
    try {
        const response = await fetch(`/api/coupons/${encodeURIComponent(code)}/check`, {
            method: 'GET',
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
            },
            credentials: 'same-origin',
        });
        
        const data = await response.json();
        
        if (response.ok && data.can_validate) {
            return {
                success: true,
                data: data.coupon,
            };
        } else {
            return {
                success: false,
                error: data.message || 'Kupon tidak dapat divalidasi',
            };
        }
    } catch (err: any) {
        return {
            success: false,
            error: 'Terjadi kesalahan. Silakan coba lagi.',
        };
    }
};

const handleScannedCode = async (decodedText: string) => {
    // Stop scanner temporarily
    await stopScanner();
    
    const code = extractCodeFromUrl(decodedText);
    scanningStatus.value = `Kode terdeteksi: ${code}. Memeriksa...`;

    const result = await checkCoupon(code);
    
    if (result.success && result.data) {
        couponData.value = result.data;
        showValidationModal.value = true;
        errorMessage.value = '';
        password.value = '';
    } else {
        errorMessage.value = result.error || 'Kupon tidak dapat divalidasi';
        scanningStatus.value = result.error || 'Kupon tidak dapat divalidasi';
        
        // Restart scanner after 3 seconds
        setTimeout(() => {
            startScanner();
        }, 3000);
    }
};

const handleManualSubmit = async () => {
    if (!manualCode.value.trim()) {
        errorMessage.value = 'Masukkan kode kupon';
        return;
    }

    isSubmittingManual.value = true;
    errorMessage.value = '';
    
    const code = extractCodeFromUrl(manualCode.value.trim());
    const result = await checkCoupon(code);
    
    isSubmittingManual.value = false;
    
    if (result.success && result.data) {
        couponData.value = result.data;
        showValidationModal.value = true;
        manualCode.value = '';
    } else {
        errorMessage.value = result.error || 'Kupon tidak dapat divalidasi';
    }
};

const handleValidate = async () => {
    if (!password.value.trim()) {
        errorMessage.value = 'Password wajib diisi';
        return;
    }

    if (!couponData.value) {
        return;
    }

    isSubmitting.value = true;
    errorMessage.value = '';

    try {
        const csrfToken = getCsrfToken();
        const headers: Record<string, string> = {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
        };
        
        if (csrfToken) {
            headers['X-XSRF-TOKEN'] = csrfToken;
        }

        const response = await fetch(`/coupons/${encodeURIComponent(couponData.value.code)}/validate`, {
            method: 'POST',
            headers,
            credentials: 'same-origin',
            body: JSON.stringify({ password: password.value }),
        });

        const data = await response.json();

        if (response.ok) {
            const message = data.message || 'Kupon berhasil divalidasi';
            successMessage.value = message;
            showValidationModal.value = false;
            
            // Reset form
            password.value = '';
            couponData.value = null;
            
            // Clear success message after 5 seconds
            setTimeout(() => {
                successMessage.value = '';
            }, 5000);
            
            // Restart scanner after 3 seconds
            setTimeout(() => {
                startScanner();
            }, 3000);
        } else {
            if (response.status === 401) {
                errorMessage.value = 'Password salah';
            } else if (response.status === 422) {
                errorMessage.value = data.message || 'Kupon tidak dapat divalidasi';
            } else {
                errorMessage.value = data.message || 'Terjadi kesalahan. Silakan coba lagi.';
            }
        }
    } catch (err: any) {
        errorMessage.value = 'Terjadi kesalahan. Silakan coba lagi.';
    } finally {
        isSubmitting.value = false;
    }
};

const handleModalClose = () => {
    showValidationModal.value = false;
    password.value = '';
    errorMessage.value = '';
    couponData.value = null;
    
    // Restart scanner
    if (!isScanning.value) {
        startScanner();
    }
};

onMounted(() => {
    startScanner();
});

onBeforeUnmount(() => {
    stopScanner();
});

onUnmounted(() => {
    stopScanner();
});

// Stop scanner when navigating away using Inertia events
if (typeof window !== 'undefined') {
    const stopOnNavigate = () => {
        stopScanner();
    };
    
    // Listen to Inertia navigation events
    document.addEventListener('inertia:start', stopOnNavigate);
    
    onUnmounted(() => {
        document.removeEventListener('inertia:start', stopOnNavigate);
    });
}
</script>

<template>
    <Head title="Scan Kupon" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 sm:gap-6 overflow-x-auto p-4 md:p-6">
            <Card class="border rounded-xl">
                <CardHeader class="pb-4">
                    <div class="flex items-center gap-2">
                        <ScanLine class="h-5 w-5 text-primary" />
                        <CardTitle class="text-base sm:text-lg font-semibold">Scan Kupon</CardTitle>
                    </div>
                    <CardDescription class="text-sm mt-1">
                        Arahkan kamera ke QR Code kupon untuk memvalidasi
                    </CardDescription>
                </CardHeader>
                <CardContent class="space-y-4">
                    <!-- Scanner View -->
                    <div class="space-y-4">
                        <div
                            :id="scannerId"
                            class="w-full rounded-xl border-2 border-dashed bg-gray-100 dark:bg-gray-800 flex items-center justify-center overflow-hidden relative"
                            style="min-height: 400px; aspect-ratio: 1;"
                        >
                            <div v-if="!isScanning" class="text-center space-y-2 p-8">
                                <ScanLine class="h-12 w-12 mx-auto text-muted-foreground" />
                                <p class="text-sm text-muted-foreground">Tekan tombol untuk memulai scan</p>
                            </div>
                        </div>
                        
                        <div v-if="scanningStatus" class="flex items-center gap-2 rounded-xl border p-4 bg-card">
                            <Loader2 v-if="isScanning && !scanningStatus.includes('terdeteksi')" class="h-5 w-5 animate-spin text-primary flex-shrink-0" />
                            <AlertCircle v-else-if="errorMessage || scanningStatus.includes('tidak')" class="h-5 w-5 text-destructive flex-shrink-0" />
                            <CheckCircle2 v-else class="h-5 w-5 text-green-600 flex-shrink-0" />
                            <p class="text-sm flex-1" :class="errorMessage || scanningStatus.includes('tidak') ? 'text-destructive' : ''">
                                {{ scanningStatus }}
                            </p>
                        </div>

                        <!-- Manual Input Section -->
                        <Collapsible v-model="showManualInput">
                            <CollapsibleTrigger as-child>
                                <Button variant="outline" class="w-full justify-between rounded-xl active:scale-[0.98] transition-transform">
                                    <span>Atau masukkan kode manual</span>
                                    <ChevronDown v-if="!showManualInput" class="h-4 w-4" />
                                    <ChevronUp v-else class="h-4 w-4" />
                                </Button>
                            </CollapsibleTrigger>
                            <CollapsibleContent class="mt-4 space-y-4">
                                <div class="space-y-3">
                                    <Label for="manual-code" class="text-sm font-medium">Kode Kupon</Label>
                                    <div class="flex flex-col gap-2 sm:flex-row">
                                        <Input
                                            id="manual-code"
                                            v-model="manualCode"
                                            placeholder="Masukkan kode kupon atau URL"
                                            class="flex-1 h-11 text-base rounded-xl sm:h-10 sm:text-sm"
                                            @keyup.enter="handleManualSubmit"
                                        />
                                        <Button
                                            @click="handleManualSubmit"
                                            :disabled="isSubmittingManual"
                                            class="h-11 w-full sm:w-auto rounded-xl active:scale-[0.98] transition-transform"
                                        >
                                            <Loader2 v-if="isSubmittingManual" class="mr-2 h-4 w-4 animate-spin" />
                                            Cek Kode
                                        </Button>
                                    </div>
                                    <p class="text-xs text-muted-foreground">
                                        Anda dapat memasukkan kode kupon atau URL lengkap
                                    </p>
                                </div>
                            </CollapsibleContent>
                        </Collapsible>

                        <!-- Error Message -->
                        <div v-if="errorMessage" class="flex items-center gap-2 rounded-xl border border-destructive bg-destructive/10 p-3">
                            <AlertCircle class="h-4 w-4 text-destructive" />
                            <p class="text-sm text-destructive">{{ errorMessage }}</p>
                        </div>

                        <!-- Success Message -->
                        <div v-if="successMessage" class="flex items-center gap-2 rounded-xl border border-green-500 bg-green-500/10 p-3">
                            <CheckCircle2 class="h-4 w-4 text-green-600" />
                            <p class="text-sm text-green-600">{{ successMessage }}</p>
                        </div>
                    </div>
                </CardContent>
            </Card>
        </div>

        <!-- Validation Confirmation Modal -->
        <Dialog :open="showValidationModal" @update:open="handleModalClose">
            <DialogContent class="sm:max-w-md max-h-[90vh] overflow-y-auto">
                <DialogHeader>
                    <DialogTitle class="text-lg sm:text-xl">Konfirmasi Penggunaan Kupon</DialogTitle>
                    <DialogDescription class="text-sm">
                        Masukkan password Anda untuk mengkonfirmasi penggunaan kupon ini
                    </DialogDescription>
                </DialogHeader>

                <div v-if="couponData" class="space-y-4 py-4">
                    <!-- Coupon Info -->
                    <div class="space-y-3 rounded-xl border bg-muted/50 p-4">
                        <div>
                            <p class="text-xs font-medium text-muted-foreground">Kode Kupon</p>
                            <p class="mt-1 font-mono font-semibold text-base break-all">{{ couponData.code }}</p>
                        </div>
                        <div>
                            <p class="text-xs font-medium text-muted-foreground">Jenis</p>
                            <p class="mt-1 text-sm">{{ couponData.type }}</p>
                        </div>
                        <div>
                            <p class="text-xs font-medium text-muted-foreground">Pelanggan</p>
                            <p class="mt-1 text-sm">{{ couponData.customer_name }}</p>
                        </div>
                    </div>

                    <!-- Password Input -->
                    <div class="space-y-2">
                        <Label for="password" class="text-sm font-medium">Password</Label>
                        <Input
                            id="password"
                            v-model="password"
                            type="password"
                            placeholder="Masukkan password Anda"
                            :disabled="isSubmitting"
                            class="h-11 text-base rounded-xl md:h-10 md:text-sm"
                            @keyup.enter="handleValidate"
                        />
                    </div>

                    <!-- Error Message -->
                    <div v-if="errorMessage" class="flex items-center gap-2 rounded-xl border border-destructive bg-destructive/10 p-3">
                        <AlertCircle class="h-4 w-4 text-destructive" />
                        <p class="text-sm text-destructive">{{ errorMessage }}</p>
                    </div>
                </div>

                <DialogFooter class="flex-col gap-2 sm:flex-row">
                    <Button
                        variant="outline"
                        @click="handleModalClose"
                        :disabled="isSubmitting"
                        class="w-full sm:w-auto rounded-xl h-11 active:scale-[0.98] transition-transform"
                    >
                        Batal
                    </Button>
                    <Button
                        @click="handleValidate"
                        :disabled="isSubmitting || !password.trim()"
                        class="w-full sm:w-auto rounded-xl h-11 active:scale-[0.98] transition-transform"
                    >
                        <Loader2 v-if="isSubmitting" class="mr-2 h-4 w-4 animate-spin" />
                        Konfirmasi Penggunaan
                    </Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>
    </AppLayout>
</template>

<style scoped>
/* Ensure scanner elements are contained and don't block navigation */
#qr-reader {
    position: relative;
    isolation: isolate;
    contain: layout style paint;
    overflow: hidden;
}

/* Contain scanner video and canvas within the container */
#qr-reader video,
#qr-reader canvas {
    position: absolute !important;
    top: 0 !important;
    left: 0 !important;
    width: 100% !important;
    height: 100% !important;
    object-fit: cover;
    pointer-events: auto;
}

/* Prevent any Html5Qrcode overlays from blocking navigation */
:deep(#qr-reader__dashboard),
:deep(#qr-reader__camera_selection),
:deep(#qr-reader__file_selection) {
    position: absolute !important;
    z-index: 1 !important;
    pointer-events: auto;
}

/* Ensure navigation elements stay on top and are clickable */
:deep(nav) {
    position: relative !important;
    z-index: 1000 !important;
    pointer-events: auto !important;
}

/* Ensure links in navigation are clickable */
:deep(nav a),
:deep(nav button),
:deep([role="navigation"] a),
:deep([role="navigation"] button) {
    position: relative !important;
    z-index: 1001 !important;
    pointer-events: auto !important;
}
</style>
