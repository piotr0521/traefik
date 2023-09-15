<script setup>
const props = defineProps({
  id: {
    type: String,
    required: true,
  },
  name: {
    type: String,
    required: true,
  },
  interval: {
    type: Object,
    required: true,
  },
  status: {
    type: String,
    default: null,
  },
});
</script>

<template>
  <div>
    <router-link
      :to="{
        name: 'position',
        params: { uuid: id },
        query: { from: interval.start, to: interval.end },
      }"
      class="hover:underline"
    >
      <span class="text-slate-900">
        {{ name }}
      </span>
    </router-link>
    <div v-if="status" class="flex items-center mt-1">
      <span
        class="w-[10px] h-[10px] inline-block mr-2 rounded-full"
        :class="{
          'bg-blue-700': status == 'In Progress',
          'bg-green-500': status == 'Completed',
          'bg-gray-500': status == 'Not Started',
        }"
      >
      </span>
      <span class="text-blue-300 text-sm">
        {{ status }}
      </span>
    </div>
  </div>
</template>

<style lang="scss" scoped></style>
