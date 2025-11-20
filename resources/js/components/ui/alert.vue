<script setup lang="ts">
import { type HTMLAttributes, computed } from 'vue'
import { type AlertVariants, alertVariants } from '.'
import { cn } from '@/lib/utils'

const props = defineProps<{
  class?: HTMLAttributes['class']
  variant?: AlertVariants['variant']
}>()

const delegatedProps = computed(() => {
  const { class: _, ...delegated } = props
  return delegated
})
</script>

<template>
  <div
    v-bind="delegatedProps"
    :class="cn(alertVariants({ variant }), props.class)"
  >
    <slot />
  </div>
</template>
