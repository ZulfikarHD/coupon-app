<script setup lang="ts">
import InputError from '@/components/InputError.vue';
import TextLink from '@/components/TextLink.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';
import { useHaptic } from '@/composables/useHaptic';
import AuthBase from '@/layouts/AuthLayout.vue';
import { register } from '@/routes';
import { store } from '@/routes/login';
import { request } from '@/routes/password';
import { Form, Head } from '@inertiajs/vue3';
import { ref, onMounted } from 'vue';

defineProps<{
    status?: string;
    canResetPassword: boolean;
    canRegister: boolean;
}>();

const { trigger } = useHaptic();
const isLoaded = ref(false);
const rememberMe = ref(false);

onMounted(() => {
    requestAnimationFrame(() => {
        isLoaded.value = true;
    });
});

const handleSubmit = () => {
    trigger('medium');
};

const toggleRemember = () => {
    rememberMe.value = !rememberMe.value;
    trigger('light');
};
</script>

<template>
    <AuthBase
        title="Welcome back"
        description="Sign in to your account to continue"
    >
        <Head title="Log in" />

        <!-- Success Status Message -->
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

        <Form
            v-bind="store.form()"
            :reset-on-success="['password']"
            v-slot="{ errors, processing }"
            class="flex flex-col gap-5"
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
                        required
                        autofocus
                        :tabindex="1"
                        autocomplete="email"
                        placeholder="you@example.com"
                        :class="[
                            'ios-input-focus h-12 rounded-xl border-border/50 bg-background/50 pl-11 text-base',
                            'placeholder:text-muted-foreground/50',
                            'transition-all duration-300',
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
                <div class="flex items-center justify-between">
                    <Label for="password" class="text-sm font-medium">
                        Password
                    </Label>
                    <TextLink
                        v-if="canResetPassword"
                        :href="request()"
                        class="text-xs font-medium text-sky-600 transition-colors hover:text-sky-500 dark:text-sky-400"
                        :tabindex="5"
                    >
                        Forgot password?
                    </TextLink>
                </div>
                <div class="relative">
                    <Input
                        id="password"
                        type="password"
                        name="password"
                        required
                        :tabindex="2"
                        autocomplete="current-password"
                        placeholder="••••••••"
                        :class="[
                            'ios-input-focus h-12 rounded-xl border-border/50 bg-background/50 pl-11 text-base',
                            'placeholder:text-muted-foreground/50',
                            'transition-all duration-300',
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

            <!-- Remember Me Toggle (iOS Style) -->
            <div
                :class="[
                    'flex items-center justify-between',
                    'transition-all duration-500',
                    isLoaded
                        ? 'translate-y-0 opacity-100'
                        : 'translate-y-4 opacity-0',
                ]"
                style="transition-delay: 150ms"
            >
                <span class="text-sm text-muted-foreground">Remember me</span>
                <button
                    type="button"
                    role="switch"
                    :aria-checked="rememberMe"
                    :tabindex="3"
                    @click="toggleRemember"
                    :class="[
                        'relative inline-flex h-7 w-12 shrink-0 cursor-pointer rounded-full border-2 border-transparent',
                        'transition-all duration-300 ease-[cubic-bezier(0.34,1.56,0.64,1)]',
                        'focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-sky-500 focus-visible:ring-offset-2',
                        'press-effect',
                        rememberMe
                            ? 'bg-gradient-to-r from-sky-500 to-cyan-500'
                            : 'bg-muted',
                    ]"
                >
                    <span
                        :class="[
                            'pointer-events-none inline-block h-6 w-6 transform rounded-full bg-white shadow-lg ring-0',
                            'transition-transform duration-300 ease-[cubic-bezier(0.34,1.56,0.64,1)]',
                            rememberMe ? 'translate-x-5' : 'translate-x-0',
                        ]"
                    />
                    <input
                        type="hidden"
                        name="remember"
                        :value="rememberMe ? 'on' : ''"
                    />
                </button>
            </div>

            <!-- Submit Button -->
            <div
                :class="[
                    'pt-2',
                    'transition-all duration-500',
                    isLoaded
                        ? 'translate-y-0 opacity-100'
                        : 'translate-y-4 opacity-0',
                ]"
                style="transition-delay: 200ms"
            >
                <Button
                    type="submit"
                    :tabindex="4"
                    :disabled="processing"
                    data-test="login-button"
                    :class="[
                        'btn-gradient h-12 w-full rounded-xl text-base font-semibold',
                        'press-effect',
                        'disabled:opacity-70',
                    ]"
                >
                    <Spinner v-if="processing" class="mr-2" />
                    <span v-if="processing">Signing in...</span>
                    <span v-else>Sign in</span>
                </Button>
            </div>

            <!-- Register Link -->
            <div
                v-if="canRegister"
                :class="[
                    'pt-2 text-center text-sm',
                    'transition-all duration-500',
                    isLoaded
                        ? 'translate-y-0 opacity-100'
                        : 'translate-y-4 opacity-0',
                ]"
                style="transition-delay: 250ms"
            >
                <span class="text-muted-foreground">
                    Don't have an account?
                </span>
                <TextLink
                    :href="register()"
                    :tabindex="6"
                    class="ml-1 font-medium text-sky-600 transition-colors hover:text-sky-500 dark:text-sky-400"
                >
                    Create one
                </TextLink>
            </div>
        </Form>
    </AuthBase>
</template>
