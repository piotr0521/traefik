import type { AbstractList, AbstractModel, AbstractTable } from "./base";
import { StateList } from "./base";

export interface Invoice extends AbstractModel {
  "@type": "Invoice";
  description: string;
  date: string;
  amount: {
    currency: string;
    base: number;
  };
  status: string;
}

export type InvoiceList = AbstractList<Invoice>;

export class InvoiceState extends StateList<Invoice> {}

export interface InvoiceTable extends AbstractTable {
  headings: ["Date", "Amount", "Status", "Description"];
}
