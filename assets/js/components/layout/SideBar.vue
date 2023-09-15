<script setup>
import { PlusIcon } from "@heroicons/vue/solid";
import axios from "axios";
import { computed, onMounted, reactive } from "vue";
import { ref } from "vue";
import { useRoute } from "vue-router";

import { useAssetTypeStore } from "@/stores/assetType";
import { useDateIntervalStore } from "@/stores/interval";
import { usePopupStore } from "@/stores/popup";

import Icons from "./SideBarIcons.vue";
const state = reactive({
  isCollapse: false,
});
const route = useRoute();
const toggleSideBar = () => {
  state.isCollapse = !state.isCollapse;
  state.openedItem = null;
};

const onResizeWindow = () => {
  let windowWidth = window.innerWidth;
  if (windowWidth <= 861) {
    state.isCollapse = true;
  }

  window.addEventListener("resize", () => {
    windowWidth = window.innerWidth;

    if (windowWidth <= 861) {
      state.isCollapse = true;
      state.openedItem = null;
    }
  });
};
onResizeWindow();

const tree = computed(() => useAssetTypeStore().tree);
const { togglePopupState } = usePopupStore();
const interval = computed(() => useDateIntervalStore().interval);

const token = ref(null);

const loadToken = async () => {
  try {
    const { data } = await axios.post(`/api/tokens`, []);
    token.value = data.link;
  } catch (error) {
    console.log(error.message);
  }
};
const collapseAsset = (item) => {
  return item.children?.some((child) => route.params.slug === child.slug) || route.params.slug === item.slug;
};
const handleItem = (id) => {
  state.openedItem = state.openedItem == id ? null : id;
};
onMounted(() => {
  loadToken();
});
</script>

<template>
  <div class="border border-blue-200 w-full max-w-xs" :class="{ 'w-auto': state.isCollapse }">
    <!-- start header -->
    <div class="grid gap-8 my-2 mx-6">
      <!-- start logo -->
      <div class="flex justify-between">
        <div class="ml-2">
          <img :class="{ hidden: state.isCollapse }" src="/images/logo.svg" alt="logo" />
          <img :class="{ hidden: !state.isCollapse }" src="/images/logo-collapse.svg" alt="logo" />
        </div>

        <button class="ml-3 shrink-0" @click="toggleSideBar">
          <img :class="{ hidden: !state.isCollapse }" src="/images/sidebar/expand.svg" alt="icon" />
          <img :class="{ hidden: state.isCollapse }" src="/images/sidebar/collapse.svg" alt="icon" />
        </button>
      </div>
      <!-- end logo -->

      <!-- start dashboard btn -->
      <router-link :to="{ name: 'dashboard' }" class="transition ease-out btn-sidebar" @click="handleItem(null)">
        <span class="w-6 h-6 inline-flex" :class="[state.isCollapse ? 'justify-center mx-auto' : 'justify-end']">
          <img src="/images/sidebar/dashboard.svg" alt="icon" :class="{ 'mx-auto': state.isCollapse }" />
        </span>

        <span :class="{ hidden: state.isCollapse }" class="ml-3"> Dashboard </span>
      </router-link>
      <!-- end dashboard btn -->
    </div>
    <!-- end header -->

    <!-- start tree -->
    <div v-for="(menu, key) in tree" :key="key" class="grid gap-2 mb-5">
      <div
        class="bg-blue-50 text-indigo-900 px-6 py-1 text-sm justify-self-stretch capitalize"
        :class="{ 'text-center': state.isCollapse }"
      >
        {{ key }}
      </div>

      <!-- list from api -->
      <div class="px-6 grid gap-0.5 text-sm">
        <div v-for="item in menu" :key="item.id" :class="{ 'mx-auto': state.isCollapse }">
          <router-link
            :to="{
              name: 'assetType',
              params: { slug: item.slug },
              query: { from: interval.start, to: interval.end },
            }"
          >
            <div
              class="cursor-pointer flex justify-between items-center w-full rounded-md transition ease-out relative py-3 px-4"
              :class="collapseAsset(item) ? ' text-white bg-blue-700' : 'text-slate-900 px-4 py-2.5 hover:bg-blue-50'"
              @click="handleItem(item.id)"
            >
              <div class="flex items-center whitespace-nowrap" :class="[state.isCollapse ? 'mx-auto' : 'mr-3']">
                <span class="w-6 h-6 inline-flex" :class="[state.isCollapse ? 'justify-center' : 'justify-end']">
                  <Icons :icon-name="item.slug" />
                </span>

                <span class="ml-3 mr-1" :class="{ hidden: state.isCollapse }">
                  {{ item.name }}
                </span>
              </div>
            </div>
          </router-link>
        </div>
      </div>

      <!-- start add assets -->
      <div class="px-10 mt-2 mb-5">
        <button
          class="flex items-center text-sm whitespace-nowrap"
          :class="{ 'mx-auto': state.isCollapse }"
          @click="togglePopupState('showInvestmentsPopup')"
        >
          <div class="bg-blue p-1 bg-blue-700 rounded-md">
            <PlusIcon class="w-4 h-4 text-white"></PlusIcon>
          </div>
          <span :class="{ hidden: state.isCollapse }" class="text-blue-700 underline ml-3"> Add Asset </span>
        </button>
      </div>
      <!-- end add assets -->
    </div>
    <!-- end tree -->
    <div class="grid gap-2 mb-5">
      <div
        class="bg-blue-50 text-indigo-900 px-6 py-1 text-sm justify-self-stretch"
        :class="{ 'text-center': state.isCollapse }"
      >
        User
      </div>

      <!-- start Other links -->
      <!-- Needs a revision on where it should go -->
      <div class="px-6 grid gap-0.5">
        <div :class="{ 'mx-auto': state.isCollapse }">
          <router-link
            :to="{ name: 'account-security' }"
            class="transition ease-out btn-sidebar"
            @click="handleItem(null)"
          >
            <span class="w-6 inline-flex" :class="[state.isCollapse ? 'justify-center' : 'justify-end']">
              <img src="/images/sidebar/security.svg" alt="icon" />
            </span>

            <span class="ml-3" :class="{ hidden: state.isCollapse }">Account Security</span>
          </router-link>
        </div>
        <div :class="{ 'mx-auto': state.isCollapse }">
          <router-link :to="{ name: 'billing' }" class="transition ease-out btn-sidebar" @click="handleItem(null)">
            <span class="w-6 inline-flex" :class="[state.isCollapse ? 'justify-center' : 'justify-end']">
              <img src="/images/sidebar/billing.svg" alt="icon" />
            </span>

            <span class="ml-3" :class="{ hidden: state.isCollapse }">Billing</span>
          </router-link>
        </div>
      </div>
      <!-- end Otherlinks -->
    </div>
    <!-- start Other -->
    <div class="grid gap-2 mb-5">
      <div
        class="bg-blue-50 text-indigo-900 px-6 py-1 text-sm justify-self-stretch"
        :class="{ 'text-center': state.isCollapse }"
      >
        Others
      </div>

      <!-- start Other links -->
      <!-- Needs a revision on where it should go -->
      <div class="px-6 grid gap-0.5">
        <div :class="{ 'mx-auto': state.isCollapse }">
          <router-link
            :to="{ name: 'accountHolders' }"
            class="transition ease-out btn-sidebar"
            @click="handleItem(null)"
          >
            <span class="w-6 inline-flex" :class="[state.isCollapse ? 'justify-center' : 'justify-end']">
              <img src="/images/sidebar/account-holders.svg" alt="icon" />
            </span>

            <span class="ml-3" :class="{ hidden: state.isCollapse }">Account Holders</span>
          </router-link>
        </div>
      </div>
      <div class="px-6 grid gap-0.5">
        <div :class="{ 'mx-auto': state.isCollapse }">
          <router-link :to="{ name: 'sponsors' }" class="transition ease-out btn-sidebar" @click="handleItem(null)">
            <span class="w-6 inline-flex" :class="[state.isCollapse ? 'justify-center' : 'justify-end']">
              <img src="/images/sidebar/sponsors.svg" alt="icon" />
            </span>

            <span class="ml-3" :class="{ hidden: state.isCollapse }">Sponsors</span>
          </router-link>
        </div>

        <div :class="{ 'mx-auto': state.isCollapse }">
          <router-link :to="{ name: 'institutions' }" class="transition ease-out btn-sidebar" @click="handleItem(null)">
            <span class="w-6 inline-flex" :class="[state.isCollapse ? 'justify-center' : 'justify-end']">
              <!-- temporary icon, need to changes -->
              <img src="/images/sidebar/institutions.svg" alt="icon" />
            </span>

            <span class="ml-3" :class="{ hidden: state.isCollapse }">Institutions</span>
          </router-link>
        </div>

        <div :class="{ 'mx-auto': state.isCollapse }">
          <router-link :to="{ name: 'transactions' }" class="transition ease-out btn-sidebar" @click="handleItem(null)">
            <span class="w-6 inline-flex" :class="[state.isCollapse ? 'justify-center' : 'justify-end']">
              <img src="/images/sidebar/transactions.svg" alt="icon" />
            </span>
            <span class="ml-3" :class="{ hidden: state.isCollapse }">Transactions</span>
          </router-link>
        </div>

        <div :class="{ 'mx-auto': state.isCollapse }">
          <router-link :to="{ name: 'tags' }" class="transition ease-out btn-sidebar" @click="handleItem(null)">
            <span class="w-6 inline-flex" :class="[state.isCollapse ? 'justify-center' : 'justify-end']">
              <img src="/images/sidebar/tags.svg" alt="icon" />
            </span>

            <span class="ml-3" :class="{ hidden: state.isCollapse }">Tags</span>
          </router-link>
        </div>
      </div>
      <!-- end Otherlinks -->
    </div>
  </div>
</template>
