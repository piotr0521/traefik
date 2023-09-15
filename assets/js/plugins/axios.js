import axiosInstance from "@/middleware/api";
axiosInstance.defaults.withCredentials = true;
axiosInstance.defaults.headers.common["X-Requested-With"] = "XMLHttpRequest";
axiosInstance.defaults.headers.common["Content-Type"] = "application/json";
export default axiosInstance;
