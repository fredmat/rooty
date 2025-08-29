<template>
  <form @submit.prevent="submit" class="space-y-4 text-left">
    <div>
      <label for="username" class="block text-sm font-medium text-gray-700">
        Identifiant
      </label>
      <input
        id="username"
        v-model="username"
        type="text"
        autocomplete="username"
        required
        class="mt-1 w-full rounded-lg border border-gray-300 px-4 py-2 focus:outline-none focus:ring-2 focus:ring-black"
      />
    </div>

    <div>
      <label for="password" class="block text-sm font-medium text-gray-700">
        Mot de passe
      </label>
      <input
        id="password"
        v-model="password"
        type="password"
        autocomplete="current-password"
        required
        class="mt-1 w-full rounded-lg border border-gray-300 px-4 py-2 focus:outline-none focus:ring-2 focus:ring-black"
      />
    </div>

    <button
      type="submit"
      :disabled="loading"
      class="w-full bg-black text-white py-2 rounded-lg hover:bg-gray-800 transition disabled:opacity-50"
    >
      Connexion
    </button>

    <p v-if="error" class="text-red-600 text-sm text-center mt-2">
      {{ error }}
    </p>
  </form>
</template>

<script setup>
import { ref } from 'vue'
import { useLogin } from '@editor/composables/auth/useLogin.js'

const username = ref('')
const password = ref('')
const error = ref(null)
const loading = ref(false)

const login = useLogin()

const submit = async () => {
  loading.value = true
  error.value = null

  try {
    await login(username.value, password.value)
    window.location.reload()
  } catch (e) {
    error.value = e.message || 'Connexion échouée.'
  } finally {
    loading.value = false
  }
}
</script>
