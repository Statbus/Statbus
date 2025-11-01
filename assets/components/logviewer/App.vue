<template>
<LogViewer :url="loadUri" />
</template>

<script setup>
import LogViewer from './components/logviewer.vue';

const props = defineProps({
  loadUri: String
});
</script>