<script setup lang="ts">
import InputError from '@/components/InputError.vue';
import TextLink from '@/components/TextLink.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';
import { useHaptic } from '@/composables/useHaptic';
import AuthLayout from '@/layouts/AuthLayout.vue';
import { login } from '@/routes';
import { email } from '@/routes/password';
import { Form, Head } from '@inertiajs/vue3';
import { ref, onMounted } from 'vue';

defineProps<{
    status?: string;
}>();

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
        title="Forgot password"
        description="Enter your email to receive a password reset link"
    >
        <Head title="Forgot password" />

        <!-- Success Status -->
        <div
            v-if="status"
            :class="[
                'mb-6 rounded-xl bg-emerald-50 p-4 text-center text-sm font-medium text-emerald-600',
                'dark:bg-emerald-500/10 dark:text-emerald-400',
                'animate-spring-in',
            ]"
        >
            <div class="flex items-center justify-center gap-2">
                <svg
                    class="h-5 w-5"
                    fill="none"
                    stroke="currentColor"
                    viewBox="0 0 24 24"
                >
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2"
                        d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"
                    />
                </svg>
                {{ status }}
            </div>
        </div>

        <div class="space-y-6">
            <Form
                v-bind="email.form()"
                v-slot="{ errors, processing }"
                @submit="handleSubmit"
            >
                <!-- Email Field -->
                <div
                    :class="[
                        'grid gap-2',
                        'transition-all duration-500',
                        isLoaded
                            ? 'translate-y-0 opacity-100'
                            : 'translate-y-4 opacity-0',
                    ]"
                    style="transition-delay: 50ms"
                >
                    <Label for="email" class="text-sm font-medium">
                        Email address
                    </Label>
                    <div class="relative">
                        <Input
                            id="email"
                            type="email"
                            name="email"
                            autocomplete="off"
                            autofocus
                            placeholder="you@example.com"
                            :class="[
                                'ios-input-focus h-12 rounded-xl border-border/50 bg-background/50 pl-11 text-base',
                                'placeholder:text-muted-foreground/50',
                                errors.email
                                    ? 'border-red-500 ring-2 ring-red-500/20'
                                    : '',
                            ]"
                        />
                        <!-- Email Icon -->
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
                                    d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"
                                />
                            </svg>
                        </div>
                    </div>
                    <InputError :message="errors.email" class="mt-1" />
                </div>

                <!-- Submit Button -->
                <div
                    :class="[
                        'my-6',
                        'transition-all duration-500',
                        isLoaded
                            ? 'translate-y-0 opacity-100'
                            : 'translate-y-4 opacity-0',
                    ]"
                    style="transition-delay: 100ms"
                >
                    <Button
                        :class="[
                            'btn-gradient h-12 w-full rounded-xl text-base font-semibold',
                            'press-effect',
                            'disabled:opacity-70',
                        ]"
                        :disabled="processing"
                        data-test="email-password-reset-link-button"
                    >
                        <Spinner v-if="processing" class="mr-2" />
                        <span v-if="processing">Sending...</span>
                        <span v-else>Send reset link</span>
                    </Button>
                </div>
            </Form>

            <!-- Back to Login -->
            <div
                :class="[
                    'text-center text-sm',
                    'transition-all duration-500',
                    isLoaded
                        ? 'translate-y-0 opacity-100'
                        : 'translate-y-4 opacity-0',
                ]"
                style="transition-delay: 150ms"
            >
                <span class="text-muted-foreground">Remember your password?</span>
                <TextLink
                    :href="login()"
                    class="ml-1 font-medium text-sky-600 transition-colors hover:text-sky-500 dark:text-sky-400"
                >
                    Sign in
                </TextLink>
            </div>
        </div>
    </AuthLayout>
</template>
