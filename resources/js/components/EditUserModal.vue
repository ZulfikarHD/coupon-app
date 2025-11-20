<script setup lang="ts">
import { Dialog, DialogContent, DialogHeader, DialogTitle, DialogDescription, DialogFooter } from '@/components/ui/dialog';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import InputError from '@/components/InputError.vue';
import { User, Mail, Lock, Shield, ChevronDown, X } from 'lucide-vue-next';
import { useForm } from '@inertiajs/vue3';
import { watch } from 'vue';

interface UserData {
    id: number;
    name: string;
    email: string;
    role: 'admin' | 'user';
}

interface Props {
    isOpen: boolean;
    user: UserData | null;
}

const props = defineProps<Props>();
const emit = defineEmits<{ 'update:isOpen': [value: boolean] }>();

const form = useForm({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
    role: 'user' as 'admin' | 'user',
});

const closeModal = () => {
    emit('update:isOpen', false);
    form.reset();
    form.clearErrors();
};

const submit = () => {
    if (!props.user) return;
    
    form.put(`/users/${props.user.id}`, {
        preserveScroll: true,
        onSuccess: () => {
            closeModal();
        },
    });
};

watch(() => props.isOpen, (newValue) => {
    if (newValue && props.user) {
        form.name = props.user.name;
        form.email = props.user.email;
        form.role = props.user.role;
        form.password = '';
        form.password_confirmation = '';
        form.clearErrors();
    }
});

watch(() => props.user, (newUser) => {
    if (newUser && props.isOpen) {
        form.name = newUser.name;
        form.email = newUser.email;
        form.role = newUser.role;
        form.password = '';
        form.password_confirmation = '';
        form.clearErrors();
    }
});
</script>

<template>
    <Dialog :open="isOpen" @update:open="(value) => emit('update:isOpen', value)">
        <DialogContent class="sm:max-w-md max-h-[90vh] overflow-hidden flex flex-col p-0 rounded-2xl border-0 shadow-2xl bg-background/95 backdrop-blur-xl [&>button]:hidden">
            <!-- iOS-style header -->
            <DialogHeader class="px-6 pt-6 pb-4 border-b border-border/50">
                <div class="flex items-center justify-between">
                    <div class="flex-1">
                        <DialogTitle class="text-xl font-semibold text-foreground">
                            Edit User
                        </DialogTitle>
                        <DialogDescription class="mt-1 text-sm text-muted-foreground">
                            Perbarui informasi user
                        </DialogDescription>
                    </div>
                    <Button
                        variant="ghost"
                        size="icon"
                        class="h-8 w-8 rounded-full hover:bg-muted"
                        @click="closeModal"
                        :disabled="form.processing"
                    >
                        <X class="h-4 w-4" />
                    </Button>
                </div>
            </DialogHeader>

            <!-- Content area with scroll -->
            <form @submit.prevent="submit" class="flex-1 overflow-y-auto px-6 py-4 space-y-5">
                <!-- Name -->
                <div class="space-y-2">
                    <Label for="edit-name" class="text-sm font-medium">
                        <div class="flex items-center gap-2">
                            <User class="h-4 w-4" />
                            Nama Lengkap
                        </div>
                    </Label>
                    <Input
                        id="edit-name"
                        v-model="form.name"
                        type="text"
                        placeholder="Masukkan nama lengkap"
                        required
                        autocomplete="name"
                        class="h-11 rounded-xl"
                        :class="{ 'border-destructive': form.errors.name }"
                        :disabled="form.processing"
                    />
                    <InputError :message="form.errors.name" />
                </div>

                <!-- Email -->
                <div class="space-y-2">
                    <Label for="edit-email" class="text-sm font-medium">
                        <div class="flex items-center gap-2">
                            <Mail class="h-4 w-4" />
                            Email
                        </div>
                    </Label>
                    <Input
                        id="edit-email"
                        v-model="form.email"
                        type="email"
                        placeholder="user@example.com"
                        required
                        autocomplete="email"
                        class="h-11 rounded-xl"
                        :class="{ 'border-destructive': form.errors.email }"
                        :disabled="form.processing"
                    />
                    <InputError :message="form.errors.email" />
                </div>

                <!-- Password (Optional) -->
                <div class="space-y-2">
                    <Label for="edit-password" class="text-sm font-medium">
                        <div class="flex items-center gap-2">
                            <Lock class="h-4 w-4" />
                            Password Baru (Opsional)
                        </div>
                    </Label>
                    <Input
                        id="edit-password"
                        v-model="form.password"
                        type="password"
                        placeholder="Kosongkan jika tidak ingin mengubah password"
                        autocomplete="new-password"
                        class="h-11 rounded-xl"
                        :class="{ 'border-destructive': form.errors.password }"
                        :disabled="form.processing"
                    />
                    <p class="text-xs text-muted-foreground">
                        Biarkan kosong jika tidak ingin mengubah password
                    </p>
                    <InputError :message="form.errors.password" />
                </div>

                <!-- Password Confirmation (if password is filled) -->
                <div v-if="form.password" class="space-y-2">
                    <Label for="edit-password-confirmation" class="text-sm font-medium">
                        <div class="flex items-center gap-2">
                            <Lock class="h-4 w-4" />
                            Konfirmasi Password Baru
                        </div>
                    </Label>
                    <Input
                        id="edit-password-confirmation"
                        v-model="form.password_confirmation"
                        type="password"
                        placeholder="Ulangi password baru"
                        autocomplete="new-password"
                        class="h-11 rounded-xl"
                        :class="{ 'border-destructive': form.errors.password_confirmation }"
                        :disabled="form.processing"
                    />
                    <InputError :message="form.errors.password_confirmation" />
                </div>

                <!-- Role -->
                <div class="space-y-2">
                    <Label for="edit-role" class="text-sm font-medium">
                        <div class="flex items-center gap-2">
                            <Shield class="h-4 w-4" />
                            Role
                        </div>
                    </Label>
                    <DropdownMenu>
                        <DropdownMenuTrigger as-child>
                            <Button
                                variant="outline"
                                class="w-full h-11 justify-between rounded-xl"
                                :class="{ 'border-destructive': form.errors.role }"
                                :disabled="form.processing"
                            >
                                {{ form.role === 'admin' ? 'Admin' : 'User' }}
                                <ChevronDown class="ml-2 h-4 w-4 opacity-50" />
                            </Button>
                        </DropdownMenuTrigger>
                        <DropdownMenuContent class="w-full">
                            <DropdownMenuItem
                                @click="form.role = 'user'"
                                :class="{ 'bg-accent': form.role === 'user' }"
                            >
                                User
                            </DropdownMenuItem>
                            <DropdownMenuItem
                                @click="form.role = 'admin'"
                                :class="{ 'bg-accent': form.role === 'admin' }"
                            >
                                Admin
                            </DropdownMenuItem>
                        </DropdownMenuContent>
                    </DropdownMenu>
                    <InputError :message="form.errors.role" />
                </div>
            </form>

            <!-- iOS-style footer -->
            <DialogFooter class="px-6 py-4 border-t border-border/50 bg-muted/30 gap-2">
                <Button
                    type="button"
                    variant="outline"
                    class="flex-1 h-11 rounded-xl font-medium"
                    @click="closeModal"
                    :disabled="form.processing"
                >
                    Batal
                </Button>
                <Button
                    type="submit"
                    class="flex-1 h-11 rounded-xl font-medium"
                    :disabled="form.processing"
                    @click="submit"
                >
                    {{ form.processing ? 'Menyimpan...' : 'Simpan' }}
                </Button>
            </DialogFooter>
        </DialogContent>
    </Dialog>
</template>
