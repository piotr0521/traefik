import type {
  PositionInvestment,
  PositionInvestmentList,
  PositionInvestmentQuery,
} from "@/entities/position-investment";

import ModelService from "./ModelService";
export default class PositionInvestmentService extends ModelService<PositionInvestment> {
  constructor(url: string) {
    super(url);
  }
  list(query: PositionInvestmentQuery): Promise<PositionInvestmentList> {
    return super.list(query);
  }

  store(positionInvestment: Partial<PositionInvestment>): Promise<PositionInvestment> {
    return super.store(positionInvestment);
  }
}
