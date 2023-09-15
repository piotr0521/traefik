import type { TagGroup, TagGroupList, TagGroupQuery } from "@/entities/tags";

import ModelService from "./ModelService";
export default class TagGroupService extends ModelService<TagGroup> {
  constructor() {
    super("tag_groups");
  }
  list(query: TagGroupQuery): Promise<TagGroupList> {
    return super.list(query);
  }

  update(tagGroup: Partial<TagGroup>): Promise<TagGroup> {
    return super.update(tagGroup);
  }

  delete(id: TagGroup["id"]): Promise<TagGroup> {
    return super.delete(id);
  }
}
