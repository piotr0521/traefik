/*
 * Welcome to your app's main JavaScript file!
 *
 * We recommend including the built version of this JavaScript file
 * (and its CSS file) in your base layout (base.html.twig).
 */

// any CSS you import will output into a single css file (app.css in this case)
import "./../styles/app.css";

import axios from "axios";
import { createPinia } from "pinia";
import { createApp } from "vue";
import VueAxios from "vue-axios";

import App from "./App.vue";
import router from "./routes";

const app = createApp(App).use(router).use(VueAxios, axios).use(createPinia());
app.config.unwrapInjectedRef = true;
app.mount("#app");
