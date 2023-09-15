<script setup>
import { ref, watch } from "vue";

const props = defineProps({
  label: {
    type: String,
  },
  value: {
    type: String,
  },
  name: {
    type: String,
  },
});

const emit = defineEmits(["update:value"]);
const privacy = ref(props.value);

watch(privacy, (newValue) => {
  emit("update:value", newValue);
});
</script>

<template>
  <div class="grid gap-1">
    <label class="form-check" :for="name">
      <input :id="name" v-model="privacy" type="checkbox" :name="name" true-value="public" false-value="private" />
      <span class="inner">
        <span></span>
      </span>
      <span class="whitespace-nowrap">
        {{ label }}
      </span>
    </label>
  </div>
</template>
