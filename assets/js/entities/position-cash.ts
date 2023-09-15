import type { AccountHolder } from "./account-holder";
import type { AccountType } from "./account-type";
import type { AbstractList, AbstractModel, AbstractTable, SearchQuery } from "./base";
import { StateList } from "./base";
import type { Institution } from "./institutions";
import type { Tag } from "./tags";
// TODO AccountType type
export interface PositionCash extends AbstractModel {
  "@type": "PositionCash";
  name: string;
  accountHolder: AccountHolder | AccountHolder["id"];
  accountType: AccountType | AccountType["id"];
  institution: Institution | Institution["id"];
  yield: number;
  currentValue: number;
  tags: Tag[] | Tag["id"][];
  notes: string;
}
export const headings = ["Name", "Open on", "Current value", "Tags"] as const;
export type PositionCashList = AbstractList<PositionCash>;

export type PositionCashQuery = SearchQuery;

export class PositionCashState extends StateList<PositionCash> {}

export interface PositionCashTable extends AbstractTable {
  headings: [typeof headings[number]];
}
