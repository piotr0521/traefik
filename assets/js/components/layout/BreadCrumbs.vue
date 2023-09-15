<script setup>
import { storeToRefs } from "pinia";

import { useBreadcrumbsStore } from "@/stores/breadcrumbs";
import { useDateIntervalStore } from "@/stores/interval";

const { breadcrumbs } = storeToRefs(useBreadcrumbsStore());
const { interval } = storeToRefs(useDateIntervalStore());

function getQuery(routeName) {
  return ["dashboard", "assetType"].includes(routeName) ? { from: interval.value.start, to: interval.value.end } : {};
}
</script>

<template v-if="breadcrumbs">
  <div class="breadcrumbs py-3 px-7 bg-blue-50">
    <ul class="flex text-sm text-slate-700">
      <li v-for="(item, index) in breadcrumbs" :key="index" class="mr-2">
        <router-link
          v-if="index !== breadcrumbs.length - 1"
          class="opacity-40 inline-flex items-center hover:text-blue-700 hover:underline"
          :to="{
            name: item.route.name,
            params: item.route.params,
            query: getQuery(item.route.name),
          }"
        >
          <span>
            {{ item.title }}
          </span>

          <svg
            class="ml-3 block shrink-0"
            width="4"
            height="6"
            viewBox="0 0 3 6"
            fill="none"
            xmlns="http://www.w3.org/2000/svg"
          >
            <path
              fill-rule="evenodd"
              clip-rule="evenodd"
              d="M1.96534 3L0.125526 1.21129C-0.0418419 1.04858 -0.0418419 0.784758 0.125526 0.622039C0.292893 0.459321 0.56425 0.459321 0.731617 0.622039L2.87447 2.70537C3.04184 2.86809 3.04184 3.13191 2.87447 3.29463L0.731617 5.37796C0.56425 5.54068 0.292893 5.54068 0.125526 5.37796C-0.0418419 5.21524 -0.0418419 4.95142 0.125526 4.78871L1.96534 3Z"
              fill="#334155"
            />
          </svg>
        </router-link>

        <span v-else>
          {{ item.title }}
        </span>
      </li>
    </ul>
  </div>
</template>
