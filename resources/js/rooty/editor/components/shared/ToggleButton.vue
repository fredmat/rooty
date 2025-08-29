<script setup>
import { computed } from 'vue'
import { useTheme } from '@/store/theme.js'
import { iconRegistry } from '@themeEditorRegistries/iconRegistry.js'

const props = defineProps({
  showLabel: {
    type: Boolean,
    default: true,
  },
})

const store = useTheme()

const themeToIconKey = {
  system: 'desktop',
  light: 'sun',
  dark: 'moon',
}

const buttonClasses = computed(() => [
  'button',
  'button--sm',
  store.appliedTheme === 'dark' ? 'button--dark' : 'button--secondary',
])

const iconComponent = computed(() => {
  const key = themeToIconKey[store.theme] || 'desktop'
  return iconRegistry[key] || null
})

const themeLabel = computed(() => {
  switch (store.theme) {
    case 'light': return 'Light'
    case 'dark': return 'Dark'
    default: return 'System'
  }
})

const toggleTheme = () => {
  store.switchTheme()
}
</script>

<template>
  <button
    type="button"
    :class="buttonClasses"
    @click="toggleTheme"
  >
    <component v-if="iconComponent" :is="iconComponent" />
    <span v-if="showLabel">{{ themeLabel }}</span>
  </button>
</template>
