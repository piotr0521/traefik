<script setup lang="ts">
import { type PropType, watch } from "vue";
import { object, string } from "yup";

import ButtonClose from "@/components/icons/ButtonClose.vue";
import Preloader from "@/components/Preloader.vue";
import useForm from "@/composables/useForm";
import type { Sponsor } from "@/entities/sponsors";
import SponsorService from "@/services/SponsorService";
import { usePopupStore } from "@/stores/popup";
import { toaster } from "@/utils/toaster";
const props = defineProps({
  sponsor: {
    type: Object as PropType<Partial<Sponsor>>,
    required: true,
  },
});
const rules = object().shape({
  name: string().required(),
  privacy: string().required().oneOf(["public", "private"]),
  website: string(),
});
const { state, errors, isSubmitting, meta, update, setTouched } = useForm(
  {
    id: "",
    name: "",
    privacy: "private",
    website: "",
  } as Partial<Sponsor>,
  rules
);
const [id, name, privacy, website] = state;
watch(
  () => props.sponsor,
  (sponsor) => {
    id.value = sponsor.id || "";
    name.value = sponsor.name || "";
    privacy.value = sponsor.privacy || "private";
    website.value = sponsor.website || "";
    setTouched(false);
  }
);
const emit = defineEmits(["updated:sponsor"]);
const { getPopupState, setPopupState } = usePopupStore();

function closePopup() {
  setPopupState({ popupName: "showPopupSponsorEdit", value: false });
}

async function submit() {
  if (isSubmitting.value) return;
  update(new SponsorService(), false).then(() => {
    toaster.success("Sponsor updated");
    emit("updated:sponsor");
    closePopup();
  });
}
</script>

<template>
  <div
    v-if="getPopupState('showPopupSponsorEdit')"
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

      <h2 class="text-indigo-900 font-normal text-xl mb-5">Add sponsor</h2>

      <!-- start content -->
      <form class="grid gap-5" @submit.prevent="submit">
        <div class="grid gap-1">
          <label class="form-label mb-2" for="groshy_sponsor_add_name">Name</label>
          <input
            id="groshy_sponsor_add_name"
            v-model="name"
            :class="{ 'border-red-500': meta.name.touched && errors.name }"
            class="form-input"
            name="groshy_sponsor_add[name]"
            type="text"
          />
          <span v-if="meta.name.touched && errors.name" class="form-error text-sm">
            {{ errors.name }}
          </span>
        </div>

        <div class="grid gap-1">
          <label class="form-check" for="groshy_sponsor_add_privacy">
            <input
              id="groshy_sponsor_add_privacy"
              v-model="privacy"
              type="checkbox"
              name="groshy_sponsor_add[privacy]"
              true-value="public"
              false-value="private"
            />
            <span class="inner">
              <span></span>
            </span>
            <span class="whitespace-nowrap">Public sponsor (other users can see it)</span>
          </label>
          <span v-if="meta.name.touched && errors.privacy" class="form-error text-sm">
            {{ errors.privacy }}
          </span>
        </div>

        <div class="grid gap-1">
          <label class="form-label mb-2" for="groshy_sponsor_add_website">Website</label>
          <input
            id="groshy_sponsor_add_website"
            v-model="website"
            :class="{ 'border-red-500': meta.name.touched && errors.website }"
            class="form-input"
            type="text"
            name="groshy_sponsor_add[website]"
          />
          <span v-if="meta.name.touched && errors.website" class="form-error text-sm">
            {{ errors.website }}
          </span>
        </div>

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
