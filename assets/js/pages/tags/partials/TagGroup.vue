<script setup lang="ts">
import { computed, type PropType, watch } from "vue";

import ButtonClose from "@/components/icons/ButtonClose.vue";
import InputLabelBase from "@/components/input/InputLabelBase.vue";
import Preloader from "@/components/Preloader.vue";
import useForm from "@/composables/useForm";
import type { TagGroup } from "@/entities/tags";
import TagGroupService from "@/services/TagGroupService";
import { usePopupStore } from "@/stores/popup";

const props = defineProps({
  tagGroup: {
    type: Object as PropType<TagGroup | null>,
  },
});
const emit = defineEmits(["updateData"]);

const { state, errors, isSubmitting, meta, store, update, resetForm } = useForm({
  id: "",
  name: "",
  position: 0,
} as Partial<TagGroup>);
const { setPopupState, getPopupState } = usePopupStore();
const [id, name, position] = state;
const castName = computed({
  get() {
    return name.value as string;
  },
  set(val: string) {
    name.value = val;
  },
});
watch(
  () => props.tagGroup,
  (tagGroup) => {
    if (tagGroup) {
      id.value = props.tagGroup?.id ?? "";
      name.value = tagGroup?.name ?? "";
      position.value = tagGroup?.position ?? 0;
    } else {
      resetForm();
    }
  }
);

function closePopup() {
  setPopupState({ popupName: "showPopupTagGroup", value: false });
}

async function submit() {
  if (isSubmitting.value) return;
  if (props.tagGroup) {
    const data = await update(new TagGroupService(), false);
    if (data) {
      closePopup();
      emit("updateData", { data, existing: true });
    }
  } else {
    const data = await store(new TagGroupService(), false);
    if (data) {
      closePopup();
      emit("updateData", { data });
    }
  }
}
</script>

<template>
  <div
    v-if="getPopupState('showPopupTagGroup')"
    class="fixed top-0 left-0 right-0 bottom-0 min-h-full min-w-full z-10 flex items-center justify-center"
  >
    <!-- start overlay -->
    <div
      class="absolute top-0 left-0 right-0 bottom-0 min-h-full min-w-full z-0"
      style="background: rgba(49, 46, 129, 0.7)"
      @click="closePopup"
    ></div>
    <!-- end overlay -->

    <!-- start popup -->
    <div class="bg-white p-12 relative z-10 max-w-screen-md w-full rounded-md">
      <!-- start button close -->
      <ButtonClose @click="closePopup" />
      <!-- end butotn close -->

      <h2 class="text-indigo-900 font-normal text-xl mb-5">Add tag group</h2>

      <!-- start content -->
      <form class="grid gap-5" @submit.prevent="submit">
        <InputLabelBase
          v-model:value="castName"
          :error="meta.name.touched && errors.name"
          label="Name"
          name="groshy_tag_group_add_name"
        />

        <div class="flex gap-8 justify-center mt-4">
          <button type="button" class="btn btn-cancel" @click="closePopup">Cancel</button>

          <button type="submit" class="btn btn-primary">Submit</button>
        </div>
      </form>
      <!-- end content -->
      <Preloader v-if="isSubmitting" />
    </div>
    <!-- end popup -->
  </div>
</template>
