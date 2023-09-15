import { useAssetTypeStore } from "@/stores/assetType";
import { useBreadcrumbsStore } from "@/stores/breadcrumbs";

export default new (class useBreadcrumbs {
  forAssetType(slug) {
    const breadcrumbsStore = useBreadcrumbsStore();
    const assetTypeStore = useAssetTypeStore();

    const currentAssetType = assetTypeStore.getBySlug(slug);
    breadcrumbsStore.reset();
    if (currentAssetType.parent) {
      breadcrumbsStore.addCrumb(currentAssetType.parent.name, "assetType", {
        slug: currentAssetType.parent.slug,
      });
    }
    breadcrumbsStore.addCrumb(currentAssetType.name, "assetType", {
      slug: slug,
    });
  }
  forAddPosition(slug, title) {
    const breadcrumbsStore = useBreadcrumbsStore();
    this.forAssetType(slug);
    breadcrumbsStore.addCrumb(title, "addPosition", {
      slug: slug,
    });
  }

  forPosition(typeSlug, position) {
    const breadcrumbsStore = useBreadcrumbsStore();
    this.forAssetType(typeSlug);
    breadcrumbsStore.addCrumb(position.name, "position", {
      uuid: position.id,
    });
  }
})();
