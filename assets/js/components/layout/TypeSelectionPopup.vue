<script setup>
import { storeToRefs } from "pinia";
import { computed, h, ref, watch } from "vue";
import { useRouter } from "vue-router";

import CashPosition from "@/pages/position-add/partials/CashPosition.vue";
import CreditCardPosition from "@/pages/position-add/partials/CreditCardPosition.vue";
import InvestmentPosition from "@/pages/position-add/partials/InvestmentPosition.vue";
import { useAssetTypeStore } from "@/stores/assetType";
import { useAssetTypeConfigStore } from "@/stores/assetTypeConfig";
import { usePopupStore } from "@/stores/popup";

import ButtonBack from "../icons/ButtonBack.vue";
import ButtonClose from "../icons/ButtonClose.vue";
import Circle from "../icons/Circle.vue";
import Icons from "./SideBarIcons.vue";
const { assets, liabilities } = useAssetTypeStore().tree;
const { selected } = storeToRefs(useAssetTypeStore());
const router = useRouter();
const tree = computed(() => [
  {
    name: "Assets",
    menu: assets,
  },
  {
    name: "Liabilities",
    menu: liabilities,
  },
]);
const { getPopupState, setPopupState } = usePopupStore();
const selectedItem = computed(() => {
  return tree.value
    .flatMap((item) => item.menu)
    .find((item) => item.children?.some((child) => child.slug === selected.value) || item.slug === selected.value);
});
const selectedPosition = computed(() => {
  return selectedItem.value?.children
    ? selectedItem.value.children.find((item) => item.slug === selected.value)
    : selectedItem.value;
});
const selectedComponent = ref(null);
watch(
  () => selectedPosition.value,
  (value) => {
    const config = useAssetTypeConfigStore().getBySlug(value?.slug);
    selectedComponent.value = config?.position;
  }
);
const components = {
  CreditCardPosition: h(CreditCardPosition),
  CashPosition: h(CashPosition),
  InvestmentPosition: h(InvestmentPosition, { slug: computed(() => selectedPosition.value?.slug) }),
};

const position = ref(null);
const resolvedComponent = computed(() => {
  return components[selectedComponent.value] ?? null;
});
const closePopup = (uuid) => {
  if (uuid) {
    router.push({
      name: "position",
      params: {
        uuid,
      },
    });
  }
  setPopupState({ popupName: "showInvestmentsPopup", value: false });
  selected.value = "";
  position.value = null;
};
const selectItem = ({ slug }) => {
  selected.value = slug ?? "";
  if (!selected.value) {
    position.value = null;
  }
};
watch(
  () => selectedItem.value,
  (item) => {
    if (item?.slug && !item.children) {
      position.value = item.slug;
    }
  }
);
</script>

<template>
  <div
    v-if="getPopupState('showInvestmentsPopup')"
    class="fixed top-0 left-0 right-0 bottom-0 min-h-full min-w-full z-10 flex items-center justify-center"
  >
    <!-- start overlay -->
    <div
      class="absolute top-0 left-0 right-0 bottom-0 min-h-full min-w-full z-0"
      style="background: rgba(49, 46, 129, 0.7)"
      @click="closePopup()"
    ></div>
    <!-- end overlay -->

    <!-- start popup -->
    <div
      id="asset-modal"
      class="bg-white relative z-10 max-w-screen-md w-full rounded-md max-h-[750px] h-full flex flex-col"
    >
      <template v-if="!resolvedComponent">
        <div class="p-12">
          <ButtonClose @click="closePopup()" />
          <!-- header -->

          <h2 class="text-indigo-900 font-normal text-3xl mb-7">Add Investment</h2>

          <!-- start content -->
          <div class="grid grid-cols-3 gap-4 h-full">
            <!-- Start Menu left -->
            <div class="space-y-6 overflow-y-auto h-full">
              <template v-for="item in tree" :key="item.name">
                <div class="space-y-3">
                  <h3 class="text-indgigo-900 text-xl">{{ item.name }}</h3>

                  <!-- start item -->
                  <div
                    v-for="asset in item.menu"
                    :key="asset.id"
                    class="transition duration-150 ease-out md:ease-in border-2 border-blue-200 rounded-md hover:border-blue-50 hover:bg-blue-50 text-indigo-900"
                    :class="`${
                      selectedItem?.id === asset.id
                        ? 'bg-blue-700 border-blue-700 hover:border-blue-700 hover:bg-blue-700 text-white'
                        : ''
                    }`"
                  >
                    <button type="button" class="flex items-center p-2 w-full" @click="selectItem(asset)">
                      <div class="block w-7">
                        <Icons :icon-name="asset.slug" />
                      </div>

                      <span class="mr-auto whitespace-nowrap">
                        {{ asset.name }}
                      </span>
                    </button>
                    <!-- end if haven't child elements -->
                  </div>
                  <!-- start item -->
                </div>
              </template>
            </div>
            <!-- end Menu left -->
            <!-- Start Component right -->
            <div class="col-span-2 overflow-y-auto h-full">
              <div class="relative text-center text-blue-200 top-1/4 -translate-y-1/4">
                <div v-if="!selectedItem">
                  <img src="/images/dashboard/chart.svg" alt="" class="w-2/3 mx-auto" />

                  <p class="w-1/2 mx-auto -mt-8">Select a class to add new asset/liability</p>
                </div>
                <div v-else>
                  <div>
                    <Icons :icon-name="selectedItem.slug" class="w-32 mx-auto mb-3" />
                    <h1 class="text-center text-indigo-900 text-xl font-normal mb-8">{{ selectedItem.name }}</h1>

                    <p class="max-w-lg mx-auto mb-8">
                      <!-- TODO liability/asset description -->
                      Investments in underlying assets are made trough private equity, Real estate, hedge funds, venture
                      capital, or debt fund
                    </p>
                  </div>

                  <div class="space-y-3">
                    <div v-for="item in selectedItem.children" :key="item.id" class="popup-selected-subitem">
                      <button type="button" class="flex items-center p-2 w-full" @click="selectItem(item)">
                        <span>
                          <Circle />
                        </span>

                        <span class="ml-3">
                          {{ item.name }}
                        </span>
                      </button>
                    </div>
                  </div>
                </div>
              </div>
            </div>
            <!-- end Component right -->
          </div>
          <!-- end content -->
        </div>
      </template>
      <template v-else>
        <!-- Header -->
        <div class="relative flex items-center justify-center py-6 border-b border-slate-300">
          <ButtonBack @click="selectItem({})" />

          <Icons :icon-name="selectedItem.slug" class="mr-3 -ml-3 w-8" />
          <h2 class="text-indigo-900 font-normal text-lg">
            Create new <span class="lowercase">{{ selectedPosition.name }}</span> account
          </h2>
        </div>
        <ButtonClose @click="closePopup()" />
        <component :is="resolvedComponent" @close="closePopup" />
      </template>
    </div>
    <!-- end popup -->
  </div>
</template>
<style scoped>
.popup-selected-subitem {
  @apply transition duration-150 ease-out md:ease-in rounded-md bg-[#FBFCFE] text-indigo-900 hover:bg-blue-700 hover:text-white max-w-xs mx-auto;
}
.popup-selected-subitem > a > span:first-child {
  @apply text-blue-300;
}
.popup-selected-subitem:hover > a > span:first-child {
  @apply text-white;
}
</style>
