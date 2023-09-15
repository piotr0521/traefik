<script setup>
const props = defineProps({
  tags: {
    type: Array,
    required: true,
  },
});
</script>
<template>
  <span
    v-for="tag in tags"
    :key="tag.id"
    class="inline-flex text-white text-xs whitespace-nowrap px-2 py-1 rounded-3xl m-1"
    :style="'background-color:' + tag.color"
  >
    {{ tag.name }}
  </span>
</template>
