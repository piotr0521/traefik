import type { AxiosResponse } from "axios";
import axios from "axios";

import { toaster } from "@/utils/toaster";

const axiosInstance = axios.create();

const LOGIN_PATH = "/login";
const LOGIN_REGEX = new RegExp(`^${LOGIN_PATH}$`);

// Check if the response status indicates that login is required.
function requiresLogin(response: AxiosResponse) {
  if (!response?.request?.responseURL) return false;
  const url = new URL(response.request.responseURL);
  const path = url.pathname;
  return response.status === 302 || response.status === 403 || LOGIN_REGEX.test(path);
}

/*
 * This interceptor will redirect the user to the login page when the response status is 302 or 403, or the path is LOGIN_PATH.
 * It also handles errors when the session cookies expire.
 *
 * To use this interceptor, simply import the axiosInstance object from this module and use it to make your API requests.
 */
axiosInstance?.interceptors?.response.use(
  (response) => {
    if (requiresLogin(response)) {
      const redirectUrl = response.headers["location"];
      window.location.assign(redirectUrl || LOGIN_PATH);
    }

    return response;
  },
  (error) => {
    if (500 <= error.response?.status && error.response?.status >= 599) {
      toaster.error("Server error. Please try again later.");
    }
    return Promise.reject(error);
  }
);

export default axiosInstance;
