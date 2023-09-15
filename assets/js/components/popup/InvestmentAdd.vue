<script setup>
import axios from "axios";
import { reactive } from "vue";
import { useRoute, useRouter } from "vue-router";

import ButtonClose from "@/components/icons/ButtonClose.vue";
import CheckboxLabelBase from "@/components/input/CheckboxLabelBase.vue";
import CheckboxLabelStyle from "@/components/input/CheckboxLabelStyle.vue";
import InputLabelBase from "@/components/input/InputLabelBase.vue";
import Preloader from "@/components/Preloader.vue";
import { useAssetTypeStore } from "@/stores/assetType";
import { usePopupStore } from "@/stores/popup.js";
import { toaster } from "@/utils/toaster";

const emit = defineEmits(["update:investments"]);
const props = defineProps(["sponsor"]);
const state = reactive({
  investment: {
    form: {
      name: null,
      privacy: "public",
      website: null,
      isEvergreen: true,
      term: null,
      irr: null,
      multiple: null,
      asset: null,
    },
    errors: {},
  },
  isFetching: false,
});

const route = useRoute();
const router = useRouter();
state.investment.form.asset = useAssetTypeStore().getBySlug(route.params.slug)["@id"];
const { setPopupState, getPopupState } = usePopupStore();

function closePopup() {
  setPopupState({ popupName: "showPopupInvestmentAdd", value: false });
}

async function submit() {
  state.isFetching = true;
  state.investment.errors = {};
  try {
    let { data } = await axios.post(`/api/asset/investments`, {
      name: state.investment.form.name,
      privacy: state.investment.form.privacy,
      sponsor: props.sponsor,
      assetType: state.investment.form.asset,
      website: state.investment.form.website,
      isEvergreen: state.investment.form.isEvergreen,
      term: state.investment.form.term,
      irr: state.investment.form.irr,
      multiple: state.investment.form.multiple,
    });
    emit("update:investments");
    closePopup();
    toaster.success("New investment added");
  } catch (errors) {
    errors.response.data.violations.forEach((error) => {
      state.investment.errors[error.propertyPath] = error.message;
    });
    console.log(errors);
  } finally {
    state.isFetching = false;
  }
}
</script>

<template>
  <div
    v-if="getPopupState('showPopupInvestmentAdd')"
    class="fixed top-0 left-0 right-0 bottom-0 min-h-full min-w-full z-10 flex items-center justify-center"
  >
    <!-- start overlay -->
    <div
      class="absolute top-0 left-0 right-0 bottom-0 min-h-full min-w-full z-0"
      style="background: rgba(49, 46, 129, 0.7)"
      @click="closePopup"
    ></div>
    <!-- end overlay -->

    <!-- start popup -->
    <div class="bg-white p-12 relative z-10 max-w-screen-md w-full rounded-md">
      <!-- start button close -->
      <ButtonClose @click="closePopup" />
      <!-- end butotn close -->

      <h2 class="text-indigo-900 font-normal text-xl mb-5">Add new investment</h2>

      <!-- start content -->
      <form class="grid gap-5" @submit.prevent="submit">
        <InputLabelBase
          v-model:value="state.investment.form.name"
          v-model:error="state.investment.errors.name"
          label="Investment name"
          name="groshy_investment_add_name"
        />

        <CheckboxLabelStyle
          v-model:value="state.investment.form.privacy"
          label="Public investment (other users can see it)"
          name="groshy_investment_add_privacy"
        />

        <InputLabelBase
          v-model:value="state.investment.form.website"
          v-model:error="state.investment.errors.website"
          label="Investment website"
          name="groshy_investment_add_website"
        />

        <div class="grid gap-1">
          <span class="form-label mb-2">Investment term</span>
          <div class="flex items-center gap-10">
            <CheckboxLabelBase
              v-model:value="state.investment.form.isEvergreen"
              label="Evergreen"
              name="groshy_investment_add_isEvergreen"
            />

            <div class="flex items-center gap-3">
              <input
                id="groshy_investment_add_term"
                v-model="state.investment.form.term"
                class="form-input"
                type="text"
              />
              <label class="form-label" for="groshy_investment_add_term"> years </label>
            </div>
          </div>
        </div>

        <div class="flex items-center gap-10">
          <div class="flex items-center gap-10">
            <InputLabelBase
              v-model:value="state.investment.form.irr"
              v-model:error="state.investment.errors.irr"
              label="Targeted IRR"
              name="groshy_investment_add_IRR"
            />
            <span class="self-end -ml-8 py-3 text-slate-900">%</span>

            <InputLabelBase
              v-model:value="state.investment.form.multiple"
              v-model:error="state.investment.errors.multiple"
              label="Targeted Equity Multiple"
              name="groshy_investment_add_multiple"
            />
          </div>
        </div>

        <div class="flex gap-8 justify-center mt-4">
          <button type="button" class="btn btn-cancel" @click="closePopup">Cancel</button>

          <button type="submit" class="btn btn-primary">Submit</button>
        </div>
      </form>
      <!-- end content -->
      <Preloader v-if="state.isFetching" />
    </div>
    <!-- end popup -->
  </div>
</template>
