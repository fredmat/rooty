// bootstrap/config.js

export default (() => {
  try {
    const el = document.getElementById('app')
    const raw = el?.dataset?.config ?? '{}'
    return JSON.parse(raw)
  } catch (e) {
    console.warn('[Rooty] Failed to parse config from #app', e)
    return {}
  }
})()
