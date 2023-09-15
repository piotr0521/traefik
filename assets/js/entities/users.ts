import { isArray } from "lodash";

import { type AbstractList, type AbstractModel, type AbstractTable, StateFetch } from "./base";
import { SearchQuery, StateList } from "./base";

export interface User extends AbstractModel {
  "@type": "User";
  firstName: string;
  lastName: string;
  username: string;
  password?: string;
}
export interface PasswordReset extends User {
  currentPassword: string;
  newPassword: string;
  newPassword_confirmation: string;
}
export type UserList = AbstractList<User>;

export class UserQuery extends SearchQuery {
  name?: string;

  constructor(query: Partial<UserQuery> = {}) {
    super();
    this.name = (isArray(query.name) ? query.name[0] : query.name) || "";
  }
}

export class UserState extends StateList<User> {}
export class UserFetchState extends StateFetch<User> {}

export interface UserTable extends AbstractTable {
  headings: "Users"[];
}

export type Password = {
  currentPassword: string;
  newPassword: string;
  newPassword_confirmation: string;
};
