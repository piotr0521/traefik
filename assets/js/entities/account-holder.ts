import { isArray } from "lodash";

import type { AbstractList, AbstractModel, AbstractTable } from "./base";
import { SearchQuery, StateList } from "./base";

export interface AccountHolder extends AbstractModel {
  "@type": "AccountHolder";
  name: string;
}

export type AccountHolderList = AbstractList<AccountHolder>;

export class AccountHolderQuery extends SearchQuery {
  name?: string;
  constructor(query: Partial<AccountHolderQuery> = {}) {
    super();
    this.name = (isArray(query.name) ? query.name[0] : query.name) || "";
  }
}

export class AccountHolderState extends StateList<AccountHolder> {}

export interface AccountHolderTable extends AbstractTable {
  headings: ["Name"];
}
