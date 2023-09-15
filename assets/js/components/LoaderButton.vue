<template>
  <button :disabled="loading || disabled">
    <div v-show="!loading">
      <slot></slot>
    </div>
    <div v-show="loading" class="flex items-center justify-center"><Loading class="mr-2 -ml-1" /> Loading...</div>
  </button>
</template>

<script setup lang="ts">
import Loading from "@/components/icons/Loading.vue";
const props = defineProps({
  loading: {
    type: Boolean,
    default: false,
  },
  disabled: {
    type: Boolean,
    default: false,
  },
});
</script>

<style scoped></style>
