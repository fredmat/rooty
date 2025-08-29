// stores/overlayStore.js

import { ref } from 'vue'
import { defineStore } from 'pinia'

/**
 * Store to control the global AppOverlay visibility and content.
 */
export const useOverlayStore = defineStore('overlay', () => {
  const visible = ref(false)
  const title = ref('')
  const message = ref('')
  const action = ref(null)     // { label: String, onClick: Function }
  const type = ref(null)       // ex: 'login', 'error', etc.
  const fullscreen = ref(false) // Whether the overlay should hide the main layout

  /**
   * Show the overlay with given configuration.
   *
   * @param {{
   *   title: string,
   *   message?: string,
   *   action?: { label: string, onClick: Function },
   *   type?: string,
   *   fullscreen?: boolean
   * }} options
   */
  function show({
    title: t,
    message: m = '',
    action: a = null,
    type: ty = null,
    fullscreen: fs = false,
  }) {
    visible.value = true
    title.value = t
    message.value = m
    action.value = a
    type.value = ty
    fullscreen.value = fs
  }

  /**
   * Hide the overlay and reset its content.
   */
  function hide() {
    visible.value = false
    title.value = ''
    message.value = ''
    action.value = null
    type.value = null
    fullscreen.value = false
  }

  return {
    visible,
    title,
    message,
    action,
    type,
    fullscreen,
    show,
    hide,
  }
})
