// composables/auth/useLogin.js

import { useAjax } from '@editor/composables/useAjax.js'

/**
 * Composable to log in the user using the AJAX login endpoint.
 *
 * @returns {(username: string, password: string) => Promise<void>}
 */
export function useLogin() {
  const loginRequest = useAjax('rooty/login')

  return async (username, password) => {
    try {
      const response = await loginRequest({ username, password })

      if (!response?.success) {
        throw new Error(response?.message || 'Identifiants invalides.')
      }

      // On succès : aucune donnée spécifique n’est utilisée ici, l'appelant décide quoi faire.
      return
    } catch (error) {
      throw new Error(error?.message || 'Erreur lors de la tentative de connexion.')
    }
  }
}
