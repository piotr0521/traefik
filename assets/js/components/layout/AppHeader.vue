<script setup>
import { vOnClickOutside } from "@vueuse/components";
import { storeToRefs } from "pinia";

import ButtonDots from "@/components/icons/ButtonDots.vue";
import { usePopupStore } from "@/stores/popup";
import { useUserStore } from "@/stores/user";

const { user, initials } = storeToRefs(useUserStore());
const { getPopupState, togglePopupState, setPopupState } = usePopupStore();

const popupName = "AppHeaderPopup";

function toggleDropdown() {
  togglePopupState(popupName);
}

function closeDropdown() {
  setPopupState({ popupName, value: false });
}
</script>

<template>
  <header
    class="flex justify-end border border-blue-50 items-center py-3 px-7 shadow-[0px_4px_19px_rgba(191,219,254,0.33)]"
  >
    <!-- start user info -->
    <div class="flex items-center">
      <div
        v-if="initials"
        class="w-12 h-12 flex items-center justify-center bg-blue-500 text-white rounded-full font-semibold text-2xl mr-2"
      >
        {{ initials }}
      </div>
      <span class="text-slate-900 font-regular"> {{ user.firstName }} {{ user.lastName }} </span>
    </div>
    <!-- end user info -->

    <!-- start user links -->
    <!-- <div v-on-click-outside="closeDropdown" class="relative"> -->
    <div v-on-click-outside="closeDropdown" class="relative">
      <!-- start btn -->
      <ButtonDots class="ml-9" @click="toggleDropdown"></ButtonDots>
      <!-- end btn -->

      <!-- start dropdown -->
      <div
        :class="{ hidden: !getPopupState(popupName) }"
        class="w-56 flex flex-col gap-5 p-5 border border-blue-200 rounded-md absolute mt-2 top-full right-0 bg-white shadow-[0px_8px_26px_-8px_rgba(203,213,225,0.5)] z-10"
      >
        <router-link :to="{ name: 'profile' }" class="flex items-center text-indigo-900 text-sm" href="#">
          <img class="mr-3" src="/images/icon/profile.svg" alt="icon" />
          <span>Update Profile</span>
        </router-link>

        <router-link class="flex items-center text-indigo-900 text-sm" :to="{ name: 'password' }">
          <img class="mr-3" src="/images/icon/password.svg" alt="icon" />
          <span>Update Password</span>
        </router-link>

        <a class="flex items-center text-indigo-900 text-sm" href="/user/dashboard">
          <img class="mr-3" src="/images/icon/dashboard.svg" alt="icon" />
          <span>Dashboard</span>
        </a>

        <a class="flex items-center text-indigo-900 text-sm" href="/logout">
          <img class="mr-3" src="/images/icon/logout.svg" alt="icon" />
          <span>Log out</span>
        </a>
      </div>
      <!-- end dropdown -->
    </div>
  </header>
</template>
