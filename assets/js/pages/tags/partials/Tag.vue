<script setup lang="ts">
import { CheckIcon } from "@heroicons/vue/solid";
import { computed, type PropType, watch } from "vue";

import ButtonClose from "@/components/icons/ButtonClose.vue";
import InputLabelBase from "@/components/input/InputLabelBase.vue";
import LoaderButton from "@/components/LoaderButton.vue";
import Preloader from "@/components/Preloader.vue";
import useForm from "@/composables/useForm";
import type { Tag } from "@/entities/tags";
import TagService from "@/services/TagService";
import { usePopupStore } from "@/stores/popup";
const props = defineProps({
  tag: {
    type: Object as PropType<Tag | null>,
  },
  tagGroupId: {
    type: [String, Number],
  },
});

const emit = defineEmits(["updateData"]);
const { state, isSubmitting, errors, meta, store, update, resetForm } = useForm({
  id: "",
  name: "",
  color: "#a8a29e",
  position: 0,
  tagGroup: "",
} as Partial<Tag>);
const colors = [
  "#a8a29e",
  "#f87171",
  "#fb923c",
  "#fbbf24",
  "#a3e635",
  "#4ade80",
  "#34d399",
  "#2dd4bf",
  "#38bdf8",
  "#60f5fa",
  "#818cf8",
  "#a78bfa",
  "#c084fc",
  "#e879f9",
];
const [id, name, color, position, tagGroup] = state;
const castedName = computed({
  get() {
    return name.value as string;
  },
  set(val: string) {
    name.value = val;
  },
});
watch(
  () => props.tag,
  (tag) => {
    if (tag) {
      id.value = tag?.id ?? "";
      name.value = tag?.name ?? "";
      color.value = tag?.color ?? colors[0];
      position.value = tag?.position ?? 0;
    } else {
      resetForm();
    }
    tagGroup.value = props.tagGroupId ?? "";
  }
);

const { setPopupState, getPopupState } = usePopupStore();

function closePopup() {
  setPopupState({ popupName: "showPopupTag", value: false });
}
async function submit() {
  if (isSubmitting.value) return;
  if (props.tag) {
    update(new TagService(), false).then((data) => {
      closePopup();
      emit("updateData", { data, existing: true });
    });
  } else {
    store(new TagService()).then((data) => {
      closePopup();
      emit("updateData", { data });
    });
  }
}
</script>

<template>
  <div
    v-if="getPopupState('showPopupTag')"
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

      <h2 class="text-indigo-900 font-normal text-xl mb-5">Add tag</h2>

      <!-- start content -->
      <form class="grid gap-5" @submit.prevent="submit">
        <InputLabelBase
          v-model:value="castedName"
          :error="meta.name.touched && errors.name"
          label="Name"
          name="groshy_tag_group_add_name"
        />

        <div class="grid gap-1">
          <label class="form-label mb-2">Color</label>

          <div class="flex gap-3">
            <label
              v-for="colorHex in colors"
              :key="colorHex"
              :style="`background: ${colorHex}`"
              class="flex items-center justify-center w-9 h-9 rounded"
            >
              <input v-model="color" type="radio" :value="colorHex" class="hidden" />
              <CheckIcon v-if="color == colorHex" class="text-white w-3 h-3" />
            </label>
          </div>

          <span v-if="meta.color.touched && errors.color" class="form-error text-sm">
            {{ errors.color }}
          </span>
        </div>

        <div class="flex gap-8 justify-center mt-4">
          <button type="button" class="btn btn-cancel" @click="closePopup">Cancel</button>
          <loader-button type="submit" class="btn btn-primary" :loading="isSubmitting">Submit</loader-button>
        </div>
      </form>
      <!-- end content -->
      <Preloader v-if="isSubmitting" />
    </div>
    <!-- end popup -->
  </div>
</template>
