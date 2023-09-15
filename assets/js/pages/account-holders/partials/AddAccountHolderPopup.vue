<script setup>
import axios from "axios";
import { reactive, ref } from "vue";

import ButtonClose from "@/components/icons/ButtonClose.vue";
import Preloader from "@/components/Preloader.vue";
import { usePopupStore } from "@/stores/popup";
import { toaster } from "@/utils/toaster";

import AccountHolderForm from "./AccountHolderForm.vue";
const emit = defineEmits(["update:accountHolder"]);

const { getPopupState, setPopupState } = usePopupStore();

const isFetching = ref(false);

function closePopup() {
  setPopupState({ popupName: "showPopupAccountHolderAdd", value: false });
}
const errors = reactive({
  name: null,
});
async function submit(form) {
  isFetching.value = true;
  try {
    let { data } = await axios.post(`/api/account_holders`, form);
    closePopup();
    toaster.success("New account holder added");
    emit("update:accountHolder", data);
  } catch (err) {
    err.response.data.violations.forEach((error) => {
      errors[error.propertyPath] = error.message;
    });
  } finally {
    isFetching.value = false;
  }
}
</script>

<template>
  <div
    v-if="getPopupState('showPopupAccountHolderAdd')"
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

      <h2 class="text-indigo-900 font-normal text-xl mb-5">Add new account holder</h2>

      <!-- start content -->
      <AccountHolderForm v-model:errors="errors" @submit="submit" @cancel="closePopup" />
      <!-- end content -->
      <Preloader v-if="isFetching" />
    </div>
    <!-- end popup -->
  </div>
</template>
