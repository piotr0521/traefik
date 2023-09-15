<script setup>
defineProps({
  label: {
    type: String,
  },
  value: {
    type: Boolean,
  },
  name: {
    type: String,
  },
});
defineEmits(["update:value"]);
</script>

<template>
  <div class="grid">
    <label :for="name" class="flex items-center text-slate-900">
      <input
        :id="name"
        type="checkbox"
        :name="name"
        :checked="value"
        class="border-slate-300 rounded-md w-6 h-6"
        @change="$emit('update:value', $event.target.checked)"
      />

      <span class="ml-4">{{ label }}</span>
    </label>
  </div>
</template>
