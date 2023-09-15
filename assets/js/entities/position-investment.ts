import type { AccountHolder } from "./account-holder";
import type { Asset } from "./asset";
import { type AbstractList, type AbstractModel, type AbstractTable, type SearchQuery, StateList } from "./base";
import type { Institution } from "./institutions";
import type { Sponsor } from "./sponsors";
import type { Tag } from "./tags";

// Needs sponsor
export interface PositionInvestment extends AbstractModel {
  "@type": "PositionInvestment";
  sponsor: Sponsor | Sponsor["id"] | null;
  isDirect: boolean;
  asset: Asset | Asset["id"] | null; //This is the investment
  capitalCommitment: number;
  name: string;
  accountHolder: AccountHolder | AccountHolder["id"] | null;
  institution?: Institution | Institution["id"] | null;
  tags: Tag[] | Tag["id"][];
  notes: string;
}

export const headings = ["Name", "Current value", "Tags"] as const;
export type PositionInvestmentList = AbstractList<PositionInvestment>;

export type PositionInvestmentQuery = SearchQuery;

export class PositionInvestmentState extends StateList<PositionInvestment> {}

export interface PositionInvestmentTable extends AbstractTable {
  headings: [typeof headings[number]];
}
