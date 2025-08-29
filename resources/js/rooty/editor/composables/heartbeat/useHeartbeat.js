// composables/heartbeat/useHeartbeat.js

import { getHeartbeatChecks } from '@editor/bootstrap/heartbeat.js'
import { useHeartbeatManager } from './useHeartbeatManager.js'

export function useHeartbeat() {
  const managers = []
  const checks = getHeartbeatChecks()

  for (const { check, interval, pauseOnError = true } of checks) {
    const { pause, resume } = useHeartbeatManager(check, interval, pauseOnError)
    managers.push({ pause, resume })
  }

  return {
    pause: () => managers.forEach(m => m.pause()),
    resume: () => managers.forEach(m => m.resume()),
  }
}
