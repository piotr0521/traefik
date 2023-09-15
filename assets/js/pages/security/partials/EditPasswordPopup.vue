<script setup>
import ButtonClose from "@/components/icons/ButtonClose.vue";
import PasswordResetForm from "@/pages/password-reset/partials/PasswordResetForm.vue";
import { usePopupStore } from "@/stores/popup";
const emit = defineEmits(["update:accountHolder"]);

const { getPopupState, setPopupState } = usePopupStore();

function closePopup() {
  setPopupState({ popupName: "showPopupPasswordEdit", value: false });
}
</script>

<template>
  <div
    v-if="getPopupState('showPopupPasswordEdit')"
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

      <h2 class="text-indigo-900 font-normal text-xl mb-5">Change Password</h2>

      <!-- start content -->
      <PasswordResetForm @submit="closePopup" @cancel="closePopup" />
    </div>
    <!-- end popup -->
  </div>
</template>
