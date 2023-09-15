import type { AccountHolder } from "./account-holder";
import type { AbstractList, AbstractModel, AbstractTable, SearchQuery } from "./base";
import { StateList } from "./base";
import type { Institution } from "./institutions";
import type { Tag } from "./tags";

export interface PositionCreditCard extends AbstractModel {
  "@type": "PositionCreditCard";
  name: string;
  accountHolder: AccountHolder | AccountHolder["id"];
  institution: Institution | Institution["id"];
  cardLimit: number;
  cardBalance: number;
  tags: Tag[] | Tag["id"][];
  notes: string;
}
export const headings = ["Name", "Current value", "Tags"] as const;
export type PositionCreditCardList = AbstractList<PositionCreditCard>;

export type PositionCreditCardQuery = SearchQuery;

export class PositionCreditCardState extends StateList<PositionCreditCard> {}

export interface PositionCreditCardTable extends AbstractTable {
  headings: [typeof headings[number]];
}
