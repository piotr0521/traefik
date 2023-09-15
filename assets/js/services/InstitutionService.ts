import type { Institution, InstitutionList, InstitutionQuery } from "@/entities/institutions";

import ModelService from "./ModelService";

export default class InstitutionService extends ModelService<Institution> {
  constructor() {
    super("institutions");
  }
  list(query: InstitutionQuery): Promise<InstitutionList> {
    return super.list(query);
  }
}
