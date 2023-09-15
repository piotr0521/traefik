<template>
  <section class="bg-bodyBg flex-1 flex items-center relative overflow-hidden py-20 md:py-24 xl:py-32">
    <div class="container mx-auto px-4 relative z-10">
      <img
        class="absolute -top-16 xl:top-1/4 right-7 xl:-right-12 z-0 hidden sm:block"
        src="images/form/bg-form-right.svg"
        alt="ornament"
      />
      <img
        class="absolute top-8 lg:top-1/3 left-7 xl:-left-6 z-0 hidden sm:block"
        src="images/form/bg-form-left.svg"
        alt="ornament"
      />

      <div class="flex flex-col items-center flex-wrap lg:flex-nowrap mb-10">
        <div class="md:w-10/12 xl:w-3/5 text-indigo-900 m-auto text-center" data-aos="fade-up" data-aos-delay="200">
          <h1 class="text-4xl md:text-5xl font-semibold mx-auto md:mx-0 mb-8">Select a plan</h1>
          <p class="text-slate-900 text-base md:text-xl font-light">
            We’ll email you before you ever get charged.
            <br />
            Cancel anytime during the trial.
          </p>
        </div>
      </div>
      <div class="max-w-3xl mx-auto">
        <form id="subscription-form" @submit.prevent="handleSubmit">
          <div v-if="!state.isFetching">
            <div
              v-for="price in state.prices"
              :key="price.id"
              class="card-subscription"
              :class="{
                'is-selected': state.selectedPlan === price['@id'],
              }"
              @click="handleSelectedPlanChange(price['@id'])"
            >
              <p class="price">
                {{ formatAmount(price.amount.amount, price.amount.currency) }} /
                {{ price.recurringInterval }}
              </p>
              <p v-if="price.recurringInterval === 'month'">
                {{ formatAmount(price.amount.amount * 12, price.amount.currency) }}
                per year, billed monthly
              </p>
              <p v-else>
                <span class="tag">most popular</span>
                {{ formatAmount(price.amount.amount / 12, price.amount.currency) }}
                per month, billed yearly
              </p>
            </div>
            <div class="block border-b border-dashed border-slate-300 my-8"></div>
            <div class="mb-10">
              <h4 class="text-xl md:text-2xl font-semibold text-indigo-900 mb-4">Payment details</h4>
              <stripe-elements
                v-if="stripeLoaded"
                v-slot="{ elements }"
                ref="elms"
                :stripe-key="stripeKey"
                :elements-options="elementsOptions"
              >
                <stripe-element
                  ref="card"
                  :elements="elements"
                  :options="cardOptions"
                  class="px-3 py-4 rounded-md border bg-white border-slate-300"
                />
              </stripe-elements>
            </div>
            <div class="mb-10">
              <h4 class="text-xl md:text-2xl font-semibold text-indigo-900 mb-4">Promo code (optional)</h4>
              <div class="flex form-row">
                <input
                  type="text"
                  class="form-input px-3 md:px-5 py-4 focus:border-slate-300 focus:ring-transparent focus:shadow-none"
                />
                <div
                  class="btn bg-slate-300 text-white font-normal capitalize px-4 md:px-10 transition-colors hover:bg-sky-400 text-base md:text-xl inline-flex items-center"
                >
                  apply
                </div>
              </div>
            </div>
            <div class="mt-4 text-center">
              <template v-if="!state.isFetching">
                <p class="text-xl text-indigo-900 font-normal mb-6">Billed in 7 days: {{ yearlyPrice }}</p>
              </template>
              <button type="submit" :disabled="!state.selectedPlan || state.isLoadingSubscribe" class="btn btn-primary">
                <span v-if="state.isLoadingSubscribe" class="flex items-center justify-center">
                  <svg
                    class="animate-spin -ml-1 mr-3 h-5 w-5 text-white"
                    xmlns="http://www.w3.org/2000/svg"
                    fill="none"
                    viewBox="0 0 24 24"
                  >
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path
                      class="opacity-75"
                      fill="currentColor"
                      d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"
                    ></path>
                  </svg>
                  Processing...
                </span>
                <span v-else>Try premium</span>
              </button>
            </div>
          </div>
          <div v-else class="relative mx-auto w-48 h-10">
            <Preloader />
          </div>
        </form>
      </div>
    </div>
  </section>
</template>

<script setup>
import { loadStripe } from "@stripe/stripe-js";
import axios from "axios";
import { computed, onBeforeMount, onMounted, reactive, ref } from "vue";
import { StripeElement, StripeElements } from "vue-stripe-js";

import Preloader from "@/components/Preloader";
import axiosInstance from "@/middleware/api";
import { formatAmount } from "@/utils/format";
import { toaster } from "@/utils/toaster";

const state = reactive({
  prices: [],
  isFetching: false,
  selectedPlan: null,
  clientSecret: null,
  isLoadingSubscribe: false,
});

const stripeKey = ref("pk_test_JJhIwOHLuzUzw4tR6vtVdPsF");
const stripeLoaded = ref(false);

// https://stripe.com/docs/js/appendix/style
const elementsOptions = ref({
  fonts: [{ cssSrc: "https://fonts.googleapis.com/css2?family=Readex+Pro:wght@300&display=swap" }],
});

const cardOptions = ref({
  style: {
    base: {
      fontFamily: `Readex Pro, sans-serif`,
      fontSize: "16px",
      color: "#0F172A",
      textAlign: "center",
      "::placeholder": {
        color: "#CBD5E1",
      },
    },
  },
});

// https://stripe.com/docs/js/elements_object/create_element?type=card
const card = ref();
const elms = ref();

// get a yearly from prices
const yearlyPrice = computed(() => {
  const price = state.prices.find((price) => price.recurringInterval === "year");
  return price ? formatAmount(price.amount.amount, price.amount.currency) : null;
});

// A function that sets the selected plan to the state.
const handleSelectedPlanChange = (id) => {
  state.selectedPlan = id;
};

// Submit handler for the form. It prevents the default behavior of the form, checks if a plan is selected, and if so,
// it creates a subscription and confirms the payment.
const handleSubmit = async (event) => {
  event.preventDefault();

  // Checking if the user has selected a plan. If not, it will show an error message.
  if (!state.selectedPlan) {
    toaster.error(`Please select a plan to continue.`);
    return;
  }

  try {
    await createSubscription();
    await confirmPaymentWithCard();
  } catch (error) {
    console.log("Error creating subscription", error);
  }
};

// Fetching the prices from the API and setting the default selected plan to the first one.
const fetchPrices = async () => {
  if (state.isFetching) return;
  state.isFetching = true;
  try {
    const { data } = await axiosInstance.get(`/api/prices`);
    state.prices = data["hydra:member"];
    // find the first price with recurringInterval of "year"
    const yearPrice = state.prices.find((price) => price.recurringInterval === "year");
    state.selectedPlan = yearPrice["@id"]; // set selected plan to the first one with recurringInterval of "year"
  } catch (error) {
    console.log("Error fetching prices", error);
    throw error;
  } finally {
    state.isFetching = false;
  }
};

// Creating a subscription and set the clientSecret result in the state object for confirm the payment later.
const createSubscription = async () => {
  if (state.isLoadingSubscribe) return;
  state.isLoadingSubscribe = true;
  try {
    const { data } = await axios.post(`/api/subscriptions`, {
      price: state.selectedPlan,
    });
    state.clientSecret = data["clientSecret"];
  } catch (error) {
    console.error("Error creating subscription", error);
    throw error;
  } finally {
    state.isLoadingSubscribe = false;
  }
};

// Confirms the payment and completes the subscription with clientSecret
const confirmPaymentWithCard = async () => {
  state.isLoadingSubscribe = true;
  try {
    // get stripe element
    const cardElement = card.value.stripeElement;

    // confirming the payment.
    await elms.value.instance
      .confirmCardPayment(state.clientSecret, {
        payment_method: {
          card: cardElement,
        },
      })
      .then((result) => {
        console.log("result", result);
        if (result.error) {
          toaster.error(`Payment failed, ${result.error.message}. Please try again.`, {
            duration: 5000,
          });
        } else {
          toaster.success(`Payment successful. Thank you for subscribing!`, {
            duration: 5000,
          });

          // Redirecting the user to the dashboard after a successful payment with reload browser
          setTimeout(() => {
            window.location.assign(`/user/dashboard`);
          }, 800);
        }
      });
  } catch (error) {
    console.error("Error creating subscription", error);
    throw error;
  } finally {
    state.isLoadingSubscribe = false;
  }
};

onBeforeMount(() => {
  const stripePromise = loadStripe(stripeKey.value);
  stripePromise.then(() => {
    stripeLoaded.value = true;
  });
});

onMounted(async () => {
  await fetchPrices();
});
</script>
