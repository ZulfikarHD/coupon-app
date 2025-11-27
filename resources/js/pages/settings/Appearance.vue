<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { useHaptic } from '@/composables/useHaptic';
import { ref, onMounted } from 'vue';

import AppearanceTabs from '@/components/AppearanceTabs.vue';
import PageHeader from '@/components/PageHeader.vue';
import { type BreadcrumbItem } from '@/types';

import AppLayout from '@/layouts/AppLayout.vue';
import SettingsLayout from '@/layouts/settings/Layout.vue';
import { edit } from '@/routes/appearance';

const { trigger } = useHaptic();
const isLoaded = ref(false);

onMounted(() => {
    requestAnimationFrame(() => {
        isLoaded.value = true;
    });
});

const breadcrumbItems: BreadcrumbItem[] = [
    {
        title: 'Appearance settings',
        href: edit().url,
    },
];
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbItems">
        <Head title="Appearance settings" />

        <SettingsLayout>
            <div class="space-y-6 p-4 md:p-0">
                <div
                    :class="[
                        'transition-all duration-500',
                        isLoaded ? 'translate-y-0 opacity-100' : 'translate-y-4 opacity-0',
                    ]"
                >
                    <PageHeader
                        title="Appearance settings"
                        description="Update your account's appearance settings"
                    />
                </div>
                <div
                    :class="[
                        'transition-all duration-500 delay-100',
                        isLoaded ? 'translate-y-0 opacity-100' : 'translate-y-4 opacity-0',
                    ]"
                >
                    <AppearanceTabs />
                </div>
            </div>
        </SettingsLayout>
    </AppLayout>
</template>
