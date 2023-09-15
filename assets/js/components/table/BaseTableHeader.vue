<script setup>
const props = defineProps({
  header: Array,
});
</script>

<template>
  <!-- start header -->
  <thead>
    <tr>
      <th v-for="(item, index) in header" :key="index" class="table-dashboard__th">
        {{ item }}
      </th>
    </tr>
  </thead>
  <!-- end header -->
</template>
