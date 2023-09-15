import { isArray } from "lodash";

import type { AbstractList, AbstractModel, AbstractTable } from "./base";
import { SearchQuery, StateList } from "./base";

export interface AccountType extends AbstractModel {
  "@type": "AccountType";
  name: string;
}

export type AccountTypeList = AbstractList<AccountType>;

export class AccountHolderQuery extends SearchQuery {
  name?: string;
  constructor(query: Partial<AccountHolderQuery> = {}) {
    super();
    this.name = (isArray(query.name) ? query.name[0] : query.name) || "";
  }
}

export class AccountHolderState extends StateList<AccountType> {}

export interface AccountHolderTable extends AbstractTable {
  headings: ["Name"];
}
