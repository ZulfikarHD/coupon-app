<script setup lang="ts">
import PasswordController from '@/actions/App/Http/Controllers/Settings/PasswordController';
import InputError from '@/components/InputError.vue';
import PageHeader from '@/components/PageHeader.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import SettingsLayout from '@/layouts/settings/Layout.vue';
import { edit } from '@/routes/user-password';
import { Form, Head } from '@inertiajs/vue3';
import { useHaptic } from '@/composables/useHaptic';
import { ref, onMounted } from 'vue';

import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { type BreadcrumbItem } from '@/types';

const { trigger } = useHaptic();
const isLoaded = ref(false);

onMounted(() => {
    requestAnimationFrame(() => {
        isLoaded.value = true;
    });
});

const breadcrumbItems: BreadcrumbItem[] = [
    {
        title: 'Pengaturan kata sandi',
        href: edit().url,
    },
];

const handleSubmit = () => {
    trigger('medium');
};
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbItems">
        <Head title="Pengaturan kata sandi" />

        <SettingsLayout>
            <div class="space-y-6 p-4 md:p-0">
                <div
                    :class="[
                        'transition-all duration-500',
                        isLoaded ? 'translate-y-0 opacity-100' : 'translate-y-4 opacity-0',
                    ]"
                >
                    <PageHeader
                        title="Ubah kata sandi"
                        description="Pastikan akun kamu menggunakan kata sandi yang panjang dan aman"
                    />
                </div>

                <Form
                    v-bind="PasswordController.update.form()"
                    :options="{
                        preserveScroll: true,
                    }"
                    reset-on-success
                    :reset-on-error="[
                        'password',
                        'password_confirmation',
                        'current_password',
                    ]"
                    :class="[
                        'space-y-6',
                        'transition-all duration-500 delay-100',
                        isLoaded ? 'translate-y-0 opacity-100' : 'translate-y-4 opacity-0',
                    ]"
                    v-slot="{ errors, processing, recentlySuccessful }"
                >
                    <div class="grid gap-2 form-field-focus">
                        <Label for="current_password" class="text-sm font-medium">Kata sandi saat ini</Label>
                        <Input
                            id="current_password"
                            name="current_password"
                            type="password"
                            class="h-11 text-base rounded-xl ios-input-focus md:h-10 md:text-sm"
                            autocomplete="current-password"
                            placeholder="Kata sandi saat ini"
                        />
                        <InputError :message="errors.current_password" />
                    </div>

                    <div class="grid gap-2 form-field-focus">
                        <Label for="password" class="text-sm font-medium">Kata sandi baru</Label>
                        <Input
                            id="password"
                            name="password"
                            type="password"
                            class="h-11 text-base rounded-xl ios-input-focus md:h-10 md:text-sm"
                            autocomplete="new-password"
                            placeholder="Kata sandi baru"
                        />
                        <InputError :message="errors.password" />
                        <p class="text-xs text-muted-foreground">
                            Gunakan kombinasi huruf, angka, dan karakter khusus
                        </p>
                    </div>

                    <div class="grid gap-2 form-field-focus">
                        <Label for="password_confirmation" class="text-sm font-medium">Konfirmasi kata sandi</Label>
                        <Input
                            id="password_confirmation"
                            name="password_confirmation"
                            type="password"
                            class="h-11 text-base rounded-xl ios-input-focus md:h-10 md:text-sm"
                            autocomplete="new-password"
                            placeholder="Konfirmasi kata sandi"
                        />
                        <InputError :message="errors.password_confirmation" />
                    </div>

                    <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
                        <Button
                            :disabled="processing"
                            data-test="update-password-button"
                            :class="[
                                'h-11 w-full sm:w-auto rounded-xl',
                                'btn-gradient press-effect',
                            ]"
                            @click="handleSubmit"
                        >
                            {{ processing ? 'Menyimpan...' : 'Simpan kata sandi' }}
                        </Button>

                        <Transition
                            enter-active-class="transition ease-in-out"
                            enter-from-class="opacity-0"
                            leave-active-class="transition ease-in-out"
                            leave-to-class="opacity-0"
                        >
                            <p
                                v-show="recentlySuccessful"
                                class="text-sm text-green-600 font-medium"
                            >
                                Tersimpan.
                            </p>
                        </Transition>
                    </div>
                </Form>
            </div>
        </SettingsLayout>
    </AppLayout>
</template>
