// composables/heartbeat/checks/useUserLoggedInCheck.js

import { useAjax } from '@editor/composables/useAjax.js'

/**
 * Heartbeat check to verify if the user is still logged in.
 * This will throw an error if the session is no longer valid.
 *
 * @returns {() => Promise<void>}
 */
export function useUserLoggedInCheck() {
  const send = useAjax('rooty/heartbeat/logged-in')

  return async () => {
    await send()
  }
}
