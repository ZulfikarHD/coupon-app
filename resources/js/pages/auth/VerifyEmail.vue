<script setup lang="ts">
import TextLink from '@/components/TextLink.vue';
import { Button } from '@/components/ui/button';
import { Spinner } from '@/components/ui/spinner';
import { useHaptic } from '@/composables/useHaptic';
import AuthLayout from '@/layouts/AuthLayout.vue';
import { logout } from '@/routes';
import { send } from '@/routes/verification';
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

const handleResend = () => {
    trigger('medium');
};
</script>

<template>
    <AuthLayout
        title="Verify your email"
        description="We've sent a verification link to your email address"
    >
        <Head title="Email verification" />

        <!-- Success Status -->
        <div
            v-if="status === 'verification-link-sent'"
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
                A new verification link has been sent!
            </div>
        </div>

        <div class="space-y-6">
            <!-- Email Icon & Info -->
            <div
                :class="[
                    'flex flex-col items-center gap-4 rounded-xl bg-sky-50 p-6 dark:bg-sky-500/10',
                    'transition-all duration-500',
                    isLoaded
                        ? 'translate-y-0 opacity-100'
                        : 'translate-y-4 opacity-0',
                ]"
                style="transition-delay: 50ms"
            >
                <div class="flex h-16 w-16 items-center justify-center rounded-full bg-sky-100 dark:bg-sky-500/20">
                    <svg
                        class="h-8 w-8 text-sky-600 dark:text-sky-400"
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
                <p class="text-center text-sm text-sky-700 dark:text-sky-300">
                    Please check your inbox and click the verification link to activate your account.
                </p>
            </div>

            <!-- Resend Form -->
            <Form
                v-bind="send.form()"
                class="space-y-4"
                v-slot="{ processing }"
                @submit="handleResend"
            >
                <div
                    :class="[
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
                    >
                        <Spinner v-if="processing" class="mr-2" />
                        <span v-if="processing">Sending...</span>
                        <span v-else>Resend verification email</span>
                    </Button>
                </div>
            </Form>

            <!-- Logout Link -->
            <div
                :class="[
                    'text-center',
                    'transition-all duration-500',
                    isLoaded
                        ? 'translate-y-0 opacity-100'
                        : 'translate-y-4 opacity-0',
                ]"
                style="transition-delay: 150ms"
            >
                <TextLink
                    :href="logout()"
                    as="button"
                    class="text-sm font-medium text-muted-foreground transition-colors hover:text-foreground"
                >
                    Sign out
                </TextLink>
            </div>
        </div>
    </AuthLayout>
</template>
