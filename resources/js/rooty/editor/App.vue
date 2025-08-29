<template>
  <!-- <AppOverlay
    :visible="overlay.visible"
    :title="overlay.title"
    :message="overlay.message"
    :action="overlay.action"
  >
    <AuthForm v-if="overlay.type === 'login'" />
  </AppOverlay> -->

  <!-- <BaseLayout v-if="!overlay.fullscreen"> -->
  <BaseLayout />
</template>

<script setup>
import BaseLayout from '@editor/components/layouts/BaseLayout.vue'
// import { useHeartbeat } from '@editor/composables/heartbeat/useHeartbeat.js'
// import { useOverlayStore } from '@editor/stores/overlayStore.js'
// import AppOverlay from '@editor/components/overlays/AppOverlay.vue'
// import AuthForm from '@editor/components/auth/AuthForm.vue'

// const overlay = useOverlayStore()
// const { pause, resume } = useHeartbeat()
</script>
