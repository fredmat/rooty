// composables/heartbeat/useHeartbeatManager.js

import { useOverlayStore } from '@editor/stores/overlayStore.js'
import { useIntervalFn } from '@vueuse/core'

/**
 * Starts a heartbeat loop for a single check.
 *
 * @param {() => Promise<void>} check - The async check function
 * @param {number} interval - Milliseconds between executions
 * @param {boolean} [pauseOnError=true] - Whether to pause the loop if the check fails
 * @returns {{ pause: () => void, resume: () => void }}
 */
export function useHeartbeatManager(check, interval = 15000, pauseOnError = true) {
  const overlay = useOverlayStore()

  const heartbeat = async () => {
    try {
      await check()
    } catch (error) {
      console.warn('[Heartbeat] Check failed:', error)

      // Fallback values if no overlay metadata is returned from the error
      const fallback = {
        title: 'Session interrompue',
        message: 'Un problème est survenu. Veuillez vous reconnecter.',
        type: 'error',
        fullscreen: true,
      }

      const {
        title,
        message,
        type,
        fullscreen,
      } = error?.data?.overlay ?? fallback

      overlay.show({ title, message, type, fullscreen })

      if (pauseOnError) pause()
    }
  }

  const { pause, resume } = useIntervalFn(heartbeat, interval, {
    immediate: true,
  })

  return { pause, resume }
}
