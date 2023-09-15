<script setup lang="ts">
import Badge from "@/components/Badge.vue";
</script>

<template>
  <div class="card p-0 flex flex-col flex-shrink-0 divide-y divide-blue-200 divide-dashed">
    <div class="px-8 py-6">
      <h2 class="h2">Multi-Factor Authentication</h2>
    </div>
    <div class="px-8 py-6 grid grid-cols-6">
      <div class="md:flex md:items-center col-span-5">
        <div class="mb-1 md:mb-0 md:w-1/3">
          <label class="h3"> Multi-Factor Authentication </label>
        </div>
        <div class="md:w-2/3 md:flex-grow">
          <span class="text-indigo-900"> Protect your Groshy.io account with an extra security step </span>
          <Badge class="ml-3" type="success"> Verified </Badge>
        </div>
      </div>
      <div class="flex justify-end">
        <button class="btn btn-security">Edit</button>
      </div>
    </div>
  </div>
</template>
