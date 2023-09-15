<script setup lang="ts">
import { storeToRefs } from "pinia";
import draggable from "vuedraggable";

import ActionsMenu from "@/components/ActionsMenu.vue";
import DeleteIcon from "@/components/icons/Delete.vue";
import DotsIcon from "@/components/icons/Dots.vue";
import DragIndicatorIcon from "@/components/icons/DragIndicator.vue";
import PencilIcon from "@/components/icons/Pencil.vue";
import { BUTTON_ACTIONS } from "@/constants/global";
import type { AbstractModel } from "@/entities/base";
import type { Tag, TagGroup } from "@/entities/tags";
import { useBreadcrumbsStore } from "@/stores/breadcrumbs";
import { usePopupStore } from "@/stores/popup";
import { useTagStore } from "@/stores/tags";

import PopupTag from "./partials/Tag.vue";
import PopupTagGroup from "./partials/TagGroup.vue";
const { updateTagGroups, deleteTagGroup, deleteTag, updateTags } = useTagStore();
const { tagGroups, tagElement, tagGroupElement, tagGroupId } = storeToRefs(useTagStore());

const { setPopupState } = usePopupStore();
type UpdateEvent<M extends AbstractModel> = {
  data: M;
  existing?: boolean;
};
type DraggableEvent = {
  newIndex: number;
  oldIndex: number;
};
useBreadcrumbsStore().reset().addCrumb("Tags", "tags");
function handleUpdateTagGroups(event: DraggableEvent) {
  updateTagGroups(event.newIndex);
}
function handleUpdateTag(event: DraggableEvent, position: number) {
  updateTags(event.newIndex, position);
}
function addTagGroup() {
  tagGroupElement.value = null;
  setPopupState({ popupName: "showPopupTagGroup", value: true });
}

function editTagGroup(tagGroup: TagGroup) {
  tagGroupElement.value = tagGroup;
  setPopupState({ popupName: "showPopupTagGroup", value: true });
}
function editTag(tag: Tag) {
  tagElement.value = tag;
  setPopupState({ popupName: "showPopupTag", value: true });
}
function addTag(id: Tag["id"]) {
  tagGroupId.value = id;
  tagElement.value = null;
  setPopupState({ popupName: "showPopupTag", value: true });
}
const updateTag = (tag: UpdateEvent<Tag>) => {
  const tagGroup = tag.data.tagGroup as TagGroup;
  if (tag.existing) {
    const storeTag = tagGroups.value
      .find((item) => item["@id"] === tagGroup["@id"])
      ?.tags.find((item) => item.id === tag.data.id);
    if (storeTag) {
      storeTag.name = tag.data.name;
      storeTag.color = tag.data.color;
    }
  } else {
    tagGroups.value.find((tagGroup) => tagGroup["@id"] === tagGroup["@id"])?.tags.unshift(tag.data);
  }
};
const updateTagGroup = (tagGroup: UpdateEvent<TagGroup>) => {
  if (tagGroup.existing) {
    const storeTagGroup = tagGroups.value.find((item: TagGroup) => item.id === tagGroup.data.id);
    if (storeTagGroup) storeTagGroup.name = tagGroup.data.name;
  } else {
    tagGroups.value.unshift(tagGroup.data);
  }
};
</script>

<template>
  <div class="py-6 px-8 grid gap-8">
    <div class="flex justify-between items-center -mb-3">
      <h1 class="h1">Tags</h1>

      <ActionsMenu :actions="[{ key: BUTTON_ACTIONS.add, label: 'Add tag group' }]" @action="addTagGroup" />
    </div>

    <!-- start tags template -->
    <draggable
      v-model="tagGroups"
      item-key="id"
      handle=".handle-group"
      class="grid xl:grid-cols-2 gap-8"
      animation="200"
      ghost-class="ghost"
      chosen-class="chosen"
      drag-class="drag"
      :force-fallback="true"
      @update="handleUpdateTagGroups"
    >
      <!-- start group -->
      <template #item="{ element, index: parentIndex }">
        <div
          class="border border-blue-200 rounded-lg flex overflow-hidden shadow-[0px_4px_10px_rgba(191,219,254,0.48)] bg-[#FBFCFE]"
        >
          <!-- start dragblock -->
          <div class="flex items-center bg-blue-50 p-2 cursor-pointer handle-group">
            <DragIndicatorIcon />
          </div>
          <!-- end dragblock -->

          <div class="flex-1 py-4 px-6 flex flex-col gap-5">
            <!-- start title group-->
            <div class="flex items-center gap-3">
              <span class="text-xl text-indigo-900 mr-auto">
                {{ element.name }}
              </span>
              <div class="relative group">
                <DotsIcon class="cursor-pointer px-2" />

                <div
                  class="hidden py-5 gap-2 border border-blue-200 rounded-md absolute right-0 top-full bg-white group-hover:grid"
                >
                  <div
                    class="flex items-center px-5 py-1 cursor-pointer hover:bg-blue-50 whitespace-nowrap"
                    @click="editTagGroup(element)"
                  >
                    <PencilIcon />
                    <span class="ml-4 font-light text-sm text-indigo-900"> Edit </span>
                  </div>

                  <div
                    class="flex items-center px-5 py-1 cursor-pointer hover:bg-blue-50 whitespace-nowrap"
                    @click="deleteTagGroup(element.id)"
                  >
                    <DeleteIcon />
                    <span class="ml-4 font-light text-sm text-indigo-900"> Delete </span>
                  </div>
                </div>
              </div>
            </div>
            <!-- end title group -->

            <!-- start items -->
            <template v-if="element.tags">
              <draggable
                v-model="element.tags"
                item-key="id"
                handle=".handle-tag"
                class="grid gap-3"
                animation="200"
                ghost-class="ghost"
                chosen-class="chosen"
                :force-fallback="true"
                @update="handleUpdateTag($event, element.position)"
              >
                <!-- start tag -->
                <template #item="item: { element: Tag }">
                  <div class="bg-blue-50 flex items-center rounded-lg p-2 px-3 gap-3">
                    <DragIndicatorIcon class="cursor-pointer handle-tag select-none" />
                    <span
                      class="w-7 h-7 d-flex rounded-lg"
                      :style="{
                        backgroundColor: item.element.color,
                      }"
                    ></span>
                    <span class="mr-auto">{{ item.element.name }}</span>
                    <PencilIcon class="cursor-pointer" @click="editTag(item.element)" />
                    <DeleteIcon class="cursor-pointer" @click="deleteTag(item.element.id, parentIndex)" />
                  </div>
                </template>
                <!-- end tag -->
              </draggable>
            </template>

            <!-- end items -->

            <!-- start btn -->
            <div class="grid justify-center mt-auto">
              <button class="btn btn-primary" @click="addTag(element['@id'])">Add Tag</button>
            </div>
            <!-- end btn -->
          </div>
        </div>
      </template>
      <!-- end group -->
    </draggable>
    <!-- end tags template -->
  </div>

  <PopupTagGroup :tag-group="tagGroupElement" @update-data="updateTagGroup" />
  <PopupTag :tag="tagElement" :tag-group-id="tagGroupId" @update-data="updateTag" />
</template>

<style>
.chosen {
  opacity: 1 !important;
}
.ghost {
  visibility: hidden !important;
}
.drag {
  opacity: 1 !important;
}
</style>
