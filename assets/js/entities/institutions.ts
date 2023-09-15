import { isArray } from "lodash";

import type { AbstractList, AbstractModel, AbstractTable } from "./base";
import { SearchQuery, StateList } from "./base";

export interface Institution extends AbstractModel {
  "@type": "Institution";
  name: string;
  website?: string;
}

export type InstitutionList = AbstractList<Institution>;

export class InstitutionQuery extends SearchQuery {
  name?: string;
  constructor(query: Partial<InstitutionQuery> = {}) {
    super();
    this.name = (isArray(query.name) ? query.name[0] : query.name) || "";
  }
}

export class InstitutionState extends StateList<Institution> {}

export interface InstitutionTable extends AbstractTable {
  headings: ["Name", "Website"];
}
