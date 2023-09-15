<script setup lang="ts">
import { computed } from "vue";

const props = defineProps({
  steps: {
    type: Number,
    required: true,
  },
  modelValue: {
    type: Number,
    required: true,
  },
});
const emit = defineEmits(["update:modelValue"]);
const value = computed({
  get: () => props.modelValue,
  set: (newValue) => emit("update:modelValue", newValue),
});
</script>

<template>
  <div class="flex items-center">
    <span class="text-indigo-900 mr-4"> {{ value }} / {{ steps }} </span>
    <div class="flex space-x-1 items-center">
      <button
        v-for="step in steps"
        :key="step"
        class="w-4 h-4 border-2 border-blue-700 hover:bg-blue-500 p-1 rounded-full"
        :class="{
          'bg-blue-700': step == value,
        }"
        @click="value = step"
      />
    </div>
  </div>
</template>

<style scoped></style>
