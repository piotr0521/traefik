import type {
  PositionCreditCard,
  PositionCreditCardList,
  PositionCreditCardQuery,
} from "@/entities/position-credit-card";

import ModelService from "./ModelService";
export default class PositionCreditCardService extends ModelService<PositionCreditCard> {
  constructor() {
    super("position/credit_cards");
  }
  list(query: PositionCreditCardQuery): Promise<PositionCreditCardList> {
    return super.list(query);
  }

  store(positionCreditCard: Partial<PositionCreditCard>): Promise<PositionCreditCard> {
    return super.store(positionCreditCard);
  }
}
