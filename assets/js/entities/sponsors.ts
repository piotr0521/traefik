import { isArray } from "lodash";

import type { AbstractList, AbstractModel, AbstractTable } from "./base";
import { SearchQuery, StateList } from "./base";

export interface Sponsor extends AbstractModel {
  "@type": "Sponsor";
  name: string;
  website: string;
  privacy: "public" | "private";
}

export type SponsorList = AbstractList<Sponsor>;

export class SponsorQuery extends SearchQuery {
  name?: string;
  privacy?: string[];

  constructor(query: Partial<SponsorQuery> = {}) {
    super();
    this.name = (isArray(query.name) ? query.name[0] : query.name) || "";
    this.privacy = isArray(query.privacy) ? query.privacy : query.privacy ? [query.privacy] : [];
  }
}

export class SponsorState extends StateList<Sponsor> {}

export interface SponsorTable extends AbstractTable {
  headings: "Sponsors"[];
}
