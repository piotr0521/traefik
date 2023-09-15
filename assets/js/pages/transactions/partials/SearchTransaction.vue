<script setup>
import { onMounted, reactive, ref, watch } from "vue";
import vSelect from "vue-select";

import Preloader from "@/components/Preloader.vue";
import axiosInstance from "@/middleware/api";

const props = defineProps({
  position: String,
});

const emit = defineEmits(["update:position"]);

const position = ref(null);

const state = reactive({
  positions: [],
  isFetching: true,
});

watch(position, (newValue) => {
  emit("update:position", newValue?.code ?? null);
});

async function getPositions() {
  try {
    const { data } = await axiosInstance.get(`/api/position/positions`);
    state.positions = data["hydra:member"].map((item) => {
      return {
        label: item.name,
        code: item.id,
      };
    });
    // Initialize selected value
    position.value = state.positions.find((item) => item.code == props.position);
  } catch (error) {
    // toaster.error(error.message);
    console.log(error.message);
  }
  state.isFetching = false;
}

onMounted(() => {
  getPositions();
});
</script>

<template>
  <div class="card p-8 relative">
    <form class="grid gap-5 xl:grid-cols-2 items-start" @submit.prevent="submit">
      <div class="grid gap-1">
        <vSelect v-model="position" placeholder="Select Investments" :options="state.positions"> </vSelect>
      </div>

      <div class="grid justify-end self-end">
        <button type="submit" class="btn btn-primary">Search</button>
      </div>
    </form>

    <Preloader v-if="state.isFetching" />
  </div>
</template>
