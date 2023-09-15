import type { Tag, TagList, TagQuery } from "@/entities/tags";

import ModelService from "./ModelService";
export default class TagService extends ModelService<Tag> {
  constructor() {
    super("tags");
  }
  list(query: TagQuery): Promise<TagList> {
    return super.list(query);
  }

  update(tag: Partial<Tag>): Promise<Tag> {
    return super.update(tag);
  }

  delete(id: Tag["id"]): Promise<Tag> {
    return super.delete(id);
  }
}
