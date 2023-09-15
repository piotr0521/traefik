import type { AbstractModel } from "./base";

export interface Asset extends AbstractModel {
  "@type": "Asset";
  name: string;
}
