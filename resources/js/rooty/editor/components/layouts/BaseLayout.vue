<template>
  <div class="layout-root">
    <div class="layout-grid">
      <EditorHeader />
      <EditorSidebarAux />
      <EditorSidebar />
      <EditorMainArea />
    </div>
  </div>
</template>

<script setup>
import EditorHeader from '@editor/components/layouts/EditorHeader.vue'
import EditorSidebarAux from '@editor/components/layouts/EditorSidebarAux.vue'
import EditorSidebar from '@editor/components/layouts/EditorSidebar.vue'
import EditorMainArea from '@editor/components/layouts/EditorMainArea.vue'
</script>
