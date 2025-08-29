import { isVNode } from 'vue'

export function hasValidSlot(slotFn) {
  const content = slotFn?.()
  return Array.isArray(content) && content.some(node => isVNode(node))
}
