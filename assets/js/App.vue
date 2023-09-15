<script setup lang="ts">
import { storeToRefs } from "pinia";
import { onBeforeMount, watch } from "vue";
import { type RouteRecordName, useRoute, useRouter } from "vue-router";

import Alert from "@/components/Alert.vue";
import AppHeader from "@/components/layout/AppHeader.vue";
import Breadcrumbs from "@/components/layout/BreadCrumbs.vue";
import SideBar from "@/components/layout/SideBar.vue";
import InvestmentsPopup from "@/components/layout/TypeSelectionPopup.vue";
import { useAlertStore } from "@/stores/alert";
import { useAppStore } from "@/stores/app";
import { useDateIntervalStore } from "@/stores/interval";
import { useUserStore } from "@/stores/user";

// Load all stores required for the whole app
const appStore = useAppStore();
const userStore = useUserStore();
const alertStore = useAlertStore();
const routeChange: RouteRecordName[] = ["dashboard", "assetType", "position"];
const hydrate = async function () {
  //@todo rethink this part, perhaps /users/me endpoint should work better
  userStore.setId(document.querySelector('[name="user-id"]')?.getAttribute("content") || "");

  await appStore.hydrate();
};

// https://stackoverflow.com/questions/69183835/vue-script-setup-top-level-await-causing-template-not-to-render
onBeforeMount(async () => await hydrate());

// Change URL if interval is updated
const route = useRoute();
const router = useRouter();
const changeUrlIfRequired = function (replace = false) {
  if (!routeChange.some((item) => item === route.name)) return;
  let query = { ...route.query };
  if (query.from === interval.value.start && query.to === interval.value.end) {
    return;
  }
  changeUrl(replace);
};

const changeUrl = function (replace = false) {
  let query = { ...route.query };
  query.from = interval.value.start;
  query.to = interval.value.end;
  router.replace({
    path: route.path,
    query,
    replace,
  });
};
const dateIntervalStore = useDateIntervalStore();
const { interval } = storeToRefs(dateIntervalStore);
watch(interval, () => changeUrlIfRequired());
watch(route, () => changeUrlIfRequired(true));
</script>

<template>
  <div v-if="appStore.hydrated" class="flex">
    <SideBar></SideBar>
    <div class="grow bg-[#FBFCFE]">
      <AppHeader></AppHeader>
      <Breadcrumbs></Breadcrumbs>
      <router-view :key="$route.path"></router-view>
      <InvestmentsPopup></InvestmentsPopup>
      <div>
        <Alert />
      </div>
    </div>
  </div>
</template>
