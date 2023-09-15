<script setup lang="ts">
import { computed, type PropType } from "vue";
const props = defineProps({
  autocomplete: {
    type: String,
    default: null,
  },
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
    type: [String, Boolean] as PropType<string | null | false>,
    default: null,
  },
  name: {
    type: String,
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

    <input
      :id="name"
      v-model="value"
      :class="{ 'border-red-500': error }"
      class="form-input"
      :type="type"
      :name="name"
      :placeholder="placeholder"
      :autocomplete="autocomplete"
      @focus="$emit('update:error', null)"
      @scroll.prevent
    />

    <span v-if="error" class="form-error text-sm">
      {{ error }}
    </span>
  </div>
</template>
