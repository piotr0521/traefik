<script setup lang="ts">
import { useAlertStore } from "@/stores/alert";

const alertStore = useAlertStore();

function getMessageClass(type: string) {
  switch (type) {
    case "success":
      return "is-success";
    case "warning":
      return "is-warning";
    case "error":
      return "is-error";
    default:
      return "is-success";
  }
}
</script>

<template>
  <transition-group tag="div" name="fade-up" class="px-8">
    <div
      v-for="(message, index) in alertStore.getMessages"
      :key="index"
      class="alert"
      :class="getMessageClass(message.type)"
      role="alert"
    >
      <div class="font-normal pr-8">{{ message.message }}</div>
      <button
        type="button"
        class="ml-auto -mx-1.5 -my-1.5 rounded-lg p-1.5 inline-flex h-8 w-8"
        aria-label="Close"
        @click="alertStore.removeAlert(index)"
      >
        <span class="sr-only">Close</span>
        <svg
          aria-hidden="true"
          class="w-5 h-5"
          fill="currentColor"
          viewBox="0 0 20 20"
          xmlns="http://www.w3.org/2000/svg"
        >
          <path
            fill-rule="evenodd"
            d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z"
            clip-rule="evenodd"
          ></path>
        </svg>
      </button>
    </div>
  </transition-group>
</template>

<style scoped>
.alert {
  @apply flex items-center py-3 pl-5 pr-4 mb-4 rounded-md;
}

.alert.is-success {
  background: #bdebd3;
  color: #81b697;
}

.alert.is-warning {
  background: #f4f2d4;
  color: #afaf99;
}

.alert.is-error {
  background: #fbc5c6;
  color: #c38989;
}

/* declare transition */
.fade-up-move,
.fade-up-enter-active,
.fade-up-leave-active {
  transition: all 0.5s cubic-bezier(0.55, 0, 0.1, 1);
}

/* declare enter from and leave to state */
.fade-up-enter-from,
.fade-up-leave-to {
  opacity: 0;
  transform: translateY(30px);
}
</style>
