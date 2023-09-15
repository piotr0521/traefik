import type { User, UserList, UserQuery } from "@/entities/users";

import ModelService from "./ModelService";
export default class UserService extends ModelService<User> {
  constructor() {
    super("users");
  }
  show(id: User["id"]): Promise<User> {
    return super.show(id);
  }
  list(query: UserQuery): Promise<UserList> {
    return super.list(query);
  }

  store(user: Partial<User>): Promise<User> {
    return super.store(user);
  }

  update(user: Partial<User>): Promise<User> {
    return super.update(user);
  }

  put(user: Partial<User>, path?: string): Promise<User> {
    return super.put(user, path);
  }
}
