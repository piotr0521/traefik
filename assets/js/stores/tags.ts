import { defineStore } from "pinia";

import type { Tag, TagGroup } from "@/entities/tags";
import TagGroupService from "@/services/TagGroupService";
import TagService from "@/services/TagService";
// TODO manage add and edit state for both tag groups and tags
export const useTagStore = defineStore("tags", {
  state: () => ({
    isFetching: false,
    tagGroups: [] as TagGroup[],
    tagGroupId: "" as TagGroup["id"],
    tagGroupElement: {} as TagGroup | null,
    tagElement: {} as Tag | null,
  }),
  getters: {
    tags(): Tag[] {
      return this.tagGroups.flatMap((tagGroup: TagGroup) => tagGroup.tags);
    },
  },
  actions: {
    async getTagGroups() {
      if (this.isFetching) return;
      this.isFetching = true;
      const service = new TagGroupService();
      try {
        this.tagGroups = (await service.list({})).items;
      } catch (errors) {
        console.log(errors);
      } finally {
        this.isFetching = false;
      }
    },
    updateTagGroups(newIndex: number) {
      const service = new TagGroupService();
      const tagGroup = this.tagGroups[newIndex];
      return service.update({
        id: tagGroup.id,
        name: tagGroup.name,
        position: newIndex,
      });
    },
    deleteTagGroup(id: TagGroup["id"]) {
      const service = new TagGroupService();
      return service.delete(id).then(() => {
        this.tagGroups.splice(
          this.tagGroups.findIndex((tagGroup) => tagGroup.id === id),
          1
        );
      });
    },
    updateTags(newIndex: number, tagGroupId: number) {
      const tag = this.tagGroups[tagGroupId].tags[newIndex];
      const service = new TagService();
      return service.update({
        id: tag.id,
        name: tag.name,
        position: newIndex,
      });
    },
    deleteTag(id: Tag["id"], position: TagGroup["position"]) {
      const service = new TagService();
      return service.delete(id).then(() => {
        this.tagGroups[position].tags.splice(
          this.tagGroups[position].tags.findIndex((tag) => tag.id === id),
          1
        );
      });
    },
  },
});
