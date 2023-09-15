<script setup>
import { reactive } from "vue";
import { computed, watch } from "vue";

import InputLabelBase from "@/components/input/InputLabelBase.vue";

const props = defineProps({
  accountHolder: {
    type: Object,
    required: false,
    default: null,
  },
  errors: {
    type: Object,
    required: false,
    default: () => ({ name: null }),
  },
});
const emit = defineEmits(["submit"]);
const errors = computed({
  get: () => props.errors,
  set: (value) => emit("update:errors", value),
});
watch(
  () => errors,
  (value) => {
    console.log(errors);
  }
);
const form = reactive({
  name: props.accountHolder?.name ?? "",
});
watch(
  () => props.accountHolder,
  (value) => {
    form.name = value?.name ?? "";
  }
);
const submit = () => {
  emit("submit", form);
};
const cancel = () => emit("cancel");
</script>

<template>
  <form class="grid gap-5" @submit.prevent="submit">
    <InputLabelBase
      v-model:value="form.name"
      v-model:error="errors.name"
      label="Account holder name"
      name="groshy_account_holder_name"
    />

    <div class="flex gap-8 justify-center mt-4">
      <button type="button" class="btn btn-cancel" @click="cancel">Cancel</button>

      <button type="submit" class="btn btn-primary">Submit</button>
    </div>
  </form>
</template>

<style lang="scss" scoped></style>
