// bootstrap/heartbeat.js

import { useUserLoggedInCheck } from '@editor/composables/heartbeat/checks/useUserLoggedInCheck.js'

export function getHeartbeatChecks() {
  return [
    {
      check: useUserLoggedInCheck(),
      interval: 2000,
      pauseOnError: true,
    },
  ]
}
