<script setup>
const props = defineProps(["label", "value", "name"]);
defineEmits(["update:value"]);
</script>
<template>
  <div class="grid gap-1">
    <label class="form-label mb-2" :for="name">Notes</label>
    <textarea
      :id="name"
      class="form-text"
      :value="value"
      :name="name"
      rows="7"
      @input="$emit('update:value', $event.target.value)"
    >
    </textarea>
  </div>
</template>
