<script setup lang="ts">
import ButtonClose from "@/components/icons/ButtonClose.vue";
import InputLabelBase from "@/components/input/InputLabelBase.vue";
import Preloader from "@/components/Preloader.vue";
import useForm from "@/composables/useForm";
import type { Institution } from "@/entities/institutions";
import InstitutionService from "@/services/InstitutionService";
import { usePopupStore } from "@/stores/popup.js";
import { toaster } from "@/utils/toaster";

const { state, errors, isSubmitting, meta, store } = useForm({
  name: "",
  website: "",
} as Partial<Institution>);
const [name, website] = state;
const { setPopupState, getPopupState } = usePopupStore();
const emit = defineEmits(["added:institution"]);

function closePopup() {
  setPopupState({ popupName: "showPopupInstitutionAdd", value: false });
}

async function submit() {
  if (isSubmitting.value) return;
  const res = await store(new InstitutionService());
  if (res) {
    toaster.success("New institution added");
    emit("added:institution");
    closePopup();
  }
}
</script>

<template>
  <div
    v-if="getPopupState('showPopupInstitutionAdd')"
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

      <h2 class="text-indigo-900 font-normal text-xl mb-5">Add institution</h2>

      <!-- start content -->
      <form class="grid gap-5" @submit.prevent="submit">
        <InputLabelBase
          v-model:value="name"
          :error="meta.name.touched && errors.name"
          label="Name"
          name="groshy_institution_add_name"
        />

        <InputLabelBase
          v-model:value="website"
          :error="meta.website.touched && errors.website"
          label="Website"
          name="groshy_institution_add_website"
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
