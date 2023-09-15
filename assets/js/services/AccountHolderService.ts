import type { AccountHolder, AccountHolderList, AccountHolderQuery } from "@/entities/account-holder";

import ModelService from "./ModelService";

export default class AccountHolderService extends ModelService<AccountHolder> {
  constructor() {
    super("account_holders");
  }
  list(query: AccountHolderQuery): Promise<AccountHolderList> {
    return super.list(query);
  }

  store(accountHolder: Partial<AccountHolder>): Promise<AccountHolder> {
    return super.store(accountHolder);
  }

  delete(id: AccountHolder["id"]): Promise<AccountHolder> {
    return super.delete(id);
  }
}
