<script setup>
import { DatePicker } from "v-calendar";
import { computed } from "vue";

const pickerConfig = {
  masks: {
    input: "MMM D, YYYY",
  },
  modelConfig: {
    type: "string",
    mask: "YYYY-MM-DD",
  },
  selectAttribute: {
    highlight: {
      class: "bg-blue-700",
      contentClass: "text-white",
      base: {
        class: "bg-blue-50",
      },
      start: {
        class: "bg-blue-700",
        contentClass: "text-white",
      },
      end: {
        class: "bg-blue-700",
        contentClass: "text-white",
      },
    },
  },
};

const props = defineProps({
  label: {
    type: String,
  },
  name: {
    type: String,
  },
  value: {
    default: new Date(),
  },
  error: {
    default: null,
  },
});

const value = computed({
  get() {
    return props.value;
  },
  set(newValue) {
    emit("update:value", newValue);
  },
});
const emit = defineEmits(["update:value", "update:error"]);
</script>
<template>
  <div class="grid gap-1">
    <label class="form-label mb-2" :for="name">
      {{ label }}
    </label>
    <DatePicker
      v-model="value"
      :model-config="pickerConfig.modelConfig"
      :select-attribute="pickerConfig.selectAttribute"
      :drag-attribute="pickerConfig.selectAttribute"
      :masks="pickerConfig.masks"
      :max-date="new Date()"
    >
      <template #default="{ inputValue, inputEvents }">
        <input
          :id="name"
          autocomplete="off"
          :class="{ 'border-red-500': error }"
          :name="name"
          class="form-input"
          :value="inputValue"
          @focus="$emit('update:error', null)"
          v-on="inputEvents"
        />
      </template>
    </DatePicker>
    <span v-if="error" class="form-error text-sm">
      {{ error }}
    </span>
  </div>
</template>
