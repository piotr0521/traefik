<script setup>
const props = defineProps({
  iconName: String,
});
</script>

<template>
  <img :src="`/images/asset-type/${iconName}.svg`" alt="icon" />
</template>
