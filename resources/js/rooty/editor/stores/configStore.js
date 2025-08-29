// stores/configStore.js

import { defineStore } from 'pinia'
import rawConfig from '@editor/bootstrap/config.js'

export const useConfigStore = defineStore('configStore', {
  state: () => ({
    ajaxUrl: rawConfig.ajaxUrl || '/wp-admin/admin-ajax.php',
    nonce: rawConfig.nonce || '',
  }),
})
