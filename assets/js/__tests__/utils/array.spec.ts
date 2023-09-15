import { contains } from "@/utils/array";

describe("contains", () => {
  const arrayA = [1, 2, 3];
  it("should return true", () => {
    const arrayB = [1, 2];
    expect(contains(arrayA, arrayB)).toBe(true);
  });
  it("should return false", () => {
    const arrayB = [1, 2, 4];
    expect(contains(arrayA, arrayB)).toBe(false);
  });
});
