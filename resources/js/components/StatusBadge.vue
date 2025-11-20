<script setup lang="ts">
import { computed } from 'vue';
import { type Component } from 'vue';
import { Badge } from '@/components/ui/badge';
import { useStatusColors } from '@/composables/useStatusColors';

interface Props {
    status?: 'active' | 'used' | 'expired';
    role?: 'admin' | 'user';
    label?: string;
    icon?: Component;
    variant?: 'default' | 'outline';
    size?: 'sm' | 'md' | 'lg';
}

const props = withDefaults(defineProps<Props>(), {
    variant: 'outline',
    size: 'md',
});

const { getStatusBadgeClasses, getRoleBadgeClasses, statusLabels, roleLabels } = useStatusColors();

const badgeClasses = computed(() => {
    if (props.status) {
        return getStatusBadgeClasses(props.status);
    }
    if (props.role) {
        return getRoleBadgeClasses(props.role);
    }
    return '';
});

const displayLabel = computed(() => {
    if (props.label) return props.label;
    if (props.status) return statusLabels[props.status];
    if (props.role) return roleLabels[props.role];
    return '';
});

const sizeClasses = {
    sm: 'text-xs px-2 py-0.5',
    md: 'text-sm px-3 py-1',
    lg: 'text-base px-4 py-1.5',
};
</script>

<template>
    <Badge
        :variant="variant"
        :class="[
            badgeClasses,
            sizeClasses[size],
            'rounded-full font-medium transition-colors',
        ]"
    >
        <component
            v-if="icon"
            :is="icon"
            :class="[
                'mr-1.5',
                size === 'sm' ? 'h-3 w-3' : size === 'md' ? 'h-3.5 w-3.5' : 'h-4 w-4',
            ]"
        />
        {{ displayLabel }}
    </Badge>
</template>
