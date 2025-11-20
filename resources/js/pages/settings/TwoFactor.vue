<script setup lang="ts">
import PageHeader from '@/components/PageHeader.vue';
import TwoFactorRecoveryCodes from '@/components/TwoFactorRecoveryCodes.vue';
import TwoFactorSetupModal from '@/components/TwoFactorSetupModal.vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { useTwoFactorAuth } from '@/composables/useTwoFactorAuth';
import AppLayout from '@/layouts/AppLayout.vue';
import SettingsLayout from '@/layouts/settings/Layout.vue';
import { disable, enable, show } from '@/routes/two-factor';
import { BreadcrumbItem } from '@/types';
import { Form, Head } from '@inertiajs/vue3';
import { ShieldBan, ShieldCheck } from 'lucide-vue-next';
import { onUnmounted, ref } from 'vue';

interface Props {
    requiresConfirmation?: boolean;
    twoFactorEnabled?: boolean;
}

withDefaults(defineProps<Props>(), {
    requiresConfirmation: false,
    twoFactorEnabled: false,
});

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Two-Factor Authentication',
        href: show.url(),
    },
];

const { hasSetupData, clearTwoFactorAuthData } = useTwoFactorAuth();
const showSetupModal = ref<boolean>(false);

onUnmounted(() => {
    clearTwoFactorAuthData();
});
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head title="Two-Factor Authentication" />
        <SettingsLayout>
            <div class="space-y-6 p-4 md:p-0">
                <PageHeader
                    title="Two-Factor Authentication"
                    description="Manage your two-factor authentication settings"
                />

                <div
                    v-if="!twoFactorEnabled"
                    class="flex flex-col items-start justify-start space-y-4 rounded-xl border p-6"
                >
                    <Badge variant="destructive" class="text-sm px-3 py-1.5">Disabled</Badge>

                    <p class="text-sm sm:text-base text-muted-foreground leading-relaxed">
                        When you enable two-factor authentication, you will be
                        prompted for a secure pin during login. This pin can be
                        retrieved from a TOTP-supported application on your
                        phone.
                    </p>

                    <div class="w-full sm:w-auto">
                        <Button
                            v-if="hasSetupData"
                            @click="showSetupModal = true"
                            class="w-full sm:w-auto h-11 gap-2 rounded-xl active:scale-[0.98] transition-transform"
                        >
                            <ShieldCheck class="h-4 w-4" />
                            Continue Setup
                        </Button>
                        <Form
                            v-else
                            v-bind="enable.form()"
                            @success="showSetupModal = true"
                            #default="{ processing }"
                        >
                            <Button 
                                type="submit" 
                                :disabled="processing"
                                class="w-full sm:w-auto h-11 gap-2 rounded-xl active:scale-[0.98] transition-transform"
                            >
                                <ShieldCheck class="h-4 w-4" />
                                {{ processing ? 'Enabling...' : 'Enable 2FA' }}
                            </Button>
                        </Form>
                    </div>
                </div>

                <div
                    v-else
                    class="flex flex-col items-start justify-start space-y-4 rounded-xl border p-6"
                >
                    <Badge variant="default" class="text-sm px-3 py-1.5 bg-green-500/10 text-green-700 border-green-500/20">Enabled</Badge>

                    <p class="text-sm sm:text-base text-muted-foreground leading-relaxed">
                        With two-factor authentication enabled, you will be
                        prompted for a secure, random pin during login, which
                        you can retrieve from the TOTP-supported application on
                        your phone.
                    </p>

                    <TwoFactorRecoveryCodes />

                    <div class="w-full sm:w-auto">
                        <Form v-bind="disable.form()" #default="{ processing }">
                            <Button
                                variant="destructive"
                                type="submit"
                                :disabled="processing"
                                class="w-full sm:w-auto h-11 gap-2 rounded-xl active:scale-[0.98] transition-transform"
                            >
                                <ShieldBan class="h-4 w-4" />
                                {{ processing ? 'Disabling...' : 'Disable 2FA' }}
                            </Button>
                        </Form>
                    </div>
                </div>

                <TwoFactorSetupModal
                    v-model:isOpen="showSetupModal"
                    :requiresConfirmation="requiresConfirmation"
                    :twoFactorEnabled="twoFactorEnabled"
                />
            </div>
        </SettingsLayout>
    </AppLayout>
</template>
