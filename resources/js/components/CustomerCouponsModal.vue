<script setup lang="ts">
import { Dialog, DialogContent, DialogHeader, DialogTitle, DialogDescription } from '@/components/ui/dialog';
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import StatusBadge from '@/components/StatusBadge.vue';
import EmptyState from '@/components/EmptyState.vue';
import { Ticket, X, Eye, Loader2 } from 'lucide-vue-next';
import { ref, watch } from 'vue';
import { router } from '@inertiajs/vue3';

interface Coupon {
    id: number;
    code: string;
    type: string;
    customer_name: string;
    customer_phone: string;
    status: 'active' | 'used' | 'expired';
    created_at: string;
    expires_at: string | null;
    formatted_phone?: string;
}

interface Props {
    isOpen: boolean;
    customerName: string;
    customerPhone: string;
}

const props = defineProps<Props>();
const emit = defineEmits<{ 'update:isOpen': [value: boolean] }>();

const coupons = ref<Coupon[]>([]);
const isLoading = ref(false);
const error = ref<string | null>(null);

const fetchCoupons = async () => {
    if (!props.isOpen) return;
    
    isLoading.value = true;
    error.value = null;
    
    try {
        const response = await fetch(`/api/coupons?customer_phone=${encodeURIComponent(props.customerPhone)}&per_page=50`, {
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
            },
            credentials: 'same-origin',
        });
        
        if (!response.ok) {
            throw new Error('Failed to fetch coupons');
        }
        
        const data = await response.json();
        coupons.value = data.coupons?.data || [];
    } catch (err) {
        error.value = 'Gagal memuat kupon';
        console.error('Error fetching coupons:', err);
    } finally {
        isLoading.value = false;
    }
};

const closeModal = () => {
    emit('update:isOpen', false);
};

const viewCoupon = (couponId: number) => {
    router.visit(`/coupons/${couponId}`, {
        preserveState: true,
        preserveScroll: true,
    });
    closeModal();
};

watch(() => props.isOpen, (newValue) => {
    if (newValue) {
        fetchCoupons();
    } else {
        coupons.value = [];
        error.value = null;
    }
});
</script>

<template>
    <Dialog :open="isOpen" @update:open="(value) => emit('update:isOpen', value)">
        <DialogContent class="sm:max-w-2xl max-h-[85vh] overflow-hidden flex flex-col p-0 rounded-2xl border-0 shadow-2xl bg-background/95 backdrop-blur-xl [&>button]:hidden">
            <!-- iOS-style header -->
            <DialogHeader class="px-6 pt-6 pb-4 border-b border-border/50">
                <div class="flex items-center justify-between">
                    <div class="flex-1">
                        <DialogTitle class="text-xl font-semibold text-foreground">
                            Kupon Pelanggan
                        </DialogTitle>
                        <DialogDescription class="mt-1 text-sm text-muted-foreground">
                            {{ customerName }} • {{ customerPhone }}
                        </DialogDescription>
                    </div>
                    <Button
                        variant="ghost"
                        size="icon"
                        class="h-8 w-8 rounded-full hover:bg-muted"
                        @click="closeModal"
                    >
                        <X class="h-4 w-4" />
                    </Button>
                </div>
            </DialogHeader>

            <!-- Content area with scroll -->
            <div class="flex-1 overflow-y-auto px-6 py-4">
                <div v-if="isLoading" class="flex items-center justify-center py-12">
                    <Loader2 class="h-6 w-6 animate-spin text-muted-foreground" />
                </div>

                <div v-else-if="error" class="py-12 text-center">
                    <p class="text-sm text-destructive">{{ error }}</p>
                </div>

                <EmptyState
                    v-else-if="coupons.length === 0"
                    :icon="Ticket"
                    title="Tidak ada kupon"
                    description="Pelanggan ini belum memiliki kupon"
                />

                <div v-else class="space-y-3">
                    <div
                        v-for="coupon in coupons"
                        :key="coupon.id"
                        class="group relative rounded-xl border border-border/50 bg-card p-4 transition-all duration-200 hover:border-primary/50 hover:shadow-md"
                    >
                        <div class="flex items-start justify-between gap-4">
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center gap-2 mb-2">
                                    <span class="font-mono font-semibold text-base text-foreground">
                                        {{ coupon.code }}
                                    </span>
                                    <StatusBadge :status="coupon.status" size="sm" />
                                </div>
                                <p class="text-sm font-medium text-foreground mb-1">
                                    {{ coupon.type }}
                                </p>
                                <p class="text-xs text-muted-foreground">
                                    Dibuat: {{ new Date(coupon.created_at).toLocaleDateString('id-ID', {
                                        year: 'numeric',
                                        month: 'short',
                                        day: 'numeric',
                                    }) }}
                                </p>
                                <p v-if="coupon.expires_at" class="text-xs text-muted-foreground mt-1">
                                    Kedaluwarsa: {{ new Date(coupon.expires_at).toLocaleDateString('id-ID', {
                                        year: 'numeric',
                                        month: 'short',
                                        day: 'numeric',
                                    }) }}
                                </p>
                            </div>
                            <Button
                                variant="ghost"
                                size="sm"
                                class="h-9 rounded-xl flex-shrink-0"
                                @click="viewCoupon(coupon.id)"
                            >
                                <Eye class="h-4 w-4 mr-2" />
                                Lihat
                            </Button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- iOS-style footer -->
            <div class="px-6 py-4 border-t border-border/50 bg-muted/30">
                <Button
                    variant="outline"
                    class="w-full rounded-xl h-11 font-medium"
                    @click="closeModal"
                >
                    Tutup
                </Button>
            </div>
        </DialogContent>
    </Dialog>
</template>
