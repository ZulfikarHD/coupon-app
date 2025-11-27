<script setup lang="ts">
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';
import { useHaptic } from '@/composables/useHaptic';
import AuthLayout from '@/layouts/AuthLayout.vue';
import { update } from '@/routes/password';
import { Form, Head } from '@inertiajs/vue3';
import { ref, onMounted, computed, watch } from 'vue';

const props = defineProps<{
    token: string;
    email: string;
}>();

const { trigger } = useHaptic();
const isLoaded = ref(false);
const inputEmail = ref(props.email);
const password = ref('');

onMounted(() => {
    requestAnimationFrame(() => {
        isLoaded.value = true;
    });
});

const handleSubmit = () => {
    trigger('medium');
};

// Password strength indicator
const passwordStrength = computed(() => {
    const p = password.value;
    if (!p) return { level: 0, label: '', color: '' };

    let score = 0;
    if (p.length >= 8) score++;
    if (p.length >= 12) score++;
    if (/[a-z]/.test(p) && /[A-Z]/.test(p)) score++;
    if (/\d/.test(p)) score++;
    if (/[^a-zA-Z0-9]/.test(p)) score++;

    if (score <= 1) return { level: 1, label: 'Weak', color: 'bg-red-500' };
    if (score <= 2) return { level: 2, label: 'Fair', color: 'bg-orange-500' };
    if (score <= 3) return { level: 3, label: 'Good', color: 'bg-yellow-500' };
    if (score <= 4)
        return { level: 4, label: 'Strong', color: 'bg-emerald-500' };
    return { level: 5, label: 'Excellent', color: 'bg-sky-500' };
});

watch(
    () => passwordStrength.value.level,
    (newLevel, oldLevel) => {
        if (newLevel > oldLevel) {
            trigger('light');
        }
    }
);
</script>

<template>
    <AuthLayout
        title="Reset password"
        description="Create a new secure password for your account"
    >
        <Head title="Reset password" />

        <Form
            v-bind="update.form()"
            :transform="(data) => ({ ...data, token, email })"
            :reset-on-success="['password', 'password_confirmation']"
            v-slot="{ errors, processing }"
            @submit="handleSubmit"
        >
            <div class="grid gap-5">
                <!-- Email Field (Read-only) -->
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
                    <Label for="email" class="text-sm font-medium">Email</Label>
                    <div class="relative">
                        <Input
                            id="email"
                            type="email"
                            name="email"
                            autocomplete="email"
                            v-model="inputEmail"
                            readonly
                            :class="[
                                'h-12 rounded-xl border-border/50 bg-muted/50 pl-11 text-base',
                                'cursor-not-allowed opacity-70',
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
                    <Label for="password" class="text-sm font-medium">
                        New password
                    </Label>
                    <div class="relative">
                        <Input
                            id="password"
                            type="password"
                            name="password"
                            autocomplete="new-password"
                            autofocus
                            placeholder="••••••••"
                            v-model="password"
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
                    <!-- Password Strength Indicator -->
                    <div v-if="password" class="mt-1 flex items-center gap-2">
                        <div class="flex flex-1 gap-1">
                            <div
                                v-for="i in 5"
                                :key="i"
                                :class="[
                                    'h-1 flex-1 rounded-full transition-all duration-300',
                                    i <= passwordStrength.level
                                        ? passwordStrength.color
                                        : 'bg-muted',
                                ]"
                            />
                        </div>
                        <span
                            :class="[
                                'text-xs font-medium transition-colors',
                                passwordStrength.level <= 1
                                    ? 'text-red-500'
                                    : passwordStrength.level <= 2
                                      ? 'text-orange-500'
                                      : passwordStrength.level <= 3
                                        ? 'text-yellow-600'
                                        : passwordStrength.level <= 4
                                          ? 'text-emerald-500'
                                          : 'text-sky-500',
                            ]"
                        >
                            {{ passwordStrength.label }}
                        </span>
                    </div>
                    <InputError :message="errors.password" class="mt-1" />
                </div>

                <!-- Confirm Password Field -->
                <div
                    :class="[
                        'grid gap-2',
                        'transition-all duration-500',
                        isLoaded
                            ? 'translate-y-0 opacity-100'
                            : 'translate-y-4 opacity-0',
                    ]"
                    style="transition-delay: 150ms"
                >
                    <Label for="password_confirmation" class="text-sm font-medium">
                        Confirm password
                    </Label>
                    <div class="relative">
                        <Input
                            id="password_confirmation"
                            type="password"
                            name="password_confirmation"
                            autocomplete="new-password"
                            placeholder="••••••••"
                            :class="[
                                'ios-input-focus h-12 rounded-xl border-border/50 bg-background/50 pl-11 text-base',
                                'placeholder:text-muted-foreground/50',
                                errors.password_confirmation
                                    ? 'border-red-500 ring-2 ring-red-500/20'
                                    : '',
                            ]"
                        />
                        <!-- Shield Icon -->
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
                                    d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"
                                />
                            </svg>
                        </div>
                    </div>
                    <InputError :message="errors.password_confirmation" class="mt-1" />
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
                        :class="[
                            'btn-gradient h-12 w-full rounded-xl text-base font-semibold',
                            'press-effect',
                            'disabled:opacity-70',
                        ]"
                        :disabled="processing"
                        data-test="reset-password-button"
                    >
                        <Spinner v-if="processing" class="mr-2" />
                        <span v-if="processing">Resetting...</span>
                        <span v-else>Reset password</span>
                    </Button>
                </div>
            </div>
        </Form>
    </AuthLayout>
</template>
