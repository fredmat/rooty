// composables/useAjax.js

import { useConfigStore } from '@editor/stores/configStore.js'

/**
 * Hook to send an AJAX POST request to WordPress admin-ajax.php
 * and handle WP_Error or HTTP errors properly.
 *
 * @param {string} action
 * @returns {(data?: Record<string, any>) => Promise<any>}
 */
export function useAjax(action) {
  return async (data = {}) => {
    const config = useConfigStore()

    const formData = new FormData()
    formData.append('action', action)
    formData.append('nonce', config.nonce)

    for (const [key, value] of Object.entries(data)) {
      formData.append(key, value)
    }

    let response
    let text

    try {
      response = await fetch(config.ajaxUrl, {
        method: 'POST',
        body: formData,
      })

      text = await response.text()
    } catch (error) {
      console.error('[useAjax] Network error:', error)
      throw new Error('Erreur réseau : impossible de joindre le serveur.')
    }

    // WordPress sometimes returns "0" for bad action or invalid nonce.
    if (text.trim() === '0') {
      throw {
        code: 'invalid_action',
        message: `Action "${action}" non reconnue ou nonce invalide.`,
        status: 400,
      }
    }

    let json

    try {
      json = JSON.parse(text)
    } catch (e) {
      throw {
        code: 'invalid_json',
        message: 'Réponse JSON invalide du serveur.',
        status: response.status || 500,
      }
    }

    // Handle WP_Error style responses or manual 'success: false'
    if (
      !response.ok ||
      json?.success === false ||
      (json?.code && json?.data?.status >= 400)
    ) {
      throw {
        code: json.code || 'error',
        message: json.message || 'Erreur serveur.',
        status: json.data?.status || response.status || 500,
        data: json.data || null,
      }
    }

    return json
  }
}
