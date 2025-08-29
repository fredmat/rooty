<template>
  <Transition name="fade" appear>
    <div
      v-if="visible"
      class="fixed inset-0 z-50 flex items-center justify-center bg-black/70 backdrop-blur-sm"
      role="alertdialog"
      aria-modal="true"
    >
      <div
        class="bg-white text-black rounded-2xl p-6 shadow-2xl w-full max-w-sm text-center space-y-4"
      >
        <h2 class="text-xl font-bold">{{ title }}</h2>

        <p v-if="message" class="text-sm text-gray-600">
          {{ message }}
        </p>

        <slot />

        <div v-if="showActionButton" class="mt-4">
          <button
            @click="action.onClick"
            class="px-4 py-2 rounded-lg bg-black text-white hover:bg-gray-800 transition"
          >
            {{ action.label }}
          </button>
        </div>
      </div>
    </div>
  </Transition>
</template>

<script setup>
import { computed, useSlots } from 'vue'

const props = defineProps({
  visible: {
    type: Boolean,
    required: true,
  },
  title: {
    type: String,
    required: true,
  },
  message: {
    type: String,
    default: '',
  },
  action: {
    type: Object,
    default: null, // { label: String, onClick: Function }
  },
})

const slots = useSlots()

/**
 * Detects whether the default slot is actually rendering content.
 */
const hasSlotContent = computed(() => {
  const slot = slots.default?.()
  return !!(slot && slot.length > 0)
})

/**
 * Shows the action button only when no slot content is provided.
 */
const showActionButton = computed(() => {
  return props.action && !hasSlotContent.value
})
</script>

<style scoped>
.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.3s ease;
}
.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}
</style>
