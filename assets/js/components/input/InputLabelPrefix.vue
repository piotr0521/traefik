<script setup lang="ts">
import { computed } from "vue";

const props = defineProps({
  label: {
    type: String,
  },
  placeholder: {
    type: String,
    default: null,
  },
  value: {
    type: [String, Number],
    default: null,
  },
  error: {
    type: String,
    default: null,
  },
  unit: {
    type: String,
  },
  name: {
    type: String,
  },
  readonly: {
    type: Boolean,
    default: false,
  },
  type: {
    type: String,
    default: "text",
  },
  numberFormat: {
    type: String as () => "integer" | "decimal" | "string",
    default: "string",
  },
});
const emit = defineEmits(["update:value", "update:error"]);
const value = computed({
  get() {
    return props.type === "number" ? Number(props.value) : String(props.value);
  },
  set(val: string | number) {
    val = String(val);
    if (props.type === "number" && props.numberFormat === "integer") {
      val = parseInt(val);
    } else if (props.type === "number" && props.numberFormat === "decimal") {
      val = parseFloat(val);
    }
    emit("update:value", val);
  },
});
</script>

<template>
  <div class="grid gap-1">
    <label class="form-label mb-2" :for="name">
      {{ label }}
    </label>
    <div class="flex">
      <span
        :class="{ 'border-red-500': error }"
        class="flex bg-slate-50 items-center px-3 text-blue-300 rounded-md border-slate-300 border border-r-0 rounded-r-none"
      >
        {{ unit ?? "$" }}
      </span>
      <input
        :id="name"
        v-model="value"
        :class="{ 'border-red-500': error }"
        class="form-input rounded-l-none"
        :type="type"
        :name="name"
        :readonly="readonly"
        @focus="$emit('update:error', null)"
        @scroll.prevent
      />
    </div>

    <span v-if="error" class="form-error text-sm">
      {{ error }}
    </span>
  </div>
</template>
