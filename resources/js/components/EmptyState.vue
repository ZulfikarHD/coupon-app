<script setup lang="ts">
import { type Component } from 'vue';

interface Props {
    icon?: Component;
    title?: string;
    description?: string;
    class?: string;
}

withDefaults(defineProps<Props>(), {
    title: 'Tidak ada data',
    description: '',
    class: '',
});
</script>

<template>
    <div :class="['py-12 text-center', $attrs.class]">
        <component
            v-if="icon"
            :is="icon"
            class="mx-auto h-12 w-12 text-muted-foreground mb-4"
        />
        <p class="text-base font-medium text-foreground mb-1">
            {{ title }}
        </p>
        <p v-if="description" class="text-sm text-muted-foreground">
            {{ description }}
        </p>
        <slot />
    </div>
</template>
