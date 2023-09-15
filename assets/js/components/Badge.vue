<script setup lang="ts">
import { computed, withDefaults } from "vue";

const types = {
  success: "bg-green-100 text-green-400",
  danger: "bg-red-100 text-red-400",
  info: "bg-blue-100 text-blue-400",
  warning: "bg-yellow-100 text-yellow-400",
};
type Props = {
  type: "success" | "danger" | "info" | "warning";
};
const props = withDefaults(defineProps<Props>(), {
  type: "info",
});
// const type = ''
const type = computed(() => types[props.type]);
</script>

<template>
  <span class="text-xs font-medium mr-2 px-2.5 py-1 rounded uppercase" :class="type">
    <slot />
  </span>
</template>

<style></style>
