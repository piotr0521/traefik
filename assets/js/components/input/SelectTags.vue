<script setup lang="ts">
import { computed } from "vue";

import { useTagStore } from "@/stores/tags";

import SelectLabelBase from "./SelectLabelBase.vue";

const props = defineProps(["value", "error", "selected"]);
const emit = defineEmits(["update:value", "update:error"]);
type TagSelectOption = {
  label: string;
  code: string;
  color: string;
};
const tags = computed<TagSelectOption[]>(() =>
  useTagStore().tags.map((tag) => ({
    label: tag.name,
    code: tag["@id"],
    color: tag.color,
  }))
);
const selected = computed({
  get() {
    return props.value;
  },
  set(newValue) {
    emit("update:value", newValue);
    emit("update:error", null);
  },
});
</script>

<template>
  <SelectLabelBase
    v-model:value="selected"
    v-model:error="error"
    label="Tags"
    placeholder="Select Tags"
    :multiple="true"
    :searchable="false"
    :options="tags"
    hide-selected
  >
    <template #selected-option-container="{ option, deselect }">
      <div class="flex items-center mr-1">
        <div
          :class="`flex items-center relative rounded-full text-white px-3 text-xs`"
          :style="`background-color: ${option.color};`"
        >
          <span>{{ option.label }}</span>
          <button
            type="button"
            class="relative z-50 border-0 ml-3 p-1 text-lg"
            @click="deselect(option)"
            @mousedown.stop
          >
            &times;
          </button>
        </div>
      </div>
    </template>
  </SelectLabelBase>
</template>
