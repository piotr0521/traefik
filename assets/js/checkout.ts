/*
 * This is the entry point for the checkout app.
 */
import axios from "axios";
import { createApp } from "vue";
import VueAxios from "vue-axios";

import Checkout from "./pages/checkout/Checkout.vue";

const checkoutApp = createApp(Checkout).use(VueAxios, axios);
checkoutApp.mount("#checkout-app");
