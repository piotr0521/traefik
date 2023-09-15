import axios from "axios";

import axiosInstance from "@/middleware/api";

import { toaster } from "./toaster";

export async function fetch(url) {
  try {
    const { data } = await axios(url);
    return data["hydra:member"].map((item) => ({
      label: item.name,
      code: item["@id"],
    }));
  } catch (error) {
    toaster.error("Loading error. Please try again later");
    console.error(error);
  }
}
export async function fetchTags() {
  try {
    const { data } = await axios(`/api/tags`);
    return data["hydra:member"].map((item) => ({
      label: item.name,
      code: item["@id"],
      color: item.color,
    }));
  } catch (error) {
    toaster.error("Loading error. Please try again later");
    console.error(error);
  }
}
export async function fetchParams(url, query) {
  try {
    const { data } = await axiosInstance.get(url, { params: query });
    return data["hydra:member"].map((item) => ({
      label: item.name,
      code: item["@id"],
    }));
  } catch (error) {
    toaster.error("Loading error. Please try again later");
    console.error(error);
  }
}

export async function fetchSymbol(url) {
  try {
    const { data } = await axios(url);
    return data["hydra:member"].map((item) => ({
      label: item.symbol + " " + item.name,
      code: item["@id"],
    }));
  } catch (error) {
    toaster.error("Loading error. Please try again later");
    console.error(error);
  }
}

export async function fetchParamsSymbol(url, query) {
  try {
    const { data } = await axiosInstance.get(url, { params: query });
    return data["hydra:member"].map((item) => ({
      label: item.symbol + " " + item.name,
      code: item["@id"],
    }));
  } catch (error) {
    toaster.error("Loading error. Please try again later");
    console.error(error);
  }
}

export function redirect(router, id) {
  router.push({
    name: "position",
    params: { uuid: id },
  });
}

export function goBack(router) {
  router.back();
}

export async function submit(state, url, router) {
  state.isFetching = true;
  try {
    const { data } = await axios.post(url, { ...state.form });
    redirect(router, data.id);
  } catch (error) {
    if (error.response?.data?.violations) {
      error.response.data.violations.forEach((item) => {
        state.errors[item.propertyPath] = item.message;
      });
    }
    console.log(error);
  } finally {
    state.isFetching = false;
  }
}

export async function update(state, url, router = null, toasterText = null) {
  state.isFetching = true;
  try {
    const { data } = await axios.patch(
      url,
      { ...state.form },
      { headers: { "Content-Type": "application/merge-patch+json" } }
    );

    // redirect router if parameter router is not null
    if (router) {
      redirect(router, data.id);
    }

    // show toaster when toasterText is existed
    if (toasterText) {
      toaster.success(toasterText);
    }
  } catch (error) {
    if (error.response?.data?.violations) {
      error.response.data.violations.forEach((item) => {
        state.errors[item.propertyPath] = item.message;
      });
    } else {
      state.errors.server = true;
    }
    console.log(error);
  } finally {
    state.isFetching = false;
  }
}

export async function fetchPosition(route) {
  try {
    const { data } = await axiosInstance.get(`/api/position/positions/${route.params.uuid}`);
    return data["@id"];
  } catch (e) {
    console.log(e);
    toaster.error("Loading error. Please try again later");
  }
}

export async function getData(url) {
  try {
    const { data } = await axiosInstance.get(url);
    return data;
  } catch (e) {
    toaster.error("Loading error. Please try again later");
    console.log(e);
  }
}
