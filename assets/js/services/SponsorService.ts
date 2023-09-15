import type { Sponsor, SponsorList, SponsorQuery } from "@/entities/sponsors";

import ModelService from "./ModelService";

export default class SponsorService extends ModelService<Sponsor> {
  constructor() {
    super("sponsors");
  }
  list(query: SponsorQuery): Promise<SponsorList> {
    return super.list(query);
  }

  store(sponsor: Partial<Sponsor>): Promise<Sponsor> {
    return super.store(sponsor);
  }
}
