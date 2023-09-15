<script setup lang="ts">
import { isArray } from "lodash";
import { useRoute } from "vue-router";

import { useAssetTypeStore } from "@/stores/assetType";
import { usePopupStore } from "@/stores/popup";

const { togglePopupState } = usePopupStore();
const route = useRoute();
const handleAddInvestment = () => {
  useAssetTypeStore().setSelected(isArray(route.params.slug) ? route.params.slug[0] : route.params.slug);
  togglePopupState("showInvestmentsPopup");
};
</script>

<template>
  <div class="bg-blue-700 text-center py-11 px-4 relative overflow-hidden z-0 w-full xl:max-w-screen-xl xl:mx-auto">
    <h2 class="text-white text-3xl lg:text-5xl px-11 font-medium mb-10">You do not have any investments</h2>
    <button class="bg-gray-50 rounded-md flex py-3 px-5 mx-auto text-blue-700 text-xl" @click="handleAddInvestment">
      Add investment
    </button>

    <img
      class="absolute left-0 top-2/3 -translate-y-1/2 -z-10 hidden lg:block"
      src="/images/dashboard/dotted-left.svg"
      alt="icon"
    />
    <img class="absolute right-7 top-1/2 -z-10 hidden lg:block" src="/images/dashboard/dotted-right.svg" alt="icon" />
  </div>
</template>
