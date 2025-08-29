<template>
  <div class="button-group">
    <slot />
  </div>
</template>

<script setup>
import { provide, toRef } from 'vue'

const props = defineProps({
  size: String,
  severity: String,
  variant: String,
  disabled: { type: Boolean, default: false }
})

provide('buttonGroupSize', toRef(props, 'size'))
provide('buttonGroupSeverity', toRef(props, 'severity'))
provide('buttonGroupVariant', toRef(props, 'variant'))
provide('buttonGroupDisabled', toRef(props, 'disabled'))
</script>
