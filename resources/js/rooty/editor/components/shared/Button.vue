<template>
  <component
    :is="tag"
    :type="isButton ? props.type : undefined"
    :disabled="isButton ? isDisabled : undefined"
    :href="isLink && !isDisabled ? props.href : undefined"
    :aria-disabled="isLink && isDisabled ? 'true' : undefined"
    :tabindex="isLink && isDisabled ? -1 : undefined"
    :role="isLink ? 'button' : undefined"
    :class="classes"
    @click="handleClick"
  >
    <span v-if="showIcon && resolvedIconPos === 'left'" class="button-icon">
      <slot name="icon">
        <component :is="props.icon" />
      </slot>
    </span>
    <span v-if="hasLabel" class="button-label">
      <slot v-if="hasDefaultSlot" />
      <template v-else>{{ props.label }}</template>
    </span>
    <span v-if="showIcon && resolvedIconPos === 'right'" class="button-icon button-icon--end">
      <slot name="icon">
        <component :is="props.icon" />
      </slot>
    </span>
  </component>
</template>

<script setup>
import { computed, useSlots, inject } from 'vue'
import { hasValidSlot } from '@editor/utils/slots'

const emit = defineEmits(['click'])

const props = defineProps({
  as:       { type: String, default: 'button' },
  href:     { type: String, default: undefined },
  type:     { type: String, default: 'button' },
  disabled: { type: Boolean, default: false },
  label:    { type: String, default: '' },
  severity: { type: String, default: undefined },
  variant:  { type: String, default: undefined },
  size:     { type: String, default: undefined },
  icon:     { type: [Object, Function], default: null },
  iconPos:  { type: String, default: 'left' }
})

const groupSize = inject('buttonGroupSize', undefined)
const groupSeverity = inject('buttonGroupSeverity', undefined)
const groupVariant = inject('buttonGroupVariant', undefined)
const groupDisabled = inject('buttonGroupDisabled', undefined)

const resolvedSize = computed(() => props.size ?? groupSize?.value)
const resolvedSeverityProp = computed(() => props.severity ?? groupSeverity?.value)
const resolvedVariantProp = computed(() => props.variant ?? groupVariant?.value)
const isDisabled = computed(() => props.disabled || groupDisabled?.value)

const slots = useSlots()
const hasDefaultSlot = computed(() => hasValidSlot(slots.default))
const hasIconSlot = computed(() => hasValidSlot(slots.icon))
const hasLabel = computed(() => hasDefaultSlot.value || (props.label ?? '').trim().length > 0)
const showIcon = computed(() => !!props.icon || hasIconSlot.value)
const isIconOnly = computed(() => showIcon.value && !hasLabel.value)
const resolvedIconPos = computed(() => (props.iconPos || '').toLowerCase() === 'right' ? 'right' : 'left')

const tag = computed(() => (props.as === 'a' || props.as === 'button') ? props.as : 'button')
const isLink = computed(() => tag.value === 'a')
const isButton = computed(() => tag.value === 'button')

const VALID_SEVERITIES = new Set(['primary','secondary','info','success','warn','help','danger','contrast'])
const VALID_VISUALS = new Set(['outline','textual','raised'])

const normalizedVisual = computed(() => {
  const v = (resolvedVariantProp.value || '').trim().toLowerCase()
  return VALID_VISUALS.has(v) ? v : null
})

const normalizedSeverity = computed(() => {
  const s = (resolvedSeverityProp.value || '').trim().toLowerCase()
  return VALID_SEVERITIES.has(s) ? s : 'primary'
})

const classes = computed(() => {
  const base = 'button'
  const mods = [
    `--${normalizedSeverity.value}`,
    resolvedSize.value ? `--${resolvedSize.value}` : null,
    normalizedVisual.value ? `--${normalizedVisual.value}` : null,
    isIconOnly.value ? '--icon-only' : '--with-label'
  ].filter(Boolean).map(m => `${base}${m}`)
  return [base, ...mods].join(' ')
})

const handleClick = (event) => {
  if (isDisabled.value) {
    event.preventDefault()
    event.stopImmediatePropagation?.()
    return
  }
  emit('click', event)
}
</script>
