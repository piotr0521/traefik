import { type RouteRecordName, useRouter } from "vue-router";

export default function useVueRouter() {
  const router = useRouter();

  const pushQuery = (query: any = {}) => {
    cleanQuery(query);
    router.push({
      name: router.currentRoute.value.name as RouteRecordName,
      query,
    });
  };

  const replaceQuery = (query: any = {}) => {
    cleanQuery(query);
    router.replace({
      name: router.currentRoute.value.name as RouteRecordName,
      query,
    });
  };
  const cleanQuery = (query: any = {}) => {
    (Object.keys(query) as (keyof typeof query)[]).forEach((key) => {
      if (!query[key]) delete query[key];
    });
  };

  return { pushQuery, replaceQuery };
}
