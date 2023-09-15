<script setup lang="ts">
import { computed } from "vue";

import SelectTags from "@/components/input/SelectTags.vue";
import TextareaLabelBase from "@/components/input/TextareaLabelBase.vue";
const props = defineProps(["form", "errors", "meta"]);
const emit = defineEmits(["update:form", "update:errors"]);
type Form = {
  tags: string[];
  notes: string;
};
const form = computed<Form>(() => props.form);
const setForm = (value: string, key: keyof Form) => {
  emit("update:form", { ...form.value, [key]: value });
};
</script>
<template>
  <div>
    <h1 class="text-xl font-normal text-indigo-900 mb-5">Classification</h1>
    <div class="grid gap-2">
      <SelectTags
        :value="form.tags"
        :error="meta.tags.touched ? errors.tags : ''"
        @update:value="setForm($event, 'tags')"
      />
      <TextareaLabelBase
        :value="form.notes"
        label="Notes"
        name="groshy_cash_add[notes]"
        @update:value="setForm($event, 'notes')"
      />
    </div>
  </div>
</template>

<style scoped></style>
