import type { Axios, AxiosRequestConfig, AxiosResponse } from "axios";

import type { AbstractList, AbstractModel, SearchQuery } from "@/entities/base";
import axiosInstance from "@/plugins/axios";
export default class ModelService<T extends AbstractModel> {
  api: Axios;
  url: string;
  prefix = "/api/";

  constructor(url = "") {
    this.api = axiosInstance;
    this.url = this.prefix + url;
  }
  async list(query: SearchQuery): Promise<AbstractList<T>> {
    const { data } = await this.get(this.url, { params: query });
    return {
      items: data["hydra:member"],
      totalItems: data["hydra:totalItems"],
      ...(data["hydra:view"] ? { pageCount: data["hydra:view"]["hydra:last"]?.split("=")?.pop() || 1 } : {}),
    };
  }
  async show(id: T["id"]): Promise<T> {
    const { data } = await this.get(`${this.url}/${id}`);
    return data;
  }
  get(url: string, config?: AxiosRequestConfig): Promise<AxiosResponse> {
    return this.api.get(url, config);
  }
  async store(model: Partial<T>): Promise<T> {
    const { data } = await this.api.post(this.url, model);
    return data;
  }

  async update(model: Partial<T>): Promise<T> {
    const { data } = await this.api.patch(`${this.url}/${model.id}`, model, {
      headers: {
        "Content-Type": "application/merge-patch+json",
      },
    });
    return data;
  }
  async put(model: Partial<T>, path?: string): Promise<T> {
    const { data } = await this.api.put(`${this.url}/${model.id}` + (path ? `/${path}` : ""), model);
    return data;
  }

  async delete(id: T["id"]): Promise<T> {
    const { data } = await this.api.delete(this.url + "/" + id);
    return data;
  }
}
