import type assetTypeBackend from "../../config/backend/asset_type.json";
import type assetTypeFrontend from "../../config/frontend/asset_type.json";
type AssetTypeConfigKey = keyof typeof assetTypeBackend & keyof typeof assetTypeFrontend;
export type AssetTypeConfig = {
  [key in AssetTypeConfigKey]: typeof assetTypeBackend[key] & typeof assetTypeFrontend[key];
};
