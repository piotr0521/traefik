import type { PositionCash, PositionCashList, PositionCashQuery } from "@/entities/position-cash";

import ModelService from "./ModelService";
export default class PositionCashService extends ModelService<PositionCash> {
  constructor() {
    super("position/cash");
  }
  list(query: PositionCashQuery): Promise<PositionCashList> {
    return super.list(query);
  }

  store(PositionCash: Partial<PositionCash>): Promise<PositionCash> {
    return super.store(PositionCash);
  }
}
