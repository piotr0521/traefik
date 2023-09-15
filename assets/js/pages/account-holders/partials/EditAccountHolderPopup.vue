<script setup>
import axios from "axios";
import { reactive, ref, watch } from "vue";

import ButtonClose from "@/components/icons/ButtonClose.vue";
import Preloader from "@/components/Preloader.vue";
import { usePopupStore } from "@/stores/popup";
import { toaster } from "@/utils/toaster";

import AccountHolderForm from "./AccountHolderForm.vue";
const emit = defineEmits(["update:accountHolder"]);

const { getPopupState, setPopupState } = usePopupStore();

const props = defineProps({
  accountHolder: {
    type: Object,
    default: null,
  },
});
const accountHolder = ref(null);
const isFetching = ref(false);
const errors = reactive({ name: null });
watch(
  () => props.accountHolder,
  (value) => {
    accountHolder.value = value;
  }
);
function closePopup() {
  setPopupState({ popupName: "showPopupAccountHolderEdit", value: false });
}

async function submit(form) {
  isFetching.value = true;
  try {
    let { data } = await axios.patch(`/api/account_holders/${props.accountHolder.id}`, form, {
      headers: { "Content-Type": "application/merge-patch+json" },
    });
    closePopup();
    toaster.success("Account holder updated");
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
    v-if="getPopupState('showPopupAccountHolderEdit')"
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

      <h2 class="text-indigo-900 font-normal text-xl mb-5">Edit account holder</h2>

      <!-- start content -->
      <AccountHolderForm
        v-model:errors="errors"
        :account-holder="accountHolder"
        @submit="submit"
        @cancel="closePopup"
      />
      <!-- end content -->
      <Preloader v-if="isFetching" />
    </div>
    <!-- end popup -->
  </div>
</template>
