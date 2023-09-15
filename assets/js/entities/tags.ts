import type { AbstractList, AbstractModel, SearchQuery } from "./base";
import { StateList } from "./base";

export interface TagGroup extends AbstractModel {
  "@type": "TagGroup";
  name: string;
  position: number;
  tags: Tag[];
}

export type TagGroupList = AbstractList<TagGroup>;

export type TagGroupQuery = SearchQuery;

export class TagGroupState extends StateList<TagGroup> {}

export interface Tag extends AbstractModel {
  "@type": "Tag";
  name: string;
  color: string;
  position: number;
  tagGroup: TagGroup | TagGroup["id"];
}
export type TagQuery = SearchQuery;

export type TagList = AbstractList<Tag>;
