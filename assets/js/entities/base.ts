import type { VNode } from "vue";

export interface AbstractModel {
  "@id": string;
  "@type": string;
  id: string | number;
}

// SearchQuery is an base class that defines search parameters for different components
export class SearchQuery {}
export type BadgeState = "success" | "warning" | "danger" | "info";
export interface AbstractTable {
  headings: string[];
  rows: {
    values: (string | number | boolean | VNode | null | undefined)[];
    [key: string]:
      | (string | number | boolean | { [key: string]: any | any[] } | VNode | null | undefined)
      | (string | number | boolean | { [key: string]: any | any[] } | VNode | null | undefined)[];
  }[];
}
export type AnyModel<T extends Partial<AbstractModel>> = {
  [k in keyof T]?: any;
};
// Class to store the state of the component that deals with a set of records from the API
export class StateList<T extends AbstractModel> {
  items: T[] = [];
  hydrating = false;
  error: string | null = null;
  pagination: PaginationCount = new PaginationCount();
}
export class StateFetch<T extends AbstractModel> {
  item: T = {} as T;
  loading = false;
  error: string | null = null;
}
export interface AbstractList<T extends AbstractModel> {
  items: T[];
  totalItems: number;
  pageCount?: number;
}

export class PaginationCount {
  itemCount = 0;
  pageCount = 0;
}

// Pagination query is a class that defines parameters for pagination
export class PaginationQuery {
  page = 1;
}

export type Error = {
  [k: string]: string | null;
};
