<script setup lang="ts">
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';
import { useHaptic } from '@/composables/useHaptic';
import AuthLayout from '@/layouts/AuthLayout.vue';
import { store } from '@/routes/password/confirm';
import { Form, Head } from '@inertiajs/vue3';
import { ref, onMounted } from 'vue';

const { trigger } = useHaptic();
const isLoaded = ref(false);

onMounted(() => {
    requestAnimationFrame(() => {
        isLoaded.value = true;
    });
});

const handleSubmit = () => {
    trigger('medium');
};
</script>

<template>
    <AuthLayout
        title="Confirm your password"
        description="This is a secure area. Please confirm your password to continue."
    >
        <Head title="Confirm password" />

        <Form
            v-bind="store.form()"
            reset-on-success
            v-slot="{ errors, processing }"
            @submit="handleSubmit"
        >
            <div class="space-y-6">
                <!-- Security Notice -->
                <div
                    :class="[
                        'flex items-start gap-3 rounded-xl bg-sky-50 p-4 dark:bg-sky-500/10',
                        'transition-all duration-500',
                        isLoaded
                            ? 'translate-y-0 opacity-100'
                            : 'translate-y-4 opacity-0',
                    ]"
                    style="transition-delay: 50ms"
                >
                    <svg
                        class="h-5 w-5 flex-shrink-0 text-sky-600 dark:text-sky-400"
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"
                        />
                    </svg>
                    <p class="text-sm text-sky-700 dark:text-sky-300">
                        For your security, please enter your password to access this protected area.
                    </p>
                </div>

                <!-- Password Field -->
                <div
                    :class="[
                        'grid gap-2',
                        'transition-all duration-500',
                        isLoaded
                            ? 'translate-y-0 opacity-100'
                            : 'translate-y-4 opacity-0',
                    ]"
                    style="transition-delay: 100ms"
                >
                    <Label for="password" class="text-sm font-medium">
                        Password
                    </Label>
                    <div class="relative">
                        <Input
                            id="password"
                            type="password"
                            name="password"
                            required
                            autocomplete="current-password"
                            autofocus
                            placeholder="••••••••"
                            :class="[
                                'ios-input-focus h-12 rounded-xl border-border/50 bg-background/50 pl-11 text-base',
                                'placeholder:text-muted-foreground/50',
                                errors.password
                                    ? 'border-red-500 ring-2 ring-red-500/20'
                                    : '',
                            ]"
                        />
                        <!-- Lock Icon -->
                        <div
                            class="pointer-events-none absolute left-4 top-1/2 -translate-y-1/2"
                        >
                            <svg
                                class="h-5 w-5 text-muted-foreground/50"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="1.5"
                                    d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"
                                />
                            </svg>
                        </div>
                    </div>
                    <InputError :message="errors.password" class="mt-1" />
                </div>

                <!-- Submit Button -->
                <div
                    :class="[
                        'transition-all duration-500',
                        isLoaded
                            ? 'translate-y-0 opacity-100'
                            : 'translate-y-4 opacity-0',
                    ]"
                    style="transition-delay: 150ms"
                >
                    <Button
                        :class="[
                            'btn-gradient h-12 w-full rounded-xl text-base font-semibold',
                            'press-effect',
                            'disabled:opacity-70',
                        ]"
                        :disabled="processing"
                        data-test="confirm-password-button"
                    >
                        <Spinner v-if="processing" class="mr-2" />
                        <span v-if="processing">Confirming...</span>
                        <span v-else>Confirm Password</span>
                    </Button>
                </div>
            </div>
        </Form>
    </AuthLayout>
</template>
