<script setup>
import { vOnClickOutside } from "@vueuse/components";
import { computed } from "vue";

import { usePopupStore } from "@/stores/popup";

const props = defineProps({
  element: Object,
  actions: {
    type: Array,
    // default() {
    //   return [{ key: "string|unique", label: "string" }];
    // },
  },
});

const { getPopupState, togglePopupState, setPopupState } = usePopupStore();

const emit = defineEmits(["action"]);
const popupName = computed(() => `TableActionsPopup${props.element.id}`);
function showActions() {
  togglePopupState(popupName.value);
}

function onClickOutside(e) {
  setPopupState({ popupName: popupName.value, value: false });
}

function actionHandler(item) {
  emit("action", item.key, props.element["id"]);
  setPopupState({ popupName: popupName.value, value: false });
}
</script>

<template>
  <div v-on-click-outside="onClickOutside" class="relative inline-block align-middle">
    <button type="button" class="block relative" @click="showActions(element)">
      <svg
        xmlns="http://www.w3.org/2000/svg"
        class="h-6 w-6 text-blue-700"
        fill="none"
        viewBox="0 0 24 24"
        stroke="currentColor"
        stroke-width="2"
      >
        <path
          stroke-linecap="round"
          stroke-linejoin="round"
          d="M12 5v.01M12 12v.01M12 19v.01M12 6a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2z"
        />
      </svg>
    </button>
    <div
      v-if="getPopupState(popupName)"
      class="flex flex-col gap-4 text-left whitespace-nowrap z-10 p-5 border border-blue-200 rounded-md absolute top-full right-0 bg-white shadow-[0px_8px_26px_-8px_rgba(203,213,225,0.5)]"
    >
      <button
        v-for="item in actions"
        :key="item.key"
        type="button"
        class="flex items-center text-indigo-900 text-sm"
        @click.stop="actionHandler(item)"
      >
        <div v-if="item.key" class="w-[18px] shrink-0 mr-3">
          <img :src="`/images/icon/${item.key}.svg`" alt="" class="w-full h-auto" />
        </div>
        {{ item.label }}
      </button>
    </div>
  </div>
</template>
