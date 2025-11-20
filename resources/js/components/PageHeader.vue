<script setup lang="ts">
interface Props {
    title: string;
    description?: string;
    class?: string;
}

withDefaults(defineProps<Props>(), {
    description: '',
    class: '',
});
</script>

<template>
    <div :class="['space-y-1', $attrs.class]">
        <h1 class="text-2xl font-semibold tracking-tight md:text-3xl">
            {{ title }}
        </h1>
        <p v-if="description" class="text-sm text-muted-foreground md:text-base">
            {{ description }}
        </p>
    </div>
</template>
